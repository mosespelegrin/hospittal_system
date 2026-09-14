<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    }

    public function test_guest_cannot_reach_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_a_user_with_no_role_is_denied_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_reach_the_admin_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_receptionist_can_reach_the_panel_and_manage_patients_but_not_doctors(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');

        $this->actingAs($receptionist)->get('/admin')->assertOk();

        $this->assertTrue($receptionist->can('viewAny', Patient::class));
        $this->assertTrue($receptionist->can('create', Patient::class));
        $this->assertFalse($receptionist->can('delete', new Patient()));

        $this->assertTrue($receptionist->can('viewAny', Doctor::class));
        $this->assertFalse($receptionist->can('create', Doctor::class));
    }

    public function test_admin_bypasses_every_permission_check(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('delete', new Doctor()));
        $this->assertTrue($admin->can('manage departments'));
    }

    public function test_a_doctor_can_only_view_their_own_appointment(): void
    {
        $department = Department::create([
            'name' => 'Cardiology',
            'status' => true,
        ]);

        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        $ownDoctor = Doctor::create([
            'department_id' => $department->id,
            'user_id' => $doctorUser->id,
            'name' => 'Dr. Emmanuel Shirima',
            'email' => 'emmanuel@hospital.test',
            'phone' => '0700000001',
            'license_number' => 'LIC-0001',
            'specialization' => 'Cardiologist',
            'status' => true,
        ]);

        $otherDoctor = Doctor::create([
            'department_id' => $department->id,
            'name' => 'Dr. Neema Mrema',
            'email' => 'neema@hospital.test',
            'phone' => '0700000002',
            'license_number' => 'LIC-0002',
            'specialization' => 'Surgeon',
            'status' => true,
        ]);

        $patient = Patient::create([
            'full_name' => 'Jane Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone' => '0711223344',
            'status' => 'active',
        ]);

        $ownAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $ownDoctor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'pending',
        ]);

        $othersAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'pending',
        ]);

        $this->assertTrue($doctorUser->can('view', $ownAppointment));
        $this->assertFalse($doctorUser->can('view', $othersAppointment));

        // Doctors are read-only: no manage-appointments permission at all.
        $this->assertFalse($doctorUser->can('update', $ownAppointment));
    }
}
