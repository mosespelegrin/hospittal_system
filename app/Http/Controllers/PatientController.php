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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],

            'department_id' => ['required', 'exists:departments,id'],
            'doctor_id' => [
                'nullable',
                Rule::exists('doctors', 'id')
                    ->where('department_id', $request->input('department_id'))
                    ->where('status', true),
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
            'chief_complaint' => ['required', 'string'],
        ], [
            'doctor_id.exists' => 'The selected doctor is not available in the chosen department.',
        ]);

        $doctorId = $this->resolveAvailableDoctor($validated);

        [$patient, $appointment] = DB::transaction(function () use ($validated, $doctorId) {
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
                $patient->update($patientData);
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
        return view('patients.success', compact('patient'));
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
            if (Appointment::hasConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time'])) {
                throw ValidationException::withMessages([
                    'appointment_time' => 'That doctor already has an appointment at this date and time. Please choose another slot.',
                ]);
            }

            return (int) $validated['doctor_id'];
        }

        $available = Doctor::where('department_id', $validated['department_id'])
            ->where('status', true)
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
