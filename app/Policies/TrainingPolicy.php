<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;

class TrainingPolicy
{
    public function __construct(private TrainingCapabilityResolver $capabilityResolver) {}

    public function view(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canView($user, $training);
    }

    public function update(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canEdit($user, $training);
    }

    public function delete(User $user, Training $training): bool
    {
        return $this->capabilityResolver->canDelete($user, $training);
    }
}
