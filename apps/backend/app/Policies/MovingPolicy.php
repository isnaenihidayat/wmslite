<?php

namespace App\Policies;

use App\Models\User;

/**
 * go-live Phase 2, Step B1c: gates only the destructive `destroy` action
 * on MovingController (admin only), matching the UserController pattern.
 */
class MovingPolicy
{
    public function delete(User $user): bool
    {
        return $user->admin;
    }
}
