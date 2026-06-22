<?php

namespace App\Policies;

use App\Models\User;

/**
 * go-live Phase 3, Section 1: gates `update`/`destroy` on ShipmentController
 * (admin only).
 *
 * EXECUTE-time deviation note (22-06-26): ShipmentController operates on the
 * `Inbound` Eloquent model (el_inbound_header, from_shipment=1 scope) — there
 * is no dedicated `Shipment` model in this codebase. `Gate::policy()` binds
 * one Policy class per model class, and `Inbound::class` is already bound to
 * `InboundPolicy` (see AppServiceProvider::boot()). Registering this Policy
 * via `Gate::policy(Inbound::class, ShipmentPolicy::class)` would silently
 * shadow that binding. Since the rule is identical (admin-only) for both
 * controllers, this Policy is intentionally NOT registered with the Gate
 * facade — ShipmentController invokes it directly (see
 * ShipmentController::update()/destroy()) and throws
 * Illuminate\Auth\Access\AuthorizationException manually on denial, which
 * Laravel's default exception handler renders identically to a Gate-based
 * 403 (bootstrap/app.php already routes all api/* exceptions through
 * shouldRenderJsonWhen).
 */
class ShipmentPolicy
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
