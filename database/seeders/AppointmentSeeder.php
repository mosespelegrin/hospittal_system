<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'approved', 'waiting', 'in_progress', 'completed', 'cancelled'];
        $patients = Patient::all();
        $doctors = Doctor::all();

        foreach ($patients as $patient) {
            // give each patient 1-3 appointments across random doctors/dates
            $appointmentCount = rand(1, 3);

            for ($i = 0; $i < $appointmentCount; $i++) {
                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctors->random()->id,
                    'appointment_date' => now()->addDays(rand(-30, 30))->toDateString(),
                    'appointment_time' => sprintf('%02d:%02d:00', rand(8, 16), [0, 15, 30, 45][array_rand([0, 15, 30, 45])]),
                    'status' => $statuses[array_rand($statuses)],
                    'notes' => rand(0, 1) ? 'Routine check-up' : null,
                ]);
            }
        }
    }
}