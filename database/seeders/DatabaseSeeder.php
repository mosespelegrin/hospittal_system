<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@hospital.test'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@hospital.test'],
            [
                'name' => 'Receptionist User',
                'password' => bcrypt('password'),
            ]
        );
        $receptionist->assignRole('receptionist');

        $this->call([
            DepartmentSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
        ]);

        // Demo doctor portal login, linked to the first seeded doctor so
        // there's an account to try the scoped "doctor" role with.
        if ($firstDoctor = Doctor::first()) {
            $doctorUser = User::firstOrCreate(
                ['email' => 'doctor@hospital.test'],
                [
                    'name' => $firstDoctor->name,
                    'password' => bcrypt('password'),
                ]
            );
            $doctorUser->assignRole('doctor');
            $firstDoctor->update(['user_id' => $doctorUser->id]);
        }
    }
}