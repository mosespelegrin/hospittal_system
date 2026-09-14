<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            ['name' => 'Dr. Amina Juma', 'specialization' => 'General Physician', 'department' => 'General Medicine'],
            ['name' => 'Dr. John Mushi', 'specialization' => 'Pediatrician', 'department' => 'Pediatrics'],
            ['name' => 'Dr. Grace Mwakalinga', 'specialization' => 'Dentist', 'department' => 'Dental'],
            ['name' => 'Dr. Peter Kileo', 'specialization' => 'Ophthalmologist', 'department' => 'Eye Clinic'],
            ['name' => 'Dr. Fatma Ally', 'specialization' => 'ENT Specialist', 'department' => 'ENT'],
            ['name' => 'Dr. Emmanuel Shirima', 'specialization' => 'Cardiologist', 'department' => 'Cardiology'],
            ['name' => 'Dr. Neema Mrema', 'specialization' => 'General Surgeon', 'department' => 'Surgery'],
            ['name' => 'Dr. Hassan Rajabu', 'specialization' => 'Orthopedic Surgeon', 'department' => 'Orthopedics'],
        ];

        foreach ($doctors as $index => $doc) {
            $department = Department::where('name', $doc['department'])->first();

            Doctor::create([
                'department_id' => $department->id,
                'name' => $doc['name'],
                'email' => 'doctor' . ($index + 1) . '@hospital.test',
                'phone' => '07' . rand(10000000, 99999999),
                'license_number' => 'LIC-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'specialization' => $doc['specialization'],
                'availability' => 'Mon-Fri, 8:00-16:00',
                'status' => true,
            ]);
        }
    }
}