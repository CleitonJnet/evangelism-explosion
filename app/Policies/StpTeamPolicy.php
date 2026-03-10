<?php

namespace App\Policies;

use App\Models\StpTeam;
use App\Models\User;
use App\Support\TrainingAccess\TrainingCapabilityResolver;

class StpTeamPolicy
{
    public function __construct(private TrainingCapabilityResolver $capabilityResolver) {}

    public function view(User $user, StpTeam $team): bool
    {
        $training = $team->session()->with('training')->first()?->training;

        if ($training === null) {
            return false;
        }

        if ($this->capabilityResolver->canEdit($user, $training)) {
            return true;
        }

        return (int) $team->mentor_user_id === (int) $user->id
            && $this->capabilityResolver->canViewStpOjt($user, $training);
    }

    public function update(User $user, StpTeam $team): bool
    {
        $training = $team->session()->with('training')->first()?->training;

        return $training !== null
            && $this->capabilityResolver->canEdit($user, $training);
    }

    public function delete(User $user, StpTeam $team): bool
    {
        return $this->update($user, $team);
    }
}
