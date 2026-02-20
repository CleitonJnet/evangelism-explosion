<?php

namespace App\Policies;

use App\Models\StpApproach;
use App\Models\User;

class StpApproachPolicy
{
    public function view(User $user, StpApproach $approach): bool
    {
        return $this->canManageApproach($user, $approach);
    }

    public function update(User $user, StpApproach $approach): bool
    {
        return $this->canManageApproach($user, $approach);
    }

    public function delete(User $user, StpApproach $approach): bool
    {
        return $this->canManageApproach($user, $approach);
    }

    private function canManageApproach(User $user, StpApproach $approach): bool
    {
        if ($approach->training()->where('teacher_id', $user->id)->exists()) {
            return true;
        }

        return $approach->team()->where('mentor_user_id', $user->id)->exists();
    }
}
