<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;

class TrainingPolicy
{
    public function view(User $user, Training $training): bool
    {
        return $this->canManageTraining($user, $training);
    }

    public function update(User $user, Training $training): bool
    {
        return $this->canManageTraining($user, $training);
    }

    public function delete(User $user, Training $training): bool
    {
        return $this->canManageTraining($user, $training);
    }

    private function canManageTraining(User $user, Training $training): bool
    {
        if ($user->hasRole('Director')) {
            return true;
        }

        return $training->teacher_id === $user->id;
    }
}
