---
name: note:policy-formalization-remaining-controllers
description: "Security gap — most controllers still have zero authorization checks; deferred to Phase 3"
date: 21-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: Policy Formalization for Remaining Controllers

**Found during:** go-live Phase 2 (Data Model & Auth Hardening), Step A3/B1
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`,
`process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_REPORT_19-06-26.md`

**Target phase:** Phase 3 — Security & Validation Audit
(`process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`)

---

## Problem

Phase 2 scoped its Policy hardening to only 3 highest-risk controller clusters
(`ApkUserPolicy`, `UserPolicy`, `MovingPolicy` — see `process/context/auth/all-auth.md`). Every
other controller still has **zero authorization checks**: any authenticated user (regardless of
`type`/`admin`/`module`) can currently create, update, or delete this data.

Confirmed during Phase 2 RESEARCH: only `UserController` had any check at all prior to Phase 2 (an
ad hoc `if (!$request->user()->admin)`), and `type`/`module` fields are never checked anywhere in
the codebase outside the 3 new Policies.

## Controllers still without any authorization check

- `InboundController` — all CRUD methods (create/update/delete inbound records)
- `OutboundController` — all CRUD methods
- `ShipmentController` — all methods except `pushInbound`/`details` already touched by Phase 2's
  bug fixes (those fixes did not add authorization, only fixed the 500 errors)
- Master-data CRUD controllers: locations (`LocationController` or equivalent), product categories
  (`ProductCategoryController`), recipients (`RecipientController`)
- Any other controller under `apps/backend/app/Http/Controllers/Api/` not named
  `ApkController`/`UserController`/`MovingController` (those 3 are now Policy-gated as of Phase 2)

## Recommendation

Phase 3's OWASP-style audit should treat this as a pre-confirmed finding to triage immediately,
not something to rediscover from scratch — the gap is already documented and verified by direct
code reading in Phase 2. Suggested approach: extend the same Policy pattern from Phase 2
(`ApkUserPolicy`/`UserPolicy`/`MovingPolicy` in `apps/backend/app/Policies/`, registered via
`Gate::policy(...)` in `AppServiceProvider::boot()`) to the remaining controllers, deciding the
authorization rule per resource (e.g. should any authenticated user be able to mutate inbound
records, or only `type`/`module`-matched users?) before writing each Policy class.

## Related

- `process/context/auth/all-auth.md` — current Policy/Gate implementation state, including this
  gap, documented as MVP scope only
- `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_REPORT_19-06-26.md` — full Phase 2 findings and rationale for the MVP scope decision
