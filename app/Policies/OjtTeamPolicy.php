<?php

namespace App\Policies;

use App\Models\OjtTeam;
use App\Models\User;

class OjtTeamPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OjtTeam $ojtTeam): bool
    {
        return (int) $ojtTeam->mentor_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OjtTeam $ojtTeam): bool
    {
        return (int) $ojtTeam->mentor_id === $user->id;
    }
}
