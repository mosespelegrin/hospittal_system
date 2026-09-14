<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Asha Kimaro', 'Baraka Ndosi', 'Catherine Msoka', 'Daudi Lyimo',
            'Esther Mollel', 'Frank Massawe', 'Grace Temba', 'Hamisi Selemani',
            'Irene Mbwana', 'James Chuma', 'Khadija Suleiman', 'Lilian Mwaikambo',
            'Mussa Kigoda', 'Neema Urio', 'Omary Chande', 'Pendo Nyanda',
        ];

        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $genders = ['male', 'female'];

        foreach ($names as $index => $name) {
            Patient::create([
                'full_name' => $name,
                'date_of_birth' => now()->subYears(rand(2, 80))->subDays(rand(0, 365)),
                'gender' => $genders[array_rand($genders)],
                'phone' => '07' . rand(10000000, 99999999),
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
                'address' => 'Street ' . rand(1, 50) . ', Dar es Salaam',
                'emergency_contact' => '07' . rand(10000000, 99999999),
                'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                'allergies' => rand(0, 1) ? 'None known' : 'Penicillin',
                'medical_history' => rand(0, 1) ? null : 'Hypertension, managed with medication',
                'status' => 'active',
                'created_by' => 1,
            ]);
        }
    }
}