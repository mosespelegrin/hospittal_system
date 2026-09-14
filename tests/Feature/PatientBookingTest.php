<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\AppointmentBooked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PatientBookingTest extends TestCase
{
    use RefreshDatabase;

    private function department(): Department
    {
        return Department::create([
            'name' => 'General Medicine',
            'description' => 'General consultations',
            'status' => true,
        ]);
    }

    private function doctor(Department $department, array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'department_id' => $department->id,
            'name' => 'Dr. Amina Juma',
            'email' => 'amina' . uniqid() . '@hospital.test',
            'phone' => '0700000000',
            'license_number' => 'LIC-' . uniqid(),
            'specialization' => 'General Physician',
            'availability' => 'Mon-Fri',
            'status' => true,
        ], $overrides));
    }

    private function bookingPayload(Department $department, array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Jane Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone' => '0711223344',
            'email' => 'jane@example.test',
            'department_id' => $department->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'chief_complaint' => 'Persistent headache',
        ], $overrides);
    }

    public function test_booking_creates_a_patient_and_appointment_and_redirects_to_a_signed_success_page(): void
    {
        Notification::fake();

        $department = $this->department();
        $this->doctor($department);

        $response = $this->post(route('patient.store'), $this->bookingPayload($department));

        $this->assertSame(1, Patient::count());
        $this->assertSame(1, Appointment::count());

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->get($response->headers->get('Location'))->assertOk();
    }

    public function test_success_page_rejects_an_unsigned_url(): void
    {
        $department = $this->department();
        $this->doctor($department);

        $this->post(route('patient.store'), $this->bookingPayload($department));

        $patient = Patient::sole();

        // Simulate someone guessing/incrementing the patient id instead of
        // following the signed link they were actually given.
        $this->get('/consultation/success/' . $patient->id)->assertForbidden();
    }

    public function test_booking_fails_with_a_friendly_error_when_no_doctor_is_available(): void
    {
        $department = $this->department();
        // No doctors seeded for this department at all.

        $response = $this->post(route('patient.store'), $this->bookingPayload($department));

        $response->assertSessionHasErrors('appointment_time');
        $this->assertSame(0, Patient::count());
        $this->assertSame(0, Appointment::count());
    }

    public function test_booking_rejects_a_slot_the_chosen_doctor_already_has(): void
    {
        $department = $this->department();
        $doctor = $this->doctor($department);

        $payload = $this->bookingPayload($department, ['doctor_id' => $doctor->id]);
        $this->post(route('patient.store'), $payload);

        $this->assertSame(1, Appointment::count());

        // A second, different patient tries to book the same doctor at the
        // exact same date/time.
        $response = $this->post(route('patient.store'), array_merge($payload, [
            'full_name' => 'John Smith',
            'phone' => '0799887766',
        ]));

        $response->assertSessionHasErrors('appointment_time');
        $this->assertSame(1, Appointment::count());
    }

    public function test_booking_reuses_the_existing_patient_record_for_the_same_name_and_phone(): void
    {
        $department = $this->department();
        $this->doctor($department);

        $payload = $this->bookingPayload($department);

        $this->post(route('patient.store'), $payload);
        $this->post(route('patient.store'), array_merge($payload, [
            'appointment_date' => now()->addDays(2)->toDateString(),
        ]));

        $this->assertSame(1, Patient::count());
        $this->assertSame(2, Appointment::count());
    }

    public function test_booking_sends_a_confirmation_notification_to_staff(): void
    {
        Notification::fake();

        $department = $this->department();
        $this->doctor($department);

        $this->post(route('patient.store'), $this->bookingPayload($department));

        Notification::assertSentOnDemand(AppointmentBooked::class);
    }
}
