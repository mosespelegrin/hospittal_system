<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view appointments');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if (! $user->can('view appointments')) {
            return false;
        }

        if ($user->hasRole('doctor')) {
            return $appointment->doctor?->user_id === $user->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('manage appointments');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if (! $user->can('manage appointments')) {
            return false;
        }

        if ($user->hasRole('doctor')) {
            return $appointment->doctor?->user_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can('delete appointments');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete appointments');
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->can('delete appointments');
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->can('delete appointments');
    }
}
