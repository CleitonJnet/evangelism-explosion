<?php

namespace App\Policies;

use App\Models\OjtSession;
use App\Models\User;

class OjtSessionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OjtSession $ojtSession): bool
    {
        return $ojtSession->teams()->where('mentor_id', $user->id)->exists();
    }
}
