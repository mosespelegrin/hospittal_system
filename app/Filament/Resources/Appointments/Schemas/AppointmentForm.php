<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->relationship('patient', 'full_name')
                    ->searchable()
                    ->required(),
                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->required()
                    ->live(),
                DatePicker::make('appointment_date')
                    ->required()
                    ->live(),
                TimePicker::make('appointment_time')
                    ->required()
                    ->rule(fn (Get $get, ?Model $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                        if (Appointment::hasConflict($get('doctor_id'), $get('appointment_date'), $value, $record?->id)) {
                            $fail('This doctor already has an appointment at that date and time.');
                        }
                    }),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'waiting' => 'Waiting',
                        'in_progress' => 'In progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
