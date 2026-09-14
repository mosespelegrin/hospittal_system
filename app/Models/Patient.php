<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_number',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'emergency_contact',
        'blood_group',
        'allergies',
        'medical_history',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->patient_number)) {
                // Locking the row scan inside the caller's transaction (see
                // PatientController::store) prevents two concurrent
                // submissions from computing the same next id and colliding
                // on the unique patient_number constraint.
                $nextId = (static::withTrashed()->lockForUpdate()->max('id') ?? 0) + 1;
                $patient->patient_number = 'PT-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}