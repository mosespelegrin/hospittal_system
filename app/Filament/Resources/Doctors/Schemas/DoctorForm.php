<?php

namespace App\Filament\Resources\Doctors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('license_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('specialization')
                    ->required(),
                TextInput::make('availability')
                    ->default(null),
                Toggle::make('status')
                    ->required(),
                Select::make('user_id')
                    ->label('Portal login')
                    ->helperText('Link a staff login with the "doctor" role so they can see their own appointments.')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable(),
                TextInput::make('login_password')
                    ->label('Create login with password')
                    ->helperText('Optional: creates a doctor login using the email above and links it.')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->visibleOn('create')
                    ->dehydrated(fn (?string $state) => filled($state)),
            ]);
    }
}