<?php

namespace App\Policies;

use App\Models\User;

/**
 * go-live Phase 3, Section 1: gates `update`/`destroy` on OutboundController
 * (admin only). `store`/`index`/`show` access control is explicitly deferred
 * — see process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md.
 */
class OutboundPolicy
{
    public function update(User $user): bool
    {
        return $user->admin;
    }

    public function delete(User $user): bool
    {
        return $user->admin;
    }
}
