<?php

namespace App\Policies;

use App\Models\User;

/**
 * go-live Phase 2, Step B1c: originally gated only the destructive
 * `destroy` action on MovingController (admin only), matching the
 * UserController pattern. go-live Phase 3, Section 6 extends this with
 * `store`, using the SAME admin-only rule for consistency.
 */
class MovingPolicy
{
    public function delete(User $user): bool
    {
        return $user->admin;
    }

    public function store(User $user): bool
    {
        return $user->admin;
    }
}
