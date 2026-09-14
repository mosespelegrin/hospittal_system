<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id', 'user_id', 'name', 'email', 'phone',
        'license_number', 'specialization', 'availability', 'status',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * The staff login (with the "doctor" role) linked to this doctor
     * record, if the doctor has portal access.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}