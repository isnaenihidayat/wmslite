<?php

namespace App\Policies;

use App\Models\User;

/**
 * MVP scope (go-live Phase 2, Step B1a): originally gated only
 * `resetPassword` and `logout` (force-logout) on ApkController. go-live
 * Phase 3, Section 4 extends this with `store`/`update`/`destroy`, using
 * the SAME rule (admin OR type 1/3) for consistency with the Phase 2
 * decision — matches the frontend's existing `canEdit` gate in
 * master/apk/page.tsx. NOT admin-only.
 *
 * Authorized via class-string form ($this->authorize('store',
 * \App\Models\ApkUser::class)) since ApkController never fetches an
 * ApkUser Eloquent instance anywhere (raw DB::table() queries only) —
 * confirmed at Phase 3 VALIDATE, the class-string form is the only
 * viable form for all methods, not just a convention preference.
 */
class ApkUserPolicy
{
    public function resetPassword(User $user): bool
    {
        return $user->admin || in_array($user->type, [1, 3]);
    }

    public function logout(User $user): bool
    {
        return $user->admin || in_array($user->type, [1, 3]);
    }

    public function store(User $user): bool
    {
        return $user->admin || in_array($user->type, [1, 3]);
    }

    public function update(User $user): bool
    {
        return $user->admin || in_array($user->type, [1, 3]);
    }

    public function delete(User $user): bool
    {
        return $user->admin || in_array($user->type, [1, 3]);
    }
}
