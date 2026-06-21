<?php

namespace App\Policies;

use App\Models\User;

/**
 * MVP scope (go-live Phase 2, Step B1a): gates only `resetPassword` and
 * `logout` (force-logout) on ApkController. Rule matches the frontend's
 * existing `canEdit` gate in master/apk/page.tsx (admin OR type 1/3) —
 * NOT admin-only. See validate-contract Execute-Agent Instruction E3.
 *
 * Authorized via class-string form ($this->authorize('resetPassword',
 * \App\Models\ApkUser::class)) since ApkController never fetches an
 * ApkUser Eloquent instance (raw DB::table() queries only) — see E2.
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
}
