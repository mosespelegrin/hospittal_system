<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    /**
     * If a login password was entered, create the doctor's portal account
     * (role "doctor") and link it.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password = $data['login_password'] ?? null;
        unset($data['login_password']);

        if (filled($password) && empty($data['user_id'])) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password,
            ]);
            $user->assignRole('doctor');
            $data['user_id'] = $user->id;
        }

        return $data;
    }
}
