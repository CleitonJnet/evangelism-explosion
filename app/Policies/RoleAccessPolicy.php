<?php

namespace App\Policies;

use App\Models\User;

class RoleAccessPolicy
{
    public function accessBoard(User $user): bool
    {
        return $user->hasRole('Board');
    }

    public function accessDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    public function accessTeacher(User $user): bool
    {
        return $user->hasRole('Teacher');
    }

    public function accessFacilitator(User $user): bool
    {
        return $user->hasRole('Facilitator');
    }

    public function accessFieldworker(User $user): bool
    {
        return $user->hasRole('FieldWorker');
    }

    public function accessMentor(User $user): bool
    {
        return $user->hasRole('Mentor');
    }

    public function accessStudent(User $user): bool
    {
        return $user->hasRole('Student');
    }
}
