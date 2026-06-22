---
name: context:all-auth
description: "Dual-stack auth: NextAuth.js (frontend) + Laravel Sanctum (backend) — the Auth group entrypoint/router"
keywords: auth, authentication, login, logout, session, nextauth, sanctum, token, accessToken, role, permission, apk scanner
related: [context:all-database]
date: 22-06-26
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
- **Policy/Gate implementation status — extended during go-live Phase 3 (22-06-26); originally
  scoped in Phase 2 (21-06-26).** Five Laravel Policy classes exist, registered in
  `apps/backend/app/Providers/AppServiceProvider.php::boot()` via `Gate::policy(...)` (this app has
  no `AuthServiceProvider` — that is not the registration point in Laravel 13):
  - `ApkUserPolicy` — gates `ApkController::resetPassword()`/`logout()` (Phase 2) plus
    `store()`/`update()`/`delete()` (Phase 3). Rule: `$user->admin || in_array($user->type, [1, 3])`,
    matching the frontend's existing `canEdit` gate in `master/apk/page.tsx`. Uses class-string
    authorization (`$this->authorize('store', \App\Models\ApkUser::class)`) since `ApkController`
    only ever runs raw `DB::table()` queries, never fetching an `ApkUser` Eloquent instance.
  - `UserPolicy` — formalizes the prior ad hoc `if (!$request->user()->admin)` check across all 5
    `UserController` methods (`viewAny`/`create`/`update`/`resetPassword`/`delete`), all admin-only.
    Behavior is unchanged from before, just formalized as a Policy. (Phase 2)
  - `MovingPolicy` — gates `MovingController::destroy` (Phase 2) and `MovingController::store`
    (Phase 3), both admin-only.
  - `InboundPolicy` (Phase 3, new) — gates `InboundController::update()`/`destroy()`, admin-only.
    Uses instance-form authorization (`$this->authorize('update', $inbound)`).
  - `OutboundPolicy` (Phase 3, new) — gates `OutboundController::update()`/`destroy()`, admin-only,
    same instance-form pattern as `InboundPolicy`.
  - `ShipmentPolicy` (Phase 3, new) — gates `ShipmentController::update()`/`destroy()`, admin-only.
    **Structural exception:** no `Shipment` Eloquent model exists in this codebase —
    `ShipmentController` operates on `Inbound::class` (`from_shipment=1` scope), which is already
    bound to `InboundPolicy` via `Gate::policy()`. Registering `ShipmentPolicy` against
    `Inbound::class` too would silently shadow that binding. `ShipmentPolicy` is therefore NOT
    registered via `Gate::policy()` — it is invoked directly through a private
    `authorizeShipment()` helper in `ShipmentController` that calls
    `app(ShipmentPolicy::class)->{$ability}($user)` and throws
    `Illuminate\Auth\Access\AuthorizationException` manually on denial. Functionally identical 403
    response shape (via the app's existing `shouldRenderJsonWhen` rendering), just not Gate-routed.

  **Residual gap (narrower after Phase 3, still real):** `store`/`index`/`show` on
  `InboundController`/`OutboundController`/`ShipmentController`, and ALL methods on every
  master-data CRUD controller (locations, categories, recipients), still have **zero authorization
  checks** — any authenticated user can currently create or read this data regardless of
  `type`/`admin`/`module`. `type`/`module` fields are never checked anywhere outside the Policies
  above. See `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` — deferred
  pending a product decision on who may create/read records per module; do not assume this is in
  progress or planned without checking the backlog first.

- **Token storage — changed in Phase 3 (22-06-26): the Sanctum bearer token never touches browser
  storage.** `apps/frontend/src/components/auth/token-sync.tsx` (which previously bridged the
  NextAuth JWT into `sessionStorage`/`localStorage` so the legacy `apiClient` singleton could read
  it) was deleted entirely — this closed a Critical XSS-exfiltration finding from the Phase 3
  security audit. All 9 `*.service.ts` files now use `createAuthenticatedClient(token)` with
  `token` as the last parameter, instead of the old `apiClient` singleton. Every caller (client
  components only — confirmed zero server-component callers across the codebase) obtains the token
  via `useSession()` and passes `session?.user?.accessToken` through. Any new frontend feature work
  must follow this pattern — there is no longer an ambient-token singleton to fall back to.

- **Sanctum token expiration — set in Phase 3 (22-06-26).** `apps/backend/config/sanctum.php`
  `expiration` changed from `null` (infinite) to `480` (minutes = 8 hours), matching the frontend
  NextAuth session `maxAge`.

- **Login rate limiting — added in Phase 3 (22-06-26).** A named rate limiter
  (`RateLimiter::for('login', ...)`, keyed on `email+ip`, 5 attempts/minute) is registered in
  `AppServiceProvider::boot()` and applied via `throttle:login` middleware on `POST /api/auth/login`.
  The 429 response uses Laravel's default shape (`{"message": "..."}`), which matches every other
  error response in this app — there is no dedicated `{data, message, status}` envelope anywhere in
  the actual codebase despite the aspirational API convention documented elsewhere. **Open
  concern for Phase 4:** the rate limiter's cache backend must be shared/consistent across however
  many app instances a future deployment runs, or the limiter silently degrades under horizontal
  scaling — not yet confirmed.
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
- use `apps/backend/app/Policies/{ApkUserPolicy,UserPolicy,MovingPolicy,InboundPolicy,OutboundPolicy,ShipmentPolicy}.php` for the 6 existing Policy classes, and `apps/backend/app/Providers/AppServiceProvider.php::boot()` for their registration (ShipmentPolicy is the one exception — invoked directly, not Gate-registered, see above)
- use `apps/frontend/src/lib/api/client.ts` for `createAuthenticatedClient(token)` — the sole authenticated-call pattern since Phase 3's token-storage removal; no ambient-token singleton exists anymore
- use `apps/backend/config/sanctum.php` for token expiration (480 min) and `apps/backend/routes/api.php` for the `throttle:login` rate limiter

## Source Paths

- `apps/frontend/src/lib/auth/auth.ts`
- `apps/frontend/src/lib/auth/types.ts`
- `apps/frontend/src/types/next-auth.d.ts`
- `apps/frontend/src/app/(auth)/login/`
- `apps/frontend/src/app/api/auth/`
- `apps/frontend/src/lib/api/client.ts`
- `apps/backend/app/Http/Controllers/Api/AuthController.php`
- `apps/backend/app/Http/Controllers/Api/ApkController.php`
- `apps/backend/app/Models/User.php`
- `apps/backend/app/Models/ApkUser.php`
- `apps/backend/routes/api.php`
- `apps/backend/app/Policies/ApkUserPolicy.php`
- `apps/backend/app/Policies/UserPolicy.php`
- `apps/backend/app/Policies/MovingPolicy.php`
- `apps/backend/app/Policies/InboundPolicy.php`
- `apps/backend/app/Policies/OutboundPolicy.php`
- `apps/backend/app/Policies/ShipmentPolicy.php`
- `apps/backend/app/Providers/AppServiceProvider.php`
- `apps/backend/config/sanctum.php`

## Update Triggers

Update this group when:

- the auth provider changes (e.g. OAuth is added)
- Policy coverage expands further (`store`/`index`/`show` on Inbound/Outbound/Shipment, or any
  master-data CRUD controller — see `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md`)
- a CSP header is added (currently deferred — `process/features/go-live/backlog/csp-header_NOTE_22-06-26.md`)
- the APK Scanner app begins development and needs its own auth flow

## Canonical Notes

- use `process/context/auth/all-auth.md` as the entrypoint
- no deeper files exist yet in this group; promote subtopics here if this file exceeds ~800 lines or gains 3+ durable sub-docs
