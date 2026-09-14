<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'doctor_id', 'appointment_date',
        'appointment_time', 'status', 'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Whether the given doctor already has a non-cancelled appointment at
     * this exact date and time. Pass $ignoreId when checking an existing
     * appointment being edited, so it doesn't conflict with itself.
     */
    public static function hasConflict(?int $doctorId, ?string $date, ?string $time, ?int $ignoreId = null): bool
    {
        if (blank($doctorId) || blank($date) || blank($time)) {
            return false;
        }

        return static::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereTime('appointment_time', date('H:i:s', strtotime($time)))
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}