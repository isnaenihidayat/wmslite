<?php

namespace App\Providers;

use App\Models\ApkUser;
use App\Models\Moving;
use App\Models\User;
use App\Policies\ApkUserPolicy;
use App\Policies\MovingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
    }
}
