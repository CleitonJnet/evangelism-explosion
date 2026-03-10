<?php

namespace App\Policies;

use App\Models\StpApproach;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;

class StpApproachPolicy
{
    public function __construct(private TrainingCapabilityResolver $capabilityResolver) {}

    public function view(User $user, StpApproach $approach): bool
    {
        return $this->capabilityResolver->canViewStpOjt($user, $approach->training);
    }

    public function update(User $user, StpApproach $approach): bool
    {
        return $this->capabilityResolver->canEdit($user, $approach->training);
    }

    public function delete(User $user, StpApproach $approach): bool
    {
        return $this->capabilityResolver->canDelete($user, $approach->training);
    }
}
