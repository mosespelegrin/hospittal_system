<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'General Medicine',
            'Pediatrics',
            'Dental',
            'Eye Clinic',
            'ENT',
            'Cardiology',
            'Surgery',
            'Orthopedics',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => "Department of {$name}", 'status' => true]
            );
        }
    }
}