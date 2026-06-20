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
**Phase status:** ⏳ PLANNED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

WMS Lite has never had a security review. Before any deployment work (Phase 4), the app needs an
OWASP-style audit of its auth, input validation, and API surface — especially because Phase 2 just
finished formalizing the role/permission model, which is exactly the kind of change that should be
re-checked by a security pass rather than trusted blindly.

---

## Entry Gate

- Phase 1 + Phase 2 exit gates passed
- Phase 2 report read for the confirmed role/permission model and any Policy classes added

---

## Blast Radius

- `apps/backend/app/Http/Middleware/*` (rate limiting, if added)
- `apps/backend/config/sanctum.php` (token expiry policy, if changed)
- `apps/backend/app/Http/Requests/*` (new Form Request validation classes, if input validation gaps found)
- `apps/backend/routes/api.php` (rate-limit middleware applied to `auth/login`, if not already present)
- Security audit report (new, durable)

---

## Implementation Checklist

### Step A — Run the audit

- [ ] A1. Invoke the `vc-security` skill (STRIDE + OWASP-based audit) scoped to `apps/backend` and
      `apps/frontend`, focused on: authZ per endpoint (every protected route actually checks the
      right role, not just "is logged in"), input validation completeness (every controller using
      `$request->validate()` or Form Requests, no raw `$request->input()` reaching Eloquent/SQL),
      Sanctum token lifecycle (expiry, rotation on login per `AuthController::login` already
      revoking old tokens — confirm this is the desired policy), and login rate limiting (currently
      absent — confirm with `routes/api.php`).
- [ ] A2. Triage findings into Critical / High / Medium / Low.

### Step B — Fix Critical/High findings

- [ ] B1. Fix every Critical and High finding in-phase.
- [ ] B2. For Medium/Low findings: fix if trivial and in-blast-radius, otherwise log to
      `process/features/go-live/backlog/` as a NOTE file.

### Step C — Add the obvious missing gates if confirmed needed

- [ ] C1. Add login rate limiting (Laravel's built-in `throttle` middleware) to
      `POST /api/auth/login` if A1 confirms it is missing.
- [ ] C2. Confirm/adjust Sanctum token expiry policy (`config/sanctum.php` `expiration`) — currently
      tokens appear to have no expiry; decide with the user whether that is acceptable for internal
      staff use or needs a TTL.

---

## Exit Gate

```bash
cd apps/backend && php artisan test
# Expected: 0 failures, including any new security-fix regression tests

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: 0 failures
```

- Zero open Critical/High findings in the audit report
- All Medium/Low findings either fixed or explicitly logged to backlog with rationale
- Phase 1 + Phase 2 regression suites still green

---

## Blockers That Would Justify BLOCKED Status

- A finding requires a breaking API contract change (e.g. changing the login response shape) that
  the user has not approved — document and route to backlog, do not silently change a public contract

---

## Phase Loop Progress

- [ ] 1. RESEARCH — research-agent: prior phase reports read; test context loaded; plan drift checked
- [ ] 2. INNOVATE — innovate-agent: approach decided; Decision Summary written
- [ ] 3. PLAN-SUPPLEMENT — plan-agent: existing phase plan updated (or "n/a — research clean")
- [ ] 4. PVL — vc-validate-agent: full V1-V7; validate-contract written
- [ ] 5. EXECUTE — all checklist items done; per-section test gates run and green
- [ ] 6. EVL — all EVL gates green; follow-up stubs registered; EVL HANDOFF SUMMARY written
- [ ] 7. UPDATE PROCESS — phase report written, umbrella state updated, commit done

**Validate-contract required before execute.**

---

## Touchpoints

- `apps/backend/app/Http/Middleware/*`
- `apps/backend/config/sanctum.php`
- `apps/backend/app/Http/Requests/*`
- `apps/backend/routes/api.php`

---

## Public Contracts

- No breaking changes to existing success-path response shapes without explicit user approval.

---

## Verification Evidence

```bash
cd apps/backend && php artisan test
# Expected: green, including any new rate-limit / validation regression tests
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
- Last completed step: not started (blocked behind Phase 1 + Phase 2)
- Validate-contract status: pending
- Next step: After Phase 2 UPDATE-PROCESS, spawn vc-research-agent for Phase 3 Step A (invoke vc-security skill).

---

## Validate Contract

(placeholder — vc-validate-agent writes this section before EXECUTE)
