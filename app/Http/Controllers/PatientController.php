<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentBooked;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PatientController extends Controller
{
    public function welcome()
    {
        $departments = Department::where('status', true)->get();
        $doctors = Doctor::where('status', true)->with('department')->get();

        return view('welcome', compact('departments', 'doctors'));
    }

    public function create()
    {
        $departments = Department::where('status', true)->get();
        $doctors = Doctor::where('status', true)->get();

        return view('patients.register', compact('departments', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $name = $request->input('full_name');

        $request->merge([
            'phone' => self::normalizePhone($request->input('phone')),
            'emergency_contact' => self::normalizePhone($request->input('emergency_contact')),
            'full_name' => is_string($name) ? trim(preg_replace('/\s+/', ' ', $name)) : $name,
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'in:male,female'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:5000'],
            'consent' => ['accepted'],

            'department_id' => ['required', 'exists:departments,id'],
            'doctor_id' => [
                'nullable',
                Rule::exists('doctors', 'id')
                    ->where('department_id', $request->input('department_id'))
                    ->where('status', true),
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'chief_complaint' => ['required', 'string', 'max:2000'],
        ], [
            'doctor_id.exists' => 'The selected doctor is not available in the chosen department.',
            'phone.regex' => 'Enter a valid phone number, e.g. 0712 345 678 or +255 712 345 678.',
            'emergency_contact.regex' => 'Enter a valid phone number for the emergency contact.',
            'consent.accepted' => 'Please confirm you agree to the privacy policy to continue.',
        ]);

        [$patient, $appointment] = DB::transaction(function () use ($validated) {
            // Resolved inside the transaction, with the doctor rows locked,
            // so two simultaneous bookings cannot take the same slot.
            $doctorId = $this->resolveAvailableDoctor($validated);

            $patient = $this->findExistingPatient($validated);

            $patientData = [
                'full_name' => $validated['full_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'status' => 'active',
                'created_by' => null,
            ];

            if ($patient) {
                // The name/phone match is not proof of identity, so a public
                // submission may only fill in blank fields on an existing
                // record, never overwrite stored medical details.
                $patient->update(array_filter(
                    Arr::only($patientData, [
                        'email', 'address', 'emergency_contact', 'blood_group', 'allergies', 'medical_history',
                    ]),
                    fn ($value, $key) => filled($value) && blank($patient->{$key}),
                    ARRAY_FILTER_USE_BOTH,
                ));
            } else {
                $patient = Patient::create($patientData);
            }

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'status' => 'pending',
                'notes' => $validated['chief_complaint'],
            ]);

            return [$patient, $appointment];
        });

        $this->sendBookingNotifications($patient, $appointment);

        return redirect(URL::temporarySignedRoute(
            'patient.success',
            now()->addDay(),
            ['patient' => $patient->id],
        ));
    }

    public function success(Patient $patient)
    {
        $appointment = $patient->appointments()->with('doctor')->latest()->first();

        return response()
            ->view('patients.success', compact('patient', 'appointment'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function sitemap()
    {
        $pages = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('patient.register'), 'priority' => '0.9'],
            ['loc' => route('privacy'), 'priority' => '0.3'],
        ];

        return response()
            ->view('sitemap', ['pages' => $pages])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Normalise Tanzanian-style numbers to +255XXXXXXXXX so the same person
     * is matched regardless of how they typed it (0712 345 678, 255712345678...).
     */
    public static function normalizePhone(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $digits = preg_replace('/[\s\-().]/', '', $value);
        $cc = config('hospital.country_code');

        if (str_starts_with($digits, '00')) {
            $digits = '+'.substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '+'.$cc.substr($digits, 1);
        } elseif (str_starts_with($digits, $cc) && strlen($digits) >= strlen($cc) + 9) {
            $digits = '+'.$digits;
        }

        return $digits;
    }

    /**
     * Pick a doctor for the appointment, honouring an explicit preference
     * if given, otherwise the least busy available doctor in the
     * department for that exact slot. Throws a validation error instead
     * of silently booking a null/conflicting doctor.
     */
    private function resolveAvailableDoctor(array $validated): int
    {
        if (! empty($validated['doctor_id'])) {
            Doctor::whereKey($validated['doctor_id'])->lockForUpdate()->first();

            if (Appointment::hasConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time'])) {
                throw ValidationException::withMessages([
                    'appointment_time' => 'That doctor already has an appointment at this date and time. Please choose another slot.',
                ]);
            }

            return (int) $validated['doctor_id'];
        }

        $available = Doctor::where('department_id', $validated['department_id'])
            ->where('status', true)
            ->lockForUpdate()
            ->get()
            ->reject(fn (Doctor $doctor) => Appointment::hasConflict(
                $doctor->id,
                $validated['appointment_date'],
                $validated['appointment_time'],
            ));

        if ($available->isEmpty()) {
            throw ValidationException::withMessages([
                'appointment_time' => 'No doctors are available in this department for that date and time. Please choose another slot.',
            ]);
        }

        return $available->random()->id;
    }

    /**
     * Reuse an existing patient record when the same name/phone combination
     * has already registered, rather than creating a duplicate patient for
     * every visit.
     */
    private function findExistingPatient(array $validated): ?Patient
    {
        return Patient::where('phone', $validated['phone'])
            ->whereRaw('LOWER(full_name) = ?', [mb_strtolower(trim($validated['full_name']))])
            ->first();
    }

    private function sendBookingNotifications(Patient $patient, Appointment $appointment): void
    {
        // The booking is already saved; a mail outage must not turn a
        // successful booking into an error page.
        try {
            $this->deliverBookingNotifications($patient, $appointment);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function deliverBookingNotifications(Patient $patient, Appointment $appointment): void
    {
        if ($patient->email) {
            Notification::route('mail', $patient->email)
                ->notify(new AppointmentBooked($appointment));
        }

        $staff = User::role(['admin', 'receptionist'])->get();

        if ($staff->isNotEmpty()) {
            Notification::send($staff, new AppointmentBooked($appointment, forStaff: true));
        }
    }
}
