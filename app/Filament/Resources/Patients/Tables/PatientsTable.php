<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Filament\Resources\Patients\PatientResource;
use App\Models\Appointment;
use App\Models\Doctor;
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
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => static::exportCsv()),
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
     * A plain, synchronous CSV download of every patient visible to the
     * current user (respects the same role-based scoping as the table).
     * Cell values that start with =, +, -, or @ are quoted, since a raw
     * leading formula character can execute code when the file is later
     * opened in Excel/Sheets (CSV formula injection).
     */
    private static function exportCsv(): StreamedResponse
    {
        $patients = PatientResource::getEloquentQuery()->get();

        $escape = fn ($value) => preg_match('/^[=+\-@]/', (string) $value)
            ? "'" . $value
            : $value;

        return response()->streamDownload(function () use ($patients, $escape) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Patient Number', 'Full Name', 'Date of Birth', 'Gender', 'Phone', 'Email', 'Blood Group', 'Status', 'Registered At']);

            foreach ($patients as $patient) {
                fputcsv($handle, array_map($escape, [
                    $patient->patient_number,
                    $patient->full_name,
                    $patient->date_of_birth?->format('Y-m-d'),
                    $patient->gender,
                    $patient->phone,
                    $patient->email,
                    $patient->blood_group,
                    $patient->status,
                    $patient->created_at?->format('Y-m-d H:i'),
                ]));
            }

            fclose($handle);
        }, 'patients-' . now()->format('Y-m-d') . '.csv');
    }
}