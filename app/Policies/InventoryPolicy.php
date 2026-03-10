<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Director') || $user->hasRole('Teacher');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        if ($user->hasRole('Director')) {
            return true;
        }

        return $user->hasRole('Teacher') && (int) $inventory->user_id === (int) $user->id;
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $this->view($user, $inventory);
    }
}
