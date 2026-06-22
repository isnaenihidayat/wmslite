---
name: plan:go-live-phase-03-security-audit
description: "WMS Lite go-live — Phase 3: Security & Validation Audit"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-03
---

# Phase 03 — Security & Validation Audit

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ✅ VERIFIED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

WMS Lite has never had a security review. Before any deployment work (Phase 4), the app needs an
OWASP-style audit of its auth, input validation, and API surface — especially because Phase 2 just
finished formalizing the role/permission model, which is exactly the kind of change that should be
re-checked by a security pass rather than trusted blindly.

RESEARCH + INNOVATE (22-06-26) completed the `vc-security` audit and produced 9 actionable findings
plus 1 info-only item. All 9 decisions below are user-approved. See the Inner Loop Refresh Note for
how this expanded scope differs from the original generic Step A-C plan.

---

## Entry Gate

- Phase 1 + Phase 2 exit gates passed
- Phase 2 report read for the confirmed role/permission model and any Policy classes added

---

## Blast Radius

**Backend — new Policy classes:**
- `apps/backend/app/Policies/InboundPolicy.php` (new)
- `apps/backend/app/Policies/OutboundPolicy.php` (new)
- `apps/backend/app/Policies/ShipmentPolicy.php` (new)

**Backend — extended Policy classes:**
- `apps/backend/app/Policies/ApkUserPolicy.php` (add `store`/`update`/`destroy` methods)
- `apps/backend/app/Policies/MovingPolicy.php` (add `store` method)

**Backend — controllers gated:**
- `apps/backend/app/Http/Controllers/Api/InboundController.php` (`update`, `destroy` only)
- `apps/backend/app/Http/Controllers/Api/OutboundController.php` (`update`, `destroy` only)
- `apps/backend/app/Http/Controllers/Api/ShipmentController.php` (`update`, `destroy` only)
- `apps/backend/app/Http/Controllers/Api/ApkController.php` (`store`/`update`/`destroy` policy calls added)
- `apps/backend/app/Http/Controllers/Api/MovingController.php` (`store` policy call added)
- `apps/backend/app/Http/Controllers/Api/ReportController.php` (module-scope filtering)
- `apps/backend/app/Http/Controllers/Api/MonitoringController.php` (module-scope filtering)
- `apps/backend/app/Http/Controllers/Api/DashboardController.php` (module-scope filtering)
- `apps/backend/app/Http/Controllers/Api/MasterController.php` (module-scope filtering)

**Backend — new shared module-filtering trait:**
- `apps/backend/app/Traits/FiltersByUserModule.php` (new — exact name decided at EXECUTE if a
  better convention-matching name is found; must be a trait, not ad hoc per-method `where()`)

**Backend — registration, config, infra:**
- `apps/backend/app/Providers/AppServiceProvider.php` (register `InboundPolicy`, `OutboundPolicy`,
  `ShipmentPolicy`)
- `apps/backend/config/sanctum.php` (`expiration` → `480`)
- `apps/backend/config/cors.php` (new — published via `php artisan config:publish cors` or created
  manually; `allowed_origins` set to frontend dev origin, `supports_credentials: false`)
- `apps/backend/app/Http/Middleware/SecurityHeaders.php` (new — `X-Frame-Options`,
  `X-Content-Type-Options`, `Referrer-Policy`)
- `apps/backend/bootstrap/app.php` (register `SecurityHeaders` middleware in the `$middleware->api()`
  or `$middleware->append()` chain, alongside existing `ForceJsonResponse`/`HandleCors`)
- `apps/backend/routes/api.php` (apply `throttle` middleware to `POST /api/auth/login`)
- Rate limiter definition for login (custom named limiter, e.g. `RateLimiter::for('login', ...)`
  keyed on `email+ip`) — registered in `apps/backend/app/Providers/AppServiceProvider.php` `boot()`
  (Laravel 13 has no dedicated `RouteServiceProvider`; this app's only provider is
  `AppServiceProvider`)
- `apps/backend/app/Exceptions/Handler.php` or `bootstrap/app.php` `withExceptions()` block (only if
  needed to reshape the default 429 `ThrottleRequestsException` response into the app's
  `{data, message, status}` shape — confirm at EXECUTE whether Laravel 13's default exception
  rendering already honors `shouldRenderJsonWhen` enough, or whether a custom `render()` override is
  required)

**Frontend — token storage removal:**
- `apps/frontend/src/components/auth/token-sync.tsx` (DELETE entire file)
- `apps/frontend/src/app/(dashboard)/layout.tsx` (remove `TokenSync` import + `<TokenSync />` mount)
- `apps/frontend/src/lib/api/client.ts` (remove `sessionStorage`/`localStorage` token read/write:
  delete `setAuthToken`, `clearAuthToken`, the request interceptor's storage read, and the 401
  response interceptor's storage clear; keep `createAuthenticatedClient(token)` as the sole
  authenticated-call pattern; keep `toFormData` helper unchanged)
- `apps/frontend/src/lib/api/apk.service.ts` (convert from `apiClient` singleton to
  `createAuthenticatedClient(token)` pattern)
- `apps/frontend/src/lib/api/master.service.ts` (same conversion)
- `apps/frontend/src/lib/api/dashboard.service.ts` (same conversion)
- `apps/frontend/src/lib/api/monitoring.service.ts` (same conversion)
- `apps/frontend/src/lib/api/shipment.service.ts` (same conversion)
- `apps/frontend/src/lib/api/moving.service.ts` (same conversion)
- `apps/frontend/src/lib/api/user.service.ts` (same conversion)
- `apps/frontend/src/lib/api/inbound.service.ts` (same conversion)
- `apps/frontend/src/lib/api/outbound.service.ts` (same conversion)
- Every caller of the above 9 service functions across `apps/frontend/src/app/(dashboard)/**` and
  `apps/frontend/src/hooks/**` (exact list to be enumerated at EXECUTE via
  `grep -rln "from \"@/lib/api/<module>.service\"" apps/frontend/src` per service file) — callers
  must now supply a token (via `useSession()`/`getSession()`) to each service call instead of
  relying on the ambient `apiClient` singleton

**Frontend — security headers:**
- `apps/frontend/next.config.ts` (add `headers()` config function with the same 3 basic headers)

**Security audit report (new, durable):**
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md`

**Backlog (new, durable — not in this phase's code blast radius):**
- `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` (new — backlog
  `store`/`index`/`show` access control for Inbound/Outbound/Shipment, rationale: no clear business
  rule yet for who may create per module)
- `process/features/go-live/backlog/csp-header_NOTE_22-06-26.md` (new — backlog CSP, rationale:
  needs dedicated testing against rendered pages, risk of breaking Next.js inline hydration)

---

## Implementation Checklist

### 1 — CRUD authorization gap (Critical) — EXECUTED 22-06-26

- [x] 1a. Create `apps/backend/app/Policies/InboundPolicy.php` with `update(User $user): bool` and
      `delete(User $user): bool` methods, admin-only (`return $user->admin;`), matching the
      `MovingPolicy`/`UserPolicy` admin-only pattern — no other signal in the codebase indicates a
      narrower or `type`-based rule for Inbound.
- [x] 1b. Create `apps/backend/app/Policies/OutboundPolicy.php` — same shape, admin-only.
- [x] 1c. Create `apps/backend/app/Policies/ShipmentPolicy.php` — same shape, admin-only.
- [x] 1d. Register all 3 new policies in `apps/backend/app/Providers/AppServiceProvider.php`
      `boot()`: `Gate::policy(Inbound::class, InboundPolicy::class)`, `Gate::policy(Outbound::class,
      OutboundPolicy::class)`, `Gate::policy(Shipment::class, ShipmentPolicy::class)` — alongside
      the 3 existing Phase 2 registrations (do not remove them).
      **EXECUTE-time deviation (22-06-26):** there is no `Shipment` Eloquent model in this codebase —
      `ShipmentController` operates on `Inbound::class` (`el_inbound_header`, `from_shipment=1`
      scope). `Gate::policy()` is keyed by model class, and `Inbound::class` is already bound to
      `InboundPolicy`; registering `ShipmentPolicy` against `Inbound::class` too would silently
      shadow that binding (last registration wins). Only `InboundPolicy` and `OutboundPolicy` were
      registered via `Gate::policy()`. `ShipmentPolicy` was created as planned but is invoked
      directly by `ShipmentController` (see 1h note) rather than through the Gate facade — see
      `ShipmentPolicy`'s class doc comment for full rationale.
- [x] 1e. In `InboundController::update()`, add `$this->authorize('update', Inbound::class);` as the
      first line of the method body (class-string form, matching `UserController`/`MovingController`
      convention — confirm whether `InboundController` already fetches an Eloquent `Inbound`
      instance inside `update`/`destroy`; if it does, prefer the instance form
      `$this->authorize('update', $inbound);` instead, consistent with whichever form avoids a
      duplicate query).
      **Confirmed at EXECUTE:** `InboundController` already fetches the Eloquent instance in both
      methods — used the instance form (`$this->authorize('update', $inbound);`) to avoid a
      duplicate query, per the plan's own stated preference.
- [x] 1f. In `InboundController::destroy()`, add `$this->authorize('delete', Inbound::class);` (or
      instance form per 1e's finding) as the first line.
- [x] 1g. Repeat 1e/1f for `OutboundController::update()` / `OutboundController::destroy()` using
      `OutboundPolicy`. Instance form used here too, consistent with 1e.
- [x] 1h. Repeat 1e/1f for `ShipmentController::update()` / `ShipmentController::destroy()` using
      `ShipmentPolicy`. Do NOT touch `ShipmentController::store()`, `index()`, or `show()` — those
      stay ungated this phase.
      **EXECUTE-time deviation (22-06-26):** per 1d's finding, `ShipmentController` cannot call
      `$this->authorize(...)` against `ShipmentPolicy` (that resolves via the Gate, which is bound to
      `InboundPolicy` for `Inbound::class`). Added a private `authorizeShipment(string $ability,
      Request $request): void` helper that calls `app(ShipmentPolicy::class)->{$ability}($user)`
      directly and throws `Illuminate\Auth\Access\AuthorizationException` manually on denial —
      Laravel's existing `shouldRenderJsonWhen` (`bootstrap/app.php`) renders this identically to a
      Gate-based 403, so the response shape is unchanged.
- [x] 1i. Add Feature tests asserting non-admin users get 403 on `update`/`destroy` for all 3
      controllers, and admin users succeed — follow the existing
      `apps/backend/tests/Feature/MovingControllerTest.php` pattern for the assertion shape.
      Added `test_update_returns_403_for_non_admin` / `test_destroy_returns_403_for_non_admin` to
      `InboundControllerTest`, `OutboundControllerTest`, `ShipmentControllerTest`; updated all
      pre-existing `update`/`destroy` happy-path tests in those 3 files from `actingUser()` to
      `actingAdmin()` since those actions are now admin-gated. All 44 tests across the 3 files pass
      (`php artisan test --filter='InboundControllerTest|OutboundControllerTest|ShipmentControllerTest'`
      — 44 tests, 106 assertions, 0 failures). Full suite (`composer test`) — 171 tests, 443
      assertions, 0 failures, confirming Phase 1/2 regression surfaces (including
      `MovingControllerTest`, `ApkControllerTest`, `UserControllerTest`) are unaffected.
- [x] 1j. Write `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` documenting
      that `store`/`index`/`show` access control for Inbound/Outbound/Shipment (and all master-data
      CRUD) is deferred — rationale: no confirmed business rule yet for who may create records per
      module (OTR vs Service), needs a separate user/product decision. Written at EXECUTE
      (22-06-26).

### 2 — Token storage removal (Critical) — EXECUTED 22-06-26

- [x] 2a. Delete `apps/frontend/src/components/auth/token-sync.tsx` entirely.
      Deleted at EXECUTE (22-06-26).
- [x] 2b. In `apps/frontend/src/app/(dashboard)/layout.tsx`, remove the `TokenSync` import and the
      `<TokenSync />` JSX mount.
      Done — `SessionProvider` now wraps children directly with no `TokenSync` mount.
- [x] 2c. In `apps/frontend/src/lib/api/client.ts`: remove `setAuthToken()`, `clearAuthToken()`, the
      request interceptor (the `sessionStorage`/`localStorage` read block), and the response
      interceptor's storage-clear-on-401 block. Keep `apiClient` export only if anything still needs
      an unauthenticated client (confirm at EXECUTE whether anything calls plain `apiClient` for
      genuinely public endpoints, e.g. `login` itself — if so, keep a minimal unauthenticated
      `apiClient` with no token logic at all). Keep `createAuthenticatedClient(token)` and
      `toFormData()` unchanged.
      **Confirmed at EXECUTE:** login uses a raw `fetch()` call in `lib/auth/auth.ts`, not
      `apiClient` — `apiClient` has zero current callers anywhere in the codebase post-conversion.
      Kept as a minimal unauthenticated export per the plan's permissive wording (safe fallback, no
      token logic at all). `createAuthenticatedClient(token)` and `toFormData()` unchanged.
- [x] 2d. Convert `apps/frontend/src/lib/api/apk.service.ts` from the `apiClient` singleton pattern
      to functions that accept a `token: string` parameter and call
      `createAuthenticatedClient(token)` internally (or accept a pre-built client instance — decide
      whichever matches the majority pattern found across all 9 files at EXECUTE time for
      consistency). **Note added at VALIDATE (22-06-26): no existing call-site precedent exists for
      `createAuthenticatedClient(token)` outside `client.ts` itself — `apk.service.ts` will be the
      first real usage anywhere in this codebase. Treat its caller-update pass (see 2m) as the
      template for the remaining 8 service files.**
      **Confirmed at EXECUTE:** chose `token: string` as the function's **last** parameter
      (consistent across all 9 files, including pre-existing positional args like `id`/`form`) —
      `createAuthenticatedClient(token)` built fresh inside each function body. Verified via scoped
      `tsc --noEmit` before moving to the next file, per plan instruction.
- [x] 2e. Repeat 2d for `master.service.ts`.
      Done — `fetchLocations`, `fetchCategories`, `fetchRecipients` each now take `(token: string)`.
- [x] 2f. Repeat 2d for `dashboard.service.ts`.
      Done — `fetchDashboardStats(token)`, `fetchReport(params, token)`.
- [x] 2g. Repeat 2d for `monitoring.service.ts`.
      Done — `fetchMonitoringList(params, token)`.
- [x] 2h. Repeat 2d for `shipment.service.ts`.
      Done — `fetchShipmentList`, `fetchShipment`, `createShipment`, `updateShipment`,
      `deleteShipment`, `pushToInbound` all take `token` as the last parameter.
- [x] 2i. Repeat 2d for `moving.service.ts`.
      Done — `fetchMovingList`, `createMoving`, `deleteMoving`.
- [x] 2j. Repeat 2d for `user.service.ts`.
      Done — `fetchUserList`, `createUser`, `updateUser`, `resetUserPassword`, `deleteUser`.
- [x] 2k. Repeat 2d for `inbound.service.ts`.
      Done — `fetchInboundList`, `fetchInbound`, `fetchInboundDetails`, `createInbound`,
      `updateInbound`, `deleteInbound`.
- [x] 2l. Repeat 2d for `outbound.service.ts`.
      Done — `fetchOutboundList`, `fetchOutbound`, `fetchOutboundWithDetails`, `createOutbound`,
      `updateOutbound`, `deleteOutbound`.
- [x] 2m. For each of the 9 service files, find every caller (`grep -rln "from \"@/lib/api/<module>
      .service\"" apps/frontend/src`) and update the call site to obtain the session token (via
      `useSession()` in client components, `auth()`/`getSession()` in server contexts) and pass it
      through. Treat this as 9 separate sub-passes — one per service module — so each can be
      verified independently before moving to the next. **Note added at VALIDATE (22-06-26): caller
      counts confirmed via grep — apk=2, master=6, dashboard=2, monitoring=1, shipment=4, moving=2,
      user=2, inbound=5, outbound=5 (29 caller files total). No existing in-repo pattern exists for
      passing a token into a service call (see 2d note) — verify server-component (`auth()`) vs
      client-component (`useSession()`) token retrieval separately, since both contexts exist across
      the 29 files.**
      **Confirmed at EXECUTE:** all 29 caller files were client components (`"use client"`) — zero
      server-component (`auth()`) callers exist anywhere across the 9 modules; every caller uses
      `useSession()` and reads `session?.user?.accessToken`. 3 of the 29 caller files
      (`shipment/_components/columns.tsx`, `inbound/_components/columns.tsx`,
      `outbound/_components/columns.tsx`) only `import type` from their respective service file
      (no actual function calls) and required zero code changes. 9 sub-passes executed in order
      (apk → master → dashboard → monitoring → shipment → moving → user → inbound → outbound), each
      verified with a scoped `tsc --noEmit` before proceeding to the next. Transitively-affected
      callers not captured by the direct grep (e.g. `dashboard/page.tsx`, which imports
      `useDashboardStats` from the hook rather than the service directly) were also found and
      updated during their respective sub-pass.
- [x] 2n. After all callers are converted, grep the whole frontend for any remaining
      `sessionStorage`/`localStorage` token reference (`grep -rn "wms_access_token"
      apps/frontend/src`) and confirm zero matches.
      **Confirmed at EXECUTE:** `grep -rn "wms_access_token" apps/frontend/src` — zero matches
      (exit code 1).
- [x] 2o. Add/update frontend tests covering at least one converted service file's token-passing
      call path (smoke-level, matching existing Vitest coverage depth from Phase 1). **Note added at
      VALIDATE (22-06-26): existing `*.page.test.tsx` files mock at the service-function boundary
      (e.g. `vi.mock("@/lib/api/inbound.service", ...)`), not at `apiClient`/`client.ts` directly —
      confirmed no test file imports an unmocked service module. The token-passing signature change
      is unlikely to break existing component-test mocks at runtime, but WILL surface as a
      TypeScript build error if a mock's typed stub no longer matches the new exported signature —
      `npm run build` (already in the Exit Gate) is the correct gate for that risk, not `npm run
      test` alone.**
      **Confirmed at EXECUTE:** the VALIDATE prediction was correct in spirit but the actual failure
      mode surfaced at `npm run test` runtime, not at build time — 4 page components
      (`recipient`, `locations`, `product-category`, `monitoring`) had never called `useSession()`
      before this change (their queries previously fired unconditionally via the singleton
      `apiClient`). Adding `useSession()` + `enabled: !!token` to these pages broke their existing
      tests with `useSession must be wrapped in a <SessionProvider />`, since those 4 test files had
      no `next-auth/react` mock. Fixed by adding the same `vi.mock("next-auth/react", ...)` pattern
      already used by `inbound/page.test.tsx` and others, now including `accessToken: "test-token"`
      in the mocked session — this both fixes the regression and gives explicit token-passing
      coverage (the `enabled: !!token` gate now proves the token reaches the query). All 14 test
      files / 19 tests pass (`npm run test`). `npm run build` also confirmed green (TypeScript
      compiles, all 17 routes generate).

### 3 — Login rate limiting (High) — EXECUTED 22-06-26

- [x] 3a. Define a named rate limiter for login in `apps/backend/app/Providers/AppServiceProvider.php`
      `boot()`, e.g. `RateLimiter::for('login', fn (Request $request) =>
      Limit::perMinute(5)->by($request->input('email').'|'.$request->ip()));` — key MUST combine
      email + IP, not IP alone.
- [x] 3b. Apply the limiter to the login route in `apps/backend/routes/api.php`:
      `Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')
      ->name('auth.login');`
- [x] 3c. Confirm the 429 response shape matches `{data, message, status}`. If Laravel 13's default
      `ThrottleRequestsException` rendering does not already produce this shape under
      `shouldRenderJsonWhen` (already configured in `bootstrap/app.php`), add a targeted `render()`
      override in `apps/backend/app/Exceptions/Handler.php` (or a `withExceptions()` closure in
      `bootstrap/app.php`, whichever the app's existing convention favors — check if
      `app/Exceptions/Handler.php` exists first) that catches `ThrottleRequestsException` and
      reshapes it to `{data: null, message: '...', status: 429}`. **Confirmed at VALIDATE (22-06-26):
      `app/Exceptions/Handler.php` does NOT exist in this codebase — use the `withExceptions()`
      closure in `bootstrap/app.php` alongside the existing `shouldRenderJsonWhen` registration.**
      **Confirmed at EXECUTE (22-06-26):** empirically probed the default 429 response — Laravel 13's
      default `ThrottleRequestsException` rendering produces `{"message": "Too Many Attempts."}`
      (plus debug-mode trace fields under `APP_DEBUG=true`, irrelevant to shape). This `{"message":
      ...}` core shape is **identical** to every other error response already in this app (the 403
      inactive-user response and the 422 validation-error responses both use this same bare
      `{"message": ...}` shape — confirmed via `AuthControllerTest`). No controller anywhere in this
      codebase actually emits the aspirational `{data, message, status}` envelope (grepped for
      `'status' =>` usages — none match that pattern; `UserController`'s `status` field refers to a
      user account status, not an HTTP envelope field). Adding a custom `withExceptions()` override
      to force a `{data, message, status}` shape would have made the 429 response *inconsistent*
      with every other error response in the app, not more consistent. No override added — default
      rendering already satisfies the actual (not aspirational) shape convention.
- [x] 3d. Add a Feature test asserting the 6th login attempt within a minute for the same email+IP
      returns 429 in the app's standard response shape.
      Added `test_login_sixth_attempt_within_one_minute_returns_429` to `AuthControllerTest` —
      asserts 5 failed attempts return 422 (wrong password) and the 6th returns 429 with a `message`
      key present, matching the existing test assertion style (status + targeted JSON path, not
      full-body equality). All 12 tests in `AuthControllerTest` pass
      (`php artisan test --filter=AuthControllerTest` — 12 tests, 64 assertions, 0 failures).

### 4 — Extend ApkUserPolicy (High) — EXECUTED 22-06-26

- [x] 4a. Add `store(User $user): bool`, `update(User $user): bool`, and `destroy(User $user): bool`
      methods to `apps/backend/app/Policies/ApkUserPolicy.php`, matching the existing
      `resetPassword`/`logout` rule (`$user->admin || in_array($user->type, [1, 3])`).
      **Confirmed at EXECUTE:** added as `store`, `update`, and `delete` (see 4b note below for the
      `delete` naming decision) — all three use the identical rule expression as `resetPassword`/
      `logout`.
- [x] 4b. In `ApkController`, add `$this->authorize('store', ApkUser::class);`,
      `$this->authorize('update', ApkUser::class);`, and `$this->authorize('destroy', ApkUser::class);`
      (or `'delete'` if following the `MovingPolicy`/`UserPolicy` naming convention where the
      controller's `destroy()` method maps to a Policy method named `delete` — confirm and use
      whichever name the Policy actually declares, for consistency name it `delete` to match
      existing convention) to the corresponding controller methods that are not yet gated (Phase 2
      only gated `resetPassword`/`logout`). **Confirmed at VALIDATE (22-06-26): `ApkController` uses
      raw `DB::table()` queries exclusively — it never fetches an Eloquent `ApkUser` instance
      anywhere in the file — so the class-string form is the only viable form for all 3 new checks,
      not just a convention preference.**
      **Confirmed at EXECUTE:** used `delete` (not `destroy`) as the Policy method name, matching
      `MovingPolicy`/`UserPolicy` convention. `ApkController::destroy()` calls
      `$this->authorize('delete', ApkUser::class);`; `store()`/`update()` call
      `$this->authorize('store'|'update', ApkUser::class);` — all class-string form, all as the
      first line of the method body.
- [x] 4c. Add Feature tests for the 3 new gated methods (non-admin/non-type-1-3 gets 403, allowed
      roles succeed).
      Added `test_store_returns_403_for_unauthorized_user`, `test_update_returns_403_for_unauthorized_user`,
      `test_destroy_returns_403_for_unauthorized_user` to `ApkControllerTest` — happy-path coverage
      for `store`/`update`/`destroy` already existed and uses `actingUser()` (factory default
      `type: 1`, which is already authorized per the policy rule), so no happy-path test changes were
      needed. All 23 tests in `ApkControllerTest` pass
      (`composer test -- --filter=ApkControllerTest` — 23 tests, 70 assertions, 0 failures).

### 5 — Token expiration (High) — EXECUTED 22-06-26

- [x] 5a. Set `apps/backend/config/sanctum.php` `'expiration' => 480,` (480 minutes = 8 hours,
      matching the frontend NextAuth `maxAge` of 8 hours). **Confirmed at VALIDATE (22-06-26): current
      value is `null` (no expiration) — change is real and necessary.**
      Set at EXECUTE (22-06-26).
- [x] 5b. Confirm no test or seeded fixture relies on an infinite-lifetime token; adjust if found.
      Grepped `tests/` and `database/factories/` for `expir`/`expires_at` — zero matches. The only
      `createToken()` calls in the suite (`AuthControllerTest`) don't pass an explicit expiration
      argument, so they inherit the new 480-minute config default with no behavior change needed.
      No test/fixture adjustment required.
- [x] 5c. No token scope/ability changes — explicitly out of scope per the approved decision
      (duplicates existing Policy coverage). Confirmed — no scope/ability code touched.

### 6 — Extend MovingPolicy (High) — EXECUTED 22-06-26

- [x] 6a. Add `store(User $user): bool` to `apps/backend/app/Policies/MovingPolicy.php`, admin-only
      (`return $user->admin;`), matching the existing `delete` method's rule.
- [x] 6b. In `MovingController::store()`, add `$this->authorize('store', Moving::class);` as the
      first line.
- [x] 6c. Add a Feature test asserting non-admin gets 403 on `store`, admin succeeds.
      Added `test_store_returns_403_for_non_admin` to `MovingControllerTest`; converted the
      pre-existing happy-path `test_store_creates_moving_record_with_current_user` and
      `test_store_requires_required_fields` from `actingUser()` to `actingAdmin()` since `store` is
      now admin-gated. All 10 tests in `MovingControllerTest` pass
      (`composer test -- --filter=MovingControllerTest` — 10 tests, 30 assertions, 0 failures).

### 7 — Read-only endpoint module gating (Medium) — DEFERRED TO BACKLOG (21-06-26)

**Not mechanically feasible as originally scoped — confirmed via direct schema verification that no
data table carries a `module` column (only `el_user` does). This needs a data-model decision, not a
security fix. Deferred to `process/features/go-live/backlog/module-scoped-data-filtering_NOTE_21-06-26.md`.
Items 7a-7f below are VOID — do not execute. Sections 1-6, 8, 9 are unaffected and proceed normally.**

- [x] 7a-7f VOID — see backlog note above. (Previously planned: a shared
      `FiltersByUserModule` trait scoping reads by `$user->module` across Report/Monitoring/Dashboard/
      Master controllers — abandoned because the join target does not exist in the schema.)

### 8 — Basic security headers (Medium) — EXECUTED 22-06-26

- [x] 8a. Create `apps/backend/app/Http/Middleware/SecurityHeaders.php` setting
      `X-Frame-Options: DENY` (or `SAMEORIGIN` — confirm no embedding use case first),
      `X-Content-Type-Options: nosniff`, and `Referrer-Policy: strict-origin-when-cross-origin` on
      every response, matching the `ForceJsonResponse` middleware's shape (`handle(Request $request,
      Closure $next): Response`).
      Used `DENY` — no legitimate embedding/iframe use case found for this app (internal WMS, not
      meant to be framed by other sites).
- [x] 8b. Register the middleware in `apps/backend/bootstrap/app.php` alongside the existing
      `ForceJsonResponse`/`HandleCors` registrations.
      Registered via `$middleware->append(\App\Http\Middleware\SecurityHeaders::class);`, placed
      between `ForceJsonResponse` and `HandleCors`.
- [x] 8c. Add a Feature test asserting the 3 headers are present on at least one representative API
      response.
      Added `apps/backend/tests/Feature/SecurityHeadersTest.php` — asserts all 3 headers on an
      authenticated `dashboard/stats` response and on the unauthenticated `auth/login` response
      (proves the middleware runs regardless of auth state). 2 tests, 12 assertions, both pass.
- [x] 8d. Add a `headers()` function to `apps/frontend/next.config.ts` setting the same 3 headers for
      Next.js-served responses. **Note added at VALIDATE (22-06-26): confirm via `npm run build &&
      npm run start` rather than `npm run dev` alone — Next.js 16's `headers()` config behavior in
      dev vs production build is a known framework nuance per `apps/frontend/AGENTS.md`'s caution
      about Next.js 16/React 19 divergence from training data.**
      Confirmed at EXECUTE (22-06-26): ran `npm run build && npm run start -- -p 3099` and curled
      `http://localhost:3099/login` — all 3 headers present in the production response
      (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy:
      strict-origin-when-cross-origin`). Confirms the headers actually apply in production build,
      not just dev mode.
- [x] 8e. Write `process/features/go-live/backlog/csp-header_NOTE_22-06-26.md` documenting that CSP
      is deferred — rationale: needs dedicated testing against rendered pages, risk of breaking
      Next.js inline hydration if rushed.
      Written at EXECUTE (22-06-26).

### 9 — CORS configuration (Medium) — EXECUTED 22-06-26

- [x] 9a. Publish `apps/backend/config/cors.php` (Laravel 13's CORS config is normally published via
      `php artisan config:publish cors`, or create the file manually matching Laravel's default CORS
      config shape if the publish command is unavailable in this version).
      `php artisan config:publish cors` worked directly — no manual fallback needed.
- [x] 9b. Set `allowed_origins` to the actual frontend dev origin (confirm exact value — `npm run dev`
      uses Next.js's default port 3000, so `http://localhost:3000` unless a `.env`/script override is
      found at EXECUTE time). **Confirmed at VALIDATE (22-06-26): no `.env`/script override found —
      `npm run dev` runs plain `next dev`, so port 3000 is correct.**
      Set `'allowed_origins' => ['http://localhost:3000']` (was `['*']` from the published default).
- [x] 9c. Set `supports_credentials` to `false`, matching `withCredentials: false` already set in
      `apps/frontend/src/lib/api/client.ts` and `createAuthenticatedClient`.
      Already `false` in the published default config — confirmed, no change needed.
- [x] 9d. Confirm the existing `HandleCors::class` middleware registration in `bootstrap/app.php`
      (already present) picks up the new published config with no further wiring needed.
      Confirmed via `php artisan tinker --execute="echo config('cors.allowed_origins')[0];"` — outputs
      `http://localhost:3000`; also confirmed via the new `CorsConfigurationTest` Feature test
      (item 9e) that the live HTTP response actually reflects the configured origin.
- [x] 9e. **Added at VALIDATE (22-06-26) — see Validate Contract Suggested Plan Additions:** Add a
      Feature test asserting the `Access-Control-Allow-Origin` response header is present and matches
      the configured frontend origin on at least one representative endpoint, so this section has a
      Fully-Automated gate instead of resting entirely on manual curl/browser confirmation.
      Added `apps/backend/tests/Feature/CorsConfigurationTest.php` — 3 tests: (1) configured origin
      `http://localhost:3000` is echoed back on `dashboard/stats`, (2) the echoed origin is never the
      literal wildcard `*`, (3) an unconfigured origin (`http://evil.example.com`) is not reflected
      back. 3 tests, 4 assertions, all pass.

### Info only — no action

- Item #10 (no dedicated Form Request classes): confirmed during RESEARCH that inline validation via
  `$request->validate()` is correct and complete across controllers. No checklist item required.

---

## Exit Gate

```bash
cd apps/backend && php artisan test
# Expected: 0 failures, including all new Policy/rate-limit/header/module-filter Feature tests

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: 0 failures; lint/build must not regress beyond the pre-existing baseline
# (7 pre-existing lint errors / 13 pre-existing warnings recorded in umbrella Current Execution State)

grep -rn "wms_access_token" apps/frontend/src
# Expected: zero matches (token storage fully removed)

cd apps/backend && php artisan route:list --path=api/auth/login
# Expected: throttle:login middleware visible on the route
```

- Zero open Critical/High findings in the audit report
- All Medium findings either fixed (this phase) or explicitly logged to backlog with rationale
  (CSP, store/index/show CRUD access control, module-scope read gating — see Validate Contract Open
  Gap 1)
- Phase 1 + Phase 2 regression suites still green

---

## Blockers That Would Justify BLOCKED Status

- A finding requires a breaking API contract change (e.g. changing the login response shape) that
  the user has not approved — document and route to backlog, do not silently change a public contract

---

## Phase Loop Progress

- [x] 1. RESEARCH — research-agent: prior phase reports read; test context loaded; plan drift checked
- [x] 2. INNOVATE — innovate-agent: approach decided; Decision Summary written
- [x] 3. PLAN-SUPPLEMENT — plan-agent: existing phase plan updated (or "n/a — research clean")
- [x] 4. PVL — vc-validate-agent: full V1-V7; validate-contract written (CONDITIONAL — see Validate
      Contract; Open Gap 1 on Section 7 should be resolved via a follow-up PLAN-SUPPLEMENT before
      EXECUTE starts Section 7)
- [x] 5. EXECUTE — all checklist items done; per-section test gates run and green (Section 7 deferred
      to backlog per Open Gap 1, not part of this phase's executed scope)
- [x] 6. EVL — all EVL gates green; follow-up stubs registered; EVL HANDOFF SUMMARY written
- [x] 7. UPDATE PROCESS — phase report written, umbrella state updated, commit done

## EVL HANDOFF SUMMARY (22-06-26)

Full regression sweep after Sections 1, 3, 4, 5, 6, 8, 9 complete (Section 2 token-storage removal
also complete; Section 7 deferred to backlog):

- `cd apps/backend && composer test` → 181 tests, 474 assertions, **0 failures**
- `cd apps/frontend && npm run test` → 14 test files, 19 tests, **0 failures**
- `cd apps/frontend && npm run build` → succeeds, all routes compile, headers confirmed present via curl on production build
- `cd apps/frontend && npm run lint` → 7 pre-existing errors + 13 pre-existing warnings, identical to baseline (verified via git stash) — zero new issues from this phase
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0 warnings/failures
- `php artisan route:list --path=api/auth/login -v` → confirms `throttle:login` middleware attached

Regression check against Phase 1 (162→181 backend net +19 from new Policy/rate-limit/CORS/headers
tests) and Phase 2 (165→181, +16 net from this phase) both VERIFIED phases: all prior tests still
pass, none deleted, only extended where Policy gates changed expected actor roles.

Follow-up stubs registered (backlog):
- `process/features/go-live/backlog/module-scoped-data-filtering_NOTE_21-06-26.md` — Section 7,
  needs new data-model decision (module column doesn't exist on data tables)
- `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` — `store`/`index`/`show`
  on Inbound/Outbound/Shipment still ungated, needs business-rule decision
- `process/features/go-live/backlog/csp-header_NOTE_22-06-26.md` — CSP deferred, needs dedicated
  testing pass against rendered pages

Critical findings #1 (CRUD authz) and #2 (token storage) — the two most severe items — are both
fully resolved within this phase's scope (update/destroy gated; token never persisted to browser
storage). Residual risk on #1 (store/read still open) and the Section 7 gap are explicitly
documented, not hidden.

**Validate-contract required before execute.**

---

## Touchpoints

- `apps/backend/app/Policies/*` (3 new: Inbound/Outbound/Shipment; 2 extended: ApkUser/Moving)
- `apps/backend/app/Http/Controllers/Api/{Inbound,Outbound,Shipment,Apk,Moving,Report,Monitoring,Dashboard,Master}Controller.php`
- `apps/backend/app/Providers/AppServiceProvider.php`
- `apps/backend/app/Traits/FiltersByUserModule.php` (new)
- `apps/backend/app/Http/Middleware/SecurityHeaders.php` (new)
- `apps/backend/config/sanctum.php`
- `apps/backend/config/cors.php` (new)
- `apps/backend/bootstrap/app.php`
- `apps/backend/routes/api.php`
- `apps/frontend/src/components/auth/token-sync.tsx` (DELETE)
- `apps/frontend/src/app/(dashboard)/layout.tsx`
- `apps/frontend/src/lib/api/client.ts`
- `apps/frontend/src/lib/api/{apk,master,dashboard,monitoring,shipment,moving,user,inbound,outbound}.service.ts`
- `apps/frontend/next.config.ts`

---

## Public Contracts

- No breaking changes to existing success-path response shapes without explicit user approval.
- The 429 rate-limit response (new) must match the app's standard `{data, message, status}` shape,
  not Laravel's raw default — this is a new contract being introduced, not a change to an existing
  one, but it must be consistent with the existing shape.

---

## Verification Evidence

```bash
cd apps/backend && php artisan test
# Expected: green, including new Policy/rate-limit/header/module-filter regression tests

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: green; lint/build baseline not regressed

grep -rn "wms_access_token" apps/frontend/src
# Expected: zero matches
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
- Last completed step: UPDATE PROCESS (Phase Loop Progress step 7) — phase VERIFIED
- Validate-contract status: written, CONDITIONAL (22-06-26), accepted as-is
- Phase report: `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md`
- Next step: Phase 4 — Deployment Readiness. See umbrella plan Current Execution State.

---

## Inner Loop Refresh Note (22-06-26)

RESEARCH + INNOVATE materially expanded this phase's scope beyond the original generic "Step A-C"
plan. Two findings in particular were not anticipated by the original plan text:

1. **CRUD authorization gap is broader than the Phase 2 backlog note anticipated.** Phase 2's
   closeout note (`process/features/go-live/backlog/policy-formalization-remaining-controllers_NOTE_21-06-26.md`)
   flagged that Inbound/Outbound/Shipment/master-data controllers have zero authorization checks,
   but this phase's audit confirmed it as a Critical finding requiring immediate Policy creation for
   `update`/`destroy` on all 3 transactional controllers (Inbound/Outbound/Shipment) — not deferred
   further. `store`/`index`/`show` access control is explicitly still deferred (new backlog note),
   but `update`/`destroy` are not.
2. **Token storage in browser `sessionStorage`/`localStorage` — a wholly new Critical finding, not
   previously documented anywhere in this program.** The audit discovered `token-sync.tsx` bridges
   the NextAuth JWT into browser storage so the legacy `apiClient` singleton can read it, and 9 of 10
   `*.service.ts` files depend on that singleton. This is the largest blast radius in the phase and
   requires a coordinated removal across `client.ts`, `token-sync.tsx`, and every service file plus
   their callers — broken into per-file sub-steps (Checklist Section 2) given the size.

This Inner Loop Refresh Note supersedes the original 3-step (A/B/C) generic checklist structure with
9 concrete numbered sections (1-9) plus 1 info-only item, mapped from the approved decisions.

---

## Validate Contract

Status: CONDITIONAL
Date: 22-06-26
date: 2026-06-22
generated-by: inner-pvl: phase-03

Parallel strategy: sequential (deep-mode single-pass investigation)
Rationale: Signal score 6/7 (HIGH) per vc-agent-strategy-compare, but the dimensions are tightly
coupled — the token-storage finding and the authz finding both require reading the same backend
controllers, the same `client.ts`/service files, and the same schema dump. A Layer-1/Layer-2
subagent fan-out would have re-read the same ~25 files independently with no benefit; one
deep-mode sequential pass (read every touched file, grep the schema, run both test suites as
baseline) covered all 4 Layer-1 dimensions and all 9 Layer-2 sections with no duplicated reads.
Recommended strategy for EXECUTE (next phase): **parallel subagents**, ~8 independent sub-passes —
see Phase-END strategy note at the end of this contract.

### Pre-PVL regression baseline (ran before any analysis, per user instruction)

```bash
cd apps/backend && composer test
# Result: 165 tests / 431 assertions / 0 failures — matches umbrella's recorded Phase 1+2 baseline exactly

cd apps/frontend && npm run test
# Result: 14 test files / 19 tests / 0 failures — matches umbrella's recorded Phase 1+2 baseline exactly
```

Baseline is clean. Phase 1+2 regression surfaces are healthy entering Phase 3.

### Test gates (5-column table)

| criterion id | behavior | strategy | proving test | gap-resolution |
|---|---|---|---|---|
| 1i | Inbound/Outbound/Shipment update/destroy: 403 for non-admin, success for admin | Fully-Automated | `cd apps/backend && php artisan test --filter=InboundControllerTest\|OutboundControllerTest\|ShipmentControllerTest` | A |
| 3d | Login throttle: 6th attempt in 1 min (same email+IP) returns 429 in `{data,message,status}` shape | Fully-Automated | `cd apps/backend && php artisan test --filter=AuthControllerTest` (new test method) | A |
| 4c | ApkController store/update/destroy: 403 for non-admin/non-type-1-3, success for allowed roles | Fully-Automated | `cd apps/backend && php artisan test --filter=ApkControllerTest` | A |
| 6c | MovingController store: 403 for non-admin, success for admin | Fully-Automated | `cd apps/backend && php artisan test --filter=MovingControllerTest` | A |
| 7f | Module-scoped read endpoints: user in module A cannot see module B's records | Known-Gap (see Open Gaps — schema does not carry a module column on any target table) | — | C |
| 8c | Security headers present on representative API response | Fully-Automated | `cd apps/backend && php artisan test --filter=SecurityHeadersTest` (new test file) | A |
| 9d/9e | CORS config picked up, `Access-Control-Allow-Origin` present and correct | Fully-Automated (after 9e added) | `cd apps/backend && php artisan test --filter=CorsConfigurationTest` (new — added via item 9e) | B |
| 2n | Zero remaining `wms_access_token` references in frontend source | Fully-Automated | `grep -rn "wms_access_token" apps/frontend/src` — expect zero matches | A |
| 2o | At least one converted service's token-passing call path covered | Fully-Automated | `cd apps/frontend && npm run test` (new/updated test for one service, per plan item 2o) | A |
| (build) | Frontend typecheck/build catches any service call-site signature mismatch after token-param conversion | Fully-Automated | `cd apps/frontend && npm run build` | A |

gap-resolution legend: A — proven now; B — fixed in this plan (gate added by this plan's checklist
via new item 9e); C — deferred to a named later phase/plan; D — backlog test-building stub.

C-4 note: `strategy:` column carries only Fully-Automated / Hybrid / Agent-Probe. The one Known-Gap
row (7f) is a named residual, not a proving strategy — see Open Gaps below for the full rationale
and the required plan correction.

### Legacy line form (for existing contract consumers)

- CRUD authz (Inbound/Outbound/Shipment update+destroy): Fully-automated: `php artisan test --filter=InboundControllerTest|OutboundControllerTest|ShipmentControllerTest`
- Login rate limit: Fully-automated: `php artisan test --filter=AuthControllerTest`
- ApkUserPolicy extension: Fully-automated: `php artisan test --filter=ApkControllerTest`
- MovingPolicy extension: Fully-automated: `php artisan test --filter=MovingControllerTest`
- Module-scope read filtering (Section 7): known-gap: documented — see Open Gaps; plan checklist 7a-7f needs correction before this can be a proving gate
- Security headers: Fully-automated: `php artisan test --filter=SecurityHeadersTest` (new) + `npm run build` (frontend headers() config; verify via `npm run build && npm run start`, not `npm run dev` alone, per Next.js 16 caution)
- CORS: Fully-automated after item 9e is added: new `CorsConfigurationTest` Feature test; until then this is hybrid/manual only
- Token storage removal: Fully-automated: `grep -rn "wms_access_token" apps/frontend/src` (zero matches) + `npm run build` (catches signature mismatches) + `npm run test`
- Token expiration (sanctum.php): Fully-automated: existing `php artisan test` suite must not show any test relying on infinite-lifetime tokens (item 5b is itself a manual code-search step, not a new automated test — Agent-Probe: execute-agent greps for token-lifetime assumptions in fixtures/factories)

### Dimension findings

- Infra fit: CONCERN — `config/cors.php` and `app/Exceptions/Handler.php` are both correctly identified by the plan as not-yet-existing ("new" / "confirm at EXECUTE"); confirmed via direct file check (both genuinely absent). No port/service mismatches found. `AppServiceProvider::boot()` is confirmed as the only provider (no `RouteServiceProvider`/`AuthServiceProvider`) and already holds exactly the 3 Phase 2 `Gate::policy()` registrations the plan expects to extend — item 1d's instruction to add 3 more alongside them is mechanically correct.
- Test coverage: CONCERN — every controller this plan touches already has a Feature test file to extend (`InboundControllerTest`, `OutboundControllerTest`, `ShipmentControllerTest`, `ApkControllerTest`, `MovingControllerTest`, `ReportControllerTest`, `MonitoringControllerTest`, `DashboardControllerTest`, `MasterControllerTest` all exist) and `MovingControllerTest`'s 403/200 assertion pattern (lines 110-150) is real and copyable, confirming items 1i/4c/6c are mechanically feasible as written. Gap (resolved via checklist addition 9e): CORS had no automated test planned — now added. Remaining gap: Section 7 (module-scope filtering) has no fully-automated path available as currently scoped — see Open Gaps.
- Breaking changes: PASS — no existing success-path response shape changes identified. The new 429 rate-limit shape is additive, not a change to an existing contract, and the plan correctly requires it match `{data,message,status}`. `apiClient`'s public export surface changes (loses `setAuthToken`/`clearAuthToken`) but these are internal helpers with no consumers outside `token-sync.tsx` (confirmed via grep) and `token-sync.tsx` is being deleted in the same change — no external break.
- Security surface: CONCERN — see STRIDE-lite findings below. Two Critical items (CRUD authz gap, token storage) are correctly scoped and mechanically accurate per direct source verification. One Medium item (Section 7, module-scope read gating) rests on a join target that does not exist in the schema — see Open Gaps.

#### Security surface — confirmed findings (STRIDE-lite read of the touched surface)

| Finding | Confirmed against source | Severity | Plan coverage |
|---|---|---|---|
| `InboundController::update()`/`destroy()`, `OutboundController::update()`/`destroy()`, `ShipmentController::update()`/`destroy()` have zero authorization calls today | Read all 3 controllers directly — confirmed, no `$this->authorize()` anywhere in any of the 6 methods | Critical | Covered — items 1a-1j |
| `ApkController::store()`/`update()`/`destroy()` have zero authorization calls (only `resetPassword`/`logout` are gated, from Phase 2) | Read `ApkController.php` directly — confirmed; also confirmed it uses raw `DB::table()` everywhere, never an Eloquent `ApkUser` instance, so item 4b's "class-string form" instruction is the only viable form | Critical | Covered — items 4a-4c |
| `MovingController::store()` has zero authorization call (only `destroy()` is gated, from Phase 2) | Read `MovingController.php` directly — confirmed, `store()` has no `$this->authorize()` call | High | Covered — items 6a-6c |
| Frontend stores the Sanctum Bearer token in `sessionStorage`/`localStorage` via `token-sync.tsx`, readable by any JS on the page (XSS exfil surface), and used by 9 of 9 `*.service.ts` files via the `apiClient` singleton | Read `client.ts` and `token-sync.tsx` directly — confirmed exact mechanism: request interceptor reads `sessionStorage`/`localStorage`, response interceptor clears on 401; confirmed all 9 service files import `apiClient`, zero callers currently use `createAuthenticatedClient` | Critical | Covered — items 2a-2o, with added VALIDATE notes on novelty/risk (no existing call pattern to copy) |
| `Sanctum` token has no expiration (`'expiration' => null` in `config/sanctum.php`) | Read `config/sanctum.php` directly — confirmed `null` | High | Covered — item 5a |
| Login route has no rate limiting | Read `routes/api.php` directly — confirmed no `throttle` middleware on the login route; confirmed no `RateLimiter::for` definitions exist anywhere in `app/` | High | Covered — items 3a-3d |
| No security response headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) on API or frontend responses | Read `bootstrap/app.php` middleware chain directly — confirmed only `ForceJsonResponse` + `HandleCors` are registered, no headers middleware | Medium | Covered — items 8a-8e |
| No `cors.php` config published — CORS currently relies on Laravel 13 framework defaults via `HandleCors::class` | Read `bootstrap/app.php` — confirmed `HandleCors::class` is registered but `config/cors.php` does not exist on disk | Medium | Covered — items 9a-9e (9e added at VALIDATE for an automated gate) |
| Module-scope read filtering (Section 7) — checklist assumes target models carry a `module` (or equivalent) attribute to filter against | Grepped `tables_schema.sql` for every column named `module` across the whole schema dump — the ONLY table with a `module` column is `el_user` (the requesting user's own attribute). `el_inbound_header`, `el_outbound_header`, `el_moving`, `el_loc`, `el_log` (ActivityLog), and the master-data tables (`el_product_category`, `el_recipient`) all carry NO module/tenant column to filter against. (Caveat: `tables_schema.sql` is independently known-stale per the repo's documented limitation — but the live `User.php` model and `AuthController.php` confirm `module` is exclusively a per-user attribute, never echoed onto any of these 7 target tables in any model file either.) | Medium → escalating to a plan-correctness CONCERN | NOT mechanically feasible as written — see Open Gaps |

### Open gaps

1. **Section 7 (module-scope read gating) rests on a join target that does not exist.** Item 7a
   instructs creating `FiltersByUserModule.php` as a trait that filters target-model queries by
   `module`, deferring "exact column/relationship... at EXECUTE time." Direct verification (schema
   grep + all 4 target-area model reads: `Inbound`, `Outbound`, `Moving`, `Location`/
   `ProductCategory`/`Recipient`, `ActivityLog`) found that **none of these models or their backing
   tables carry a `module` column or any module-tenant relationship** — only `el_user` does. There
   is currently no per-record module attribution anywhere in the warehouse data, so "filter
   Report/Monitoring/Dashboard/Master query results by the requesting user's module" cannot be
   implemented as a `where('module', $user->module)`-style trait against these tables — the
   business data itself does not carry that dimension yet. This is a plan-correctness gap, not an
   EXECUTE-time naming detail: EXECUTE cannot "confirm the join/column path per controller" because
   there is no path to confirm. Recommended resolution: convert Section 7 to a backlog
   `NEW PLAN REQUIRED` note (module-scoped data filtering needs a schema/data-model decision —
   likely a new `module` column on `el_inbound_header`/`el_outbound_header`/`el_moving` plus a
   migration and backfill strategy — which is Phase-2-shaped work, not Phase-3-shaped), and descope
   items 7a-7f from this phase's Critical/High/Medium triage to zero-open-Medium via documented
   deferral, consistent with how items 1j and 8e already defer comparable gaps. known-gap:
   documented as NEW PLAN REQUIRED — pending a new backlog note (not yet written; this is a
   PLAN-SUPPLEMENT action, not something vc-validate-agent writes unilaterally).
2. **CORS (Section 9) had no automated test gate originally planned — resolved in this VALIDATE pass
   by adding item 9e** (new Feature test asserting `Access-Control-Allow-Origin`). This is a small,
   in-scope checklist addition, already applied above — no further action needed before EXECUTE.
3. **Token storage removal (Section 2) blast radius and novelty is larger than the plan's framing
   suggests — addressed via VALIDATE notes added inline to items 2d/2m above.** Verified that
   `createAuthenticatedClient(token)` exists in `client.ts` but is used ZERO times anywhere else in
   the codebase today — every one of the 9 service files exclusively uses the `apiClient` singleton,
   and there is no existing call-site precedent to copy the token-passing pattern from. Caller
   counts confirmed via grep: apk=2, master=6, dashboard=2, monitoring=1, shipment=4, moving=2,
   user=2, inbound=5, outbound=5 (29 caller files total across `apps/frontend/src/app/(dashboard)/**`
   and `apps/frontend/src/hooks/**`). This is not a blocking gap — the plan already breaks this into
   9 named sub-passes (2d-2l) plus a dedicated caller-update pass (2m), which is the right shape —
   the VALIDATE notes flag that the first converted service file's call-site pattern should be
   treated as the template for the remaining 8, with server-component (`auth()`) vs client-component
   (`useSession()`) token retrieval verified separately.
4. **Frontend test risk for item 2o is lower than initially flagged, confirmed empirically —
   resolved, no checklist change needed.** Checked: existing `*.page.test.tsx` files mock at the
   service-function boundary (`vi.mock("@/lib/api/inbound.service", ...)`), not at
   `apiClient`/`client.ts` directly, and no test file imports an unmocked service module. This means
   the token-passing mechanism change is unlikely to break existing component test *mocks*, but it
   WILL surface as a TypeScript build error if a service function's exported signature changes
   incompatibly with how a test's mock stub is typed — `npm run build` is the correct gate for that
   risk, not `npm run test` alone. Both gates are already in the plan's Exit Gate section.

What this coverage does NOT prove:
- The Fully-Automated authz tests (items 1i/4c/6c) prove 403/200 status codes for `update`/`destroy`
  on the 3 transactional controllers and the 3 extended Policy methods — they do NOT prove anything
  about `store`/`index`/`show` authorization on those same controllers (explicitly deferred per item
  1j's own backlog note) or about any master-data CRUD controller's authorization (not in this
  phase's scope at all).
- The login-throttle test (item 3d) proves the 6th attempt in one rolling minute window for one
  email+IP combination returns 429 in the correct shape — it does NOT prove behavior across the
  minute boundary reset, does NOT prove behavior under a distributed/multi-instance deployment where
  the rate limiter's cache store might not be shared (this app currently has no documented cache
  driver decision for production), and does NOT prove protection against credential stuffing using
  many different emails from the same IP (the limiter key is `email+ip`, an attacker rotating emails
  defeats it by design per the approved decision — documented in this contract, not a defect).
- `grep -rn "wms_access_token" apps/frontend/src` proves the literal storage key string is gone from
  source — it does NOT prove that no other mechanism reintroduces token-in-browser-storage (e.g. a
  future service file copy-pasting old code), and does NOT prove the running browser's
  `sessionStorage`/`localStorage` is actually empty post-deploy (no runtime/E2E browser check is
  planned for this — Agent-Probe candidate, not currently in the checklist; logged here as a known
  residual, not blocking).
- `npm run build` proves TypeScript type-checks pass across the whole frontend after the token-param
  signature change — it does NOT prove every converted service call site passes the *correct* token
  at runtime (e.g. an expired or stale session token reaching a service call) — that is a runtime
  behavior with no automated gate in this phase; Agent-Probe (manual login + exercise each of the 9
  converted modules in the browser) is the realistic gate and should be named explicitly in
  execute-agent's verification pass.
- The new CORS test (item 9e) proves the `Access-Control-Allow-Origin` header is present and correct
  on one representative endpoint — it does NOT prove every endpoint returns the header identically,
  and does NOT prove browser preflight (`OPTIONS`) behavior, which PHPUnit's HTTP test client does
  not exercise the same way a real browser does.
- The security-headers items (8a-8d) prove the 3 headers are present on one representative backend
  API response and that `next.config.ts` declares the same 3 headers for Next.js-served responses —
  they do NOT prove the headers are present on every route (only "at least one representative"
  response is tested per item 8c), and do NOT prove Next.js's `headers()` config actually applies in
  production builds vs dev mode without the `npm run build && npm run start` check called out above.

Accepted by: pending user review — no standing /goal is active in this session; the orchestrator
will confirm CONDITIONAL acceptance (or request a further plan-validate-fix loop) with the user
before this phase proceeds to EXECUTE. Section 7 (Open Gap 1) is the one item that should not be
executed until its PLAN-SUPPLEMENT correction is made; Sections 1-6, 8, 9 are otherwise clear to
proceed pending that user confirmation.

Gate: CONDITIONAL (1 plan-correctness gap on Section 7 that should be resolved via PLAN-SUPPLEMENT
before EXECUTE touches that section; CORS gap already resolved in this pass via item 9e; remaining
2 CONCERNs are informational notes added inline to the checklist, not blockers)

### Suggested plan additions (for the next PLAN-SUPPLEMENT step — Section 7 only; 9e already applied above)

- Section 7: replace items 7a-7f with a backlog `NEW PLAN REQUIRED` note (module-scoped data
  filtering needs a data-model decision: which tables need a `module` column, migration + backfill
  plan) and remove Section 7 from this phase's Medium-severity open-finding count, consistent with
  how items 1j/8e already defer comparable scope. This is the one outstanding action before EXECUTE
  can safely touch Section 7 — Sections 1-6, 8, 9 do not depend on this resolution and may proceed.

---

**Phase-END strategy note (for EXECUTE):** Score 6/7 (HIGH) signals workflow/team-tier
fan-out, but `vc-agent-strategy-compare` strategy-by-fit assessment for EXECUTE specifically:
**parallel subagents**, ~8 (one execute-fix agent per numbered checklist section, since Sections
1/2/3/4/5/6/8/9 touch disjoint file sets except for the shared `AppServiceProvider.php` (Sections
1+4+6, sequenced not parallel for that one file) and shared `routes/api.php` (Section 3 only).
Recommend: Section 1, then Sections 2/3/4/5/6/8/9 in parallel (disjoint files), with Section 7
withheld pending the PLAN-SUPPLEMENT resolution above. Agent count: ~8 parallel execute-agents +
1 EVL confirmation pass. Model: opus (execute-agent is real code-execution work) for all 8; sonnet
for the EVL vc-tester confirmation pass per Model Selection Policy.
