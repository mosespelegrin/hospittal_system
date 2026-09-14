<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view doctors');
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->can('view doctors');
    }

    public function create(User $user): bool
    {
        return $user->can('manage doctors');
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->can('manage doctors');
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->can('delete doctors');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete doctors');
    }

    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->can('delete doctors');
    }

    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->can('delete doctors');
    }
}
