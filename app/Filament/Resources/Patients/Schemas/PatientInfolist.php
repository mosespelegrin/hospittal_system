<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('patient_number'),
                TextEntry::make('full_name'),
                TextEntry::make('date_of_birth')
                    ->date(),
                TextEntry::make('gender')
                    ->badge(),
                TextEntry::make('phone'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('emergency_contact')
                    ->placeholder('-'),
                TextEntry::make('blood_group')
                    ->placeholder('-'),
                TextEntry::make('allergies')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('medical_history')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
