<?php

namespace App\Policies;

use App\Models\User;

/**
 * go-live Phase 2, Step B1b: formalizes the existing ad hoc admin check
 * (`if (! $request->user()->admin)`) across all 5 UserController methods
 * into a Laravel Policy. Behavior is unchanged — admin-only — only the
 * mechanism changes.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin;
    }

    public function create(User $user): bool
    {
        return $user->admin;
    }

    public function update(User $user): bool
    {
        return $user->admin;
    }

    public function resetPassword(User $user): bool
    {
        return $user->admin;
    }

    public function delete(User $user): bool
    {
        return $user->admin;
    }
}
