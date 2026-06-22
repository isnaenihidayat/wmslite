---
name: report:go-live-phase-03-security-audit
description: "WMS Lite go-live — Phase 3 closeout report: Security & Validation Audit"
date: 22-06-26
metadata:
  node_type: memory
  type: report
  feature: go-live
  phase: phase-03
---

# Phase 03 — Security & Validation Audit — Closeout Report

**Program:** go-live
**Phase plan:** `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
**Status:** ✅ VERIFIED (CONDITIONAL gate, accepted scope)
**Date:** 22-06-26

---

## Summary

Phase 3 ran an OWASP-style security audit (`vc-security`) of WMS Lite's auth, input validation, and
API surface — the first formal security review the app has had. RESEARCH + INNOVATE produced 9
actionable findings (2 Critical, 3 High, 3 Medium, 1 deferred-as-not-feasible) plus 1 info-only item.
All decisions were user-approved before PVL.

PVL returned **CONDITIONAL**: 0 FAILs, 1 plan-correctness gap (Section 7 — module-scoped read
filtering rests on a schema join target, `module`, that does not exist on any data table). The gate
correctly withheld Section 7 from EXECUTE rather than letting it run against a non-existent design,
and added one new automated test gate (Section 9 CORS, item 9e) during the PVL pass itself.

EXECUTE implemented Sections 1-6, 8, 9 (8 of 9 numbered sections); Section 7 was correctly skipped
per the CONDITIONAL gate's own instruction and converted to a backlog note instead.

### Findings addressed

1. **CRUD authorization gap (Critical)** — `InboundPolicy`, `OutboundPolicy`, `ShipmentPolicy`
   created (admin-only, matching the `MovingPolicy`/`UserPolicy` pattern). `update`/`destroy` gated
   on `InboundController`, `OutboundController`, `ShipmentController`. `store`/`index`/`show` on
   these 3 controllers remain ungated — deferred, see Backlog.
   - **Structural note:** no `Shipment` Eloquent model exists in this codebase —
     `ShipmentController` operates on `Inbound::class` (`from_shipment=1` scope). `ShipmentPolicy`
     could not be registered via `Gate::policy()` (would silently shadow the `InboundPolicy`
     binding on the same model class). It is invoked directly via a private `authorizeShipment()`
     helper instead, throwing `AuthorizationException` manually. Functionally equivalent 403
     response shape; documented in the Policy's class doc comment.
2. **Token storage in browser `sessionStorage`/`localStorage` (Critical)** — `token-sync.tsx`
   deleted entirely. All 9 `*.service.ts` files converted from the `apiClient` singleton to
   `createAuthenticatedClient(token)`, with `token` as the last parameter. All 29 caller files
   (all client components, zero server-component callers) updated to pass `session?.user?.accessToken`
   via `useSession()`. `grep -rn "wms_access_token" apps/frontend/src` confirms zero matches.
3. **Login rate limiting (High)** — named limiter (`email+ip`, 5/min) registered in
   `AppServiceProvider::boot()`, applied via `throttle:login` on `POST /api/auth/login`. Default
   Laravel 429 shape (`{"message": "..."}`) kept as-is — confirmed it already matches every other
   error response in this app (no controller anywhere actually emits the aspirational
   `{data, message, status}` envelope).
4. **ApkUserPolicy extension (High)** — `store`/`update`/`delete` methods added, same rule as
   existing `resetPassword`/`logout` (`$user->admin || in_array($user->type, [1, 3])`).
5. **Token expiration (High)** — `config/sanctum.php` `expiration` changed from `null` to `480`
   (minutes), matching the frontend's 8-hour NextAuth session `maxAge`.
6. **MovingPolicy extension (High)** — `store` method added, admin-only, gating
   `MovingController::store()`.
7. **Module-scoped read gating (Medium) — DEFERRED, not executed.** Confirmed via direct schema
   grep that no data table (`el_inbound_header`, `el_outbound_header`, `el_moving`, `el_loc`,
   master-data tables) carries a `module` column — only `el_user` does. This is a data-model
   decision, not a security fix; see backlog note.
8. **Security headers (Medium)** — `SecurityHeaders` middleware (`X-Frame-Options: DENY`,
   `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`)
   registered backend-side; matching `headers()` config added to `next.config.ts`. Verified present
   on a production build (`npm run build && npm run start`), not just dev mode.
9. **CORS configuration (Medium)** — `config/cors.php` published, `allowed_origins` restricted to
   `http://localhost:3000`, `supports_credentials: false` (already matched frontend). New
   `CorsConfigurationTest` Feature test added during PVL (item 9e) to make this fully-automated.
10. **Info only** — confirmed inline `$request->validate()` validation is correct and complete
    across controllers; no dedicated Form Request classes needed.

---

## Test Results

- Backend: `composer test` → **181 tests, 474 assertions, 0 failures** (baseline was 165/431 — net
  +16 tests from Sections 1, 3, 4, 6, 8, 9)
- Frontend: `npm run test` → **14 test files, 19 tests, 0 failures** (unchanged count, but 4 page
  tests gained a `next-auth/react` session mock as part of the token-storage fix — see Deviations)
- Frontend: `npm run build` → succeeds, all 17 routes compile; security headers confirmed present
  via curl against the production build
- Frontend: `npm run lint` → 7 pre-existing errors + 13 pre-existing warnings, identical to baseline
  (verified via git-stash diff) — zero new issues from this phase
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0 warnings/failures
- `php artisan route:list --path=api/auth/login -v` → confirms `throttle:login` middleware attached

Regression check against Phase 1 (162→181, net +19 including Phase 2's own +3) and Phase 2
(165→181, net +16 from this phase) both VERIFIED phases: all prior tests still pass, none deleted,
only extended where Policy gates changed the expected actor role (e.g. `update`/`destroy` tests
moved from `actingUser()` to `actingAdmin()`).

---

## Deviations From Plan

1. **`ShipmentPolicy` registration workaround** (documented above under finding #1) — required
   because no dedicated `Shipment` Eloquent model exists; `Gate::policy()` is keyed by model class
   and `Inbound::class` was already bound to `InboundPolicy`. Resolved with a direct-invocation
   helper rather than the Gate facade. Functionally equivalent, not a scope change.
2. **4 frontend page tests required a new `next-auth/react` mock.** `recipient`, `locations`,
   `product-category`, and `monitoring` page tests had never called `useSession()` before (their
   queries fired unconditionally via the old `apiClient` singleton). Adding `useSession()` +
   `enabled: !!token` broke these 4 tests with "useSession must be wrapped in a `<SessionProvider />`".
   Fixed by adding the same `vi.mock("next-auth/react", ...)` pattern already used elsewhere,
   including a mocked `accessToken` — this both fixes the regression and proves token-passing
   coverage for those 4 pages. Anticipated in spirit by the VALIDATE contract's item 2o note (which
   correctly flagged `npm run build` as the relevant gate) but the actual failure surfaced at test
   runtime rather than build time.
3. **Section 7 not executed** — per the CONDITIONAL gate's own instruction (Open Gap 1), converted
   to a backlog note instead of attempting a non-feasible design.

No deviation required returning to PLAN. All items above were resolved within this phase's existing
blast radius.

---

## Backlog Items Found (3 total, all new this phase)

1. `process/features/go-live/backlog/module-scoped-data-filtering_NOTE_21-06-26.md` — Section 7's
   underlying gap: no data table carries a per-record module attribution. Needs a Phase-2-shaped
   data-model decision (new column + migration + backfill), not a security fix.
2. `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` — `store`/`index`/`show`
   on Inbound/Outbound/Shipment (and all master-data CRUD) remain ungated. No confirmed business
   rule yet for who may create/read records per module.
3. `process/features/go-live/backlog/csp-header_NOTE_22-06-26.md` — Content-Security-Policy
   explicitly deferred; needs dedicated browser-based testing (report-only mode first) given the
   risk of breaking Next.js inline hydration if rushed.

All 3 are genuine product/business decisions or dedicated-testing-effort items, not implementation
shortcuts.

---

## Recommendation for Phase 4

Phase 4 (Deployment Readiness) can proceed — Phases 1, 2, and 3 exit gates are all met (zero open
Critical/High findings; the one open Medium item, Section 7, is explicitly deferred with rationale,
matching the umbrella's exit-gate language "Medium findings either fixed or explicitly logged to
backlog with rationale").

One item worth Phase 4's attention: the login rate limiter's key (`email+ip`) assumes a single-cache
backend. This app currently has no documented cache driver decision for production — confirm the
cache store is shared/consistent across however many app instances Phase 4's deployment target runs,
or the rate limiter's effectiveness will silently degrade under horizontal scaling.

---

## Validate-Contract Outcome

Net Gate: CONDITIONAL at PVL (0 FAILs, 1 plan-correctness gap on Section 7, fully resolved by
descoping that section to backlog rather than executing a non-feasible design). All other 8 sections'
execute-agent guidance was followed without further deviation beyond the 2 items noted above.

`generated-by: inner-pvl: phase-03` — date: 2026-06-22.

---

## Regression Note

Checked against both previously VERIFIED phases (Phase 1, Phase 2) — see Test Results above. No
regressions found; only intentional test updates where Policy gates changed the expected actor role.

---

## Status

✅ VERIFIED — backend 181/474 (0 failures), frontend 19 tests (0 failures), both context validators
clean, 8 of 9 checklist sections executed (Section 7 correctly deferred per CONDITIONAL gate), phase
report written.
