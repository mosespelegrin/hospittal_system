<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Filament\Resources\Patients\PatientResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_number')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('emergency_contact')
                    ->searchable(),
                TextColumn::make('blood_group')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female']),
                SelectFilter::make('status')
                    ->options(fn () => Patient::query()->distinct()->pluck('status', 'status')->filter()->all()),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'receptionist']) ?? false)
                    ->action(fn ($livewire) => static::exportCsv($livewire->getFilteredSortedTableQuery())),
            ])
            ->recordActions([
                Action::make('assignDoctor')
                    ->label('Assign Doctor')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->schema([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->options(Doctor::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('appointment_date')
                            ->required(),
                        TimePicker::make('appointment_time')
                            ->required(),
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
                    ])
                    ->action(function (array $data, $record) {
                        if (Appointment::hasConflict($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
                            Notification::make()
                                ->title('That doctor already has an appointment at this date and time.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Appointment::create([
                            'patient_id' => $record->id,
                            'doctor_id' => $data['doctor_id'],
                            'appointment_date' => $data['appointment_date'],
                            'appointment_time' => $data['appointment_time'],
                            'status' => $data['status'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Doctor assigned successfully')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Streams a CSV of the patients currently shown in the table (search and
     * filters applied). Only admin and receptionist see the button, and the
     * role is re-checked here because Livewire actions can be called directly.
     * Cell values starting with =, +, -, or @ are quoted to prevent CSV
     * formula injection when opened in Excel/Sheets.
     */
    private static function exportCsv($query): StreamedResponse
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'receptionist']), 403);

        $escape = fn ($value) => preg_match('/^[=+\-@]/', (string) $value)
            ? "'" . $value
            : $value;

        return response()->streamDownload(function () use ($query, $escape) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "﻿"); // UTF-8 BOM so Excel reads accents correctly

            fputcsv($handle, ['Patient Number', 'Full Name', 'Date of Birth', 'Gender', 'Phone', 'Email', 'Emergency Contact', 'Blood Group', 'Status', 'Registered At', 'Total Appointments', 'Last Appointment']);

            $query->withCount('appointments')
                ->withMax('appointments', 'appointment_date')
                ->reorder('id')
                ->chunkById(500, function ($patients) use ($handle, $escape) {
                    foreach ($patients as $patient) {
                        fputcsv($handle, array_map($escape, [
                            $patient->patient_number,
                            $patient->full_name,
                            $patient->date_of_birth?->format('Y-m-d'),
                            $patient->gender,
                            $patient->phone,
                            $patient->email,
                            $patient->emergency_contact,
                            $patient->blood_group,
                            $patient->status,
                            $patient->created_at?->format('Y-m-d H:i'),
                            $patient->appointments_count,
                            $patient->appointments_max_appointment_date,
                        ]));
                    }
                });

            fclose($handle);
        }, 'patients-' . now()->format('Y-m-d') . '.csv');
    }
}
