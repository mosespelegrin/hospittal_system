<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Create the production admin from ADMIN_EMAIL / ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            ['name' => env('ADMIN_NAME', 'Administrator'), 'password' => $password]
        );
        $admin->assignRole('admin');
    }
}
