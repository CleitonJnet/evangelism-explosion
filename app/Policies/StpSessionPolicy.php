<?php

namespace App\Policies;

use App\Models\StpSession;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;

class StpSessionPolicy
{
    public function __construct(private TrainingCapabilityResolver $capabilityResolver) {}

    public function view(User $user, StpSession $session): bool
    {
        if ($this->capabilityResolver->canEdit($user, $session->training)) {
            return true;
        }

        if (! $this->capabilityResolver->canViewStpOjt($user, $session->training)) {
            return false;
        }

        return $session->teams()
            ->where('mentor_user_id', $user->id)
            ->exists();
    }

    public function update(User $user, StpSession $session): bool
    {
        return $this->capabilityResolver->canEdit($user, $session->training);
    }

    public function delete(User $user, StpSession $session): bool
    {
        return $this->capabilityResolver->canDelete($user, $session->training);
    }
}
