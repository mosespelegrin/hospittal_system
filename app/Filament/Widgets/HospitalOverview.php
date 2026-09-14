<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class HospitalOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['admin', 'receptionist']) ?? false;
    }

    protected function getStats(): array
    {
        return [
            Stat::make("Today's appointments", Appointment::whereDate('appointment_date', today())->count())
                ->description('Across all departments')
                ->icon('heroicon-o-calendar-days')
                ->color('info'),
            Stat::make('Pending appointments', Appointment::where('status', 'pending')->count())
                ->description('Awaiting confirmation')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Active patients', Patient::where('status', 'active')->count())
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Active doctors', Doctor::where('status', true)->count())
                ->icon('heroicon-o-user-group')
                ->color('success'),
        ];
    }
}
