<?php

namespace App\Providers;

use App\Models\ApkUser;
use App\Models\Inbound;
use App\Models\Moving;
use App\Models\Outbound;
use App\Models\User;
use App\Policies\ApkUserPolicy;
use App\Policies\InboundPolicy;
use App\Policies\MovingPolicy;
use App\Policies\OutboundPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // go-live Phase 2, Step B1 — MVP Policy hardening (3 highest-risk
        // clusters only). This Laravel 13 app has no AuthServiceProvider;
        // registration happens here per validate-contract Execute-Agent
        // Instruction E1.
        Gate::policy(ApkUser::class, ApkUserPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Moving::class, MovingPolicy::class);

        // go-live Phase 3, Section 1 — CRUD authorization gap (update/destroy
        // only). ShipmentPolicy is intentionally NOT registered here:
        // ShipmentController operates on the same Inbound::class model as
        // InboundController, and Gate::policy() is keyed by model class, so
        // registering ShipmentPolicy against Inbound::class would silently
        // shadow InboundPolicy above. See ShipmentPolicy's class doc comment
        // for the full rationale — ShipmentController invokes it directly.
        Gate::policy(Inbound::class, InboundPolicy::class);
        Gate::policy(Outbound::class, OutboundPolicy::class);

        // go-live Phase 3, Section 3 — Login rate limiting. This Laravel 13
        // app has no dedicated RouteServiceProvider, so the named limiter is
        // registered here. Keyed on email+ip (not IP alone) so a single
        // attacker IP cannot lock out all users sharing that IP, and so
        // rotating IPs against one known email is still throttled.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });
    }
}
