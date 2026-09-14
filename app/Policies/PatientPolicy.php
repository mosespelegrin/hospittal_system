<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        if (! $user->can('view patients')) {
            return false;
        }

        // A doctor may only open patients they actually have an
        // appointment with, not the whole patient list.
        if ($user->hasRole('doctor')) {
            return $patient->appointments()
                ->whereHas('doctor', fn ($query) => $query->where('user_id', $user->id))
                ->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('manage patients');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('manage patients');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('delete patients');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete patients');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->can('delete patients');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->can('delete patients');
    }
}
