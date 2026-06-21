---
name: context:all-auth
description: "Dual-stack auth: NextAuth.js (frontend) + Laravel Sanctum (backend) — the Auth group entrypoint/router"
keywords: auth, authentication, login, logout, session, nextauth, sanctum, token, accessToken, role, permission, apk scanner
related: [context:all-database]
date: 21-06-26
---

# Auth Context

This file is the canonical Auth context entrypoint for wmslite.

Use it after `process/context/all-context.md` when the task needs login/session, role/permission, or token-handling awareness.

---

## Scope

This group covers:

- Dual-stack auth: the Next.js frontend uses NextAuth.js v5 (beta.31); the Laravel backend uses Sanctum 4.0 (bearer token).
- Login flow: `apps/frontend/src/lib/auth/auth.ts` — NextAuth's `CredentialsProvider` accepts email+password from the login form, then POSTs to `${NEXT_PUBLIC_LARAVEL_API_URL}/auth/login` (default fallback `http://localhost:8000/api`). If Laravel responds with `{token, user}`, NextAuth stores `token` as `accessToken` inside the JWT session.
- Session strategy: `"jwt"`, `maxAge: 8 * 60 * 60` (8 hours).
- Custom fields propagated through the NextAuth `jwt`/`session` callbacks: `accessToken` (Sanctum token), `user_id`, `type`, `admin`, `module`, `status` — all sourced from the `el_user` table (see `context:all-database`).
- Custom login page: `pages: { signIn: "/login", error: "/login" }`; route location `apps/frontend/src/app/(auth)/login/`.
- Backend: `AuthController` (`apps/backend/app/Http/Controllers/Api/AuthController.php`) exposes `POST /api/auth/login`, `POST /api/auth/logout` (requires `auth:sanctum`), `GET /api/auth/me` (requires `auth:sanctum`).
- All other API routes (shipments, inbound, outbound, reports, master, moving, monitoring, admin) are wrapped in `auth:sanctum` middleware in `routes/api.php`.
- Role/permission checks are based on a combination of `type` (0-3) + `admin` (boolean flag) + `module` (1=OTR, 2=Service) from `el_user` — NOT a separate roles table.
- **Policy/Gate implementation status — confirmed during go-live Phase 2 (21-06-26), MVP scope
  only.** Three Laravel Policy classes exist, registered in
  `apps/backend/app/Providers/AppServiceProvider.php::boot()` via `Gate::policy(...)` (this app has
  no `AuthServiceProvider` — that is not the registration point in Laravel 13):
  - `ApkUserPolicy` — gates `ApkController::resetPassword()` and `ApkController::logout()` (force
    logout). Rule: `$user->admin || in_array($user->type, [1, 3])`, matching the frontend's
    existing `canEdit` gate in `master/apk/page.tsx`. Uses class-string authorization
    (`$this->authorize('resetPassword', \App\Models\ApkUser::class)`) since `ApkController` only
    ever runs raw `DB::table()` queries, never fetching an `ApkUser` Eloquent instance.
  - `UserPolicy` — formalizes the prior ad hoc `if (!$request->user()->admin)` check across all 5
    `UserController` methods (`viewAny`/`create`/`update`/`resetPassword`/`delete`), all admin-only.
    Behavior is unchanged from before, just formalized as a Policy.
  - `MovingPolicy` — gates `MovingController::destroy` (destructive delete), admin-only.

  **This is deliberately scoped to the 3 highest-risk clusters, not full coverage — this is a known
  gap, not resolved work.** `InboundController`, `OutboundController`, `ShipmentController`, and all
  master-data CRUD controllers (locations, categories, recipients) still have **zero authorization
  checks** — any authenticated user can currently create/update/delete this data regardless of
  `type`/`admin`/`module`. `type`/`module` fields are never checked anywhere outside the 3 Policies
  above. Full Policy coverage for the remaining controllers is an explicit, documented Phase 3
  backlog item (see `process/features/go-live/backlog/`), not something to assume is in progress or
  planned without checking the backlog first.
- The APK Scanner has its OWN account system, separate from `el_user` (tables `el_apk`/`el_apk_s`, model `ApkUser.php`, managed via `ApkController.php` with reset-password and force-logout). This is not the same NextAuth/Sanctum session used by the dashboard web app — it's intended for a separate mobile scanner app that has NOT been built yet (only account management exists so far).

It does not cover:

- Login form UI components — see `process/features/` if a relevant feature folder exists, or read the code directly.
- Detailed schema of `el_user`/`el_apk` tables — see `context:all-database`.
- The APK Scanner application itself (not yet built).

## Read When

Read this entrypoint when:

- changing the login/logout/session flow
- adding a new role/permission check
- debugging an expired or invalid Sanctum token
- integrating the APK Scanner account system

## Quick Routing

- use `apps/frontend/src/lib/auth/auth.ts` for the NextAuth configuration, CredentialsProvider, and JWT/session callbacks
- use `apps/frontend/src/lib/auth/types.ts` and `apps/frontend/src/types/next-auth.d.ts` for the typed session/JWT shape
- use `apps/backend/app/Http/Controllers/Api/AuthController.php` for the Sanctum login/logout/me endpoints
- use `apps/backend/app/Http/Controllers/Api/ApkController.php` for the separate APK Scanner account management (reset password, force logout)
- use `apps/backend/routes/api.php` for which routes require `auth:sanctum`
- use `apps/backend/app/Policies/{ApkUserPolicy,UserPolicy,MovingPolicy}.php` for the 3 existing Policy classes, and `apps/backend/app/Providers/AppServiceProvider.php::boot()` for their registration

## Source Paths

- `apps/frontend/src/lib/auth/auth.ts`
- `apps/frontend/src/lib/auth/types.ts`
- `apps/frontend/src/types/next-auth.d.ts`
- `apps/frontend/src/app/(auth)/login/`
- `apps/frontend/src/app/api/auth/`
- `apps/backend/app/Http/Controllers/Api/AuthController.php`
- `apps/backend/app/Http/Controllers/Api/ApkController.php`
- `apps/backend/app/Models/User.php`
- `apps/backend/app/Models/ApkUser.php`
- `apps/backend/routes/api.php`
- `apps/backend/app/Policies/ApkUserPolicy.php`
- `apps/backend/app/Policies/UserPolicy.php`
- `apps/backend/app/Policies/MovingPolicy.php`
- `apps/backend/app/Providers/AppServiceProvider.php`

## Update Triggers

Update this group when:

- the auth provider changes (e.g. OAuth is added)
- Policy coverage expands beyond the current 3 clusters (Inbound/Outbound/Shipment/master-data CRUD — Phase 3 backlog)
- the APK Scanner app begins development and needs its own auth flow

## Canonical Notes

- use `process/context/auth/all-auth.md` as the entrypoint
- no deeper files exist yet in this group; promote subtopics here if this file exceeds ~800 lines or gains 3+ durable sub-docs
