---
name: report:go-live-phase-02-data-model-auth-hardening
description: "WMS Lite go-live — Phase 2 closeout report: Data Model & Auth Hardening"
date: 21-06-26
metadata:
  node_type: memory
  type: report
  feature: go-live
  phase: phase-02
---

# Phase 02 Report — Data Model & Auth Hardening

**Program:** go-live
**Phase plan:** `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
**Status:** ✅ VERIFIED
**Date:** 21-06-26

---

## Summary

Phase 2 resolved all four `all-context.md` Open Question items carried over from the original
harness STUDY pass, then implemented four concrete changes that fell out of those findings:

- **Step A — Resolve open questions.** All 4 unresolved items investigated against live Yii
  source (`Driver.php`, `ShipmentController.php`, `SyncCommand.php`) and confirmed with the user.
- **Step B1 — MVP Policy hardening.** Created 3 Laravel Policy classes (`ApkUserPolicy`,
  `UserPolicy`, `MovingPolicy`) covering the 3 highest-risk controller clusters; registered in
  `AppServiceProvider::boot()`.
- **Step B3 — 2 product bug fixes.** `ShipmentController::pushInbound()` (duplicate-row /
  always-500 bug) and `InboundController::details()` (orderBy on a nonexistent column /
  always-500 bug) — both carried over from the Phase 1 backlog.
- **Step B4 — UI label fix.** `master/apk/page.tsx` mislabeling ("Main"/"Staging" with an
  incorrect "— Testing" suffix) corrected to "OTR Module"/"Service Module", plus a new permanent
  warning on Service Module account creation/edit.
- **Step C — Context doc updates.** `all-context.md`, `database/all-database.md`,
  `auth/all-auth.md` all updated with confirmed facts in place of "needs verification" language.

## Significant Findings

Two of the four original Open Question assumptions turned out to be **wrong**, not merely
unconfirmed:

1. **Shadow `_s` tables are NOT a staging-vs-confirmed-record pattern.** Confirmed from Yii
   `Driver.php` (`actionaddIn` / `actionaddIns`): main tables hold bulk/qty records; `_s`
   counterparts plus `el_inbound_lots` hold per-lot/serial-number detail records. Both sides hold
   live, final data — there is no staging/confirmed distinction at all.
2. **Shipment status flow assumption was wrong; the code was right.** The actual flow is
   `Air/Ocean Intransit -> Custom Process -> Warehouse in Transit -> successful` (sticky at
   `successful`, driven by an optional field plus `SyncCommand::sync()`'s Schenker integration) —
   not the previously-documented `new -> inprogress -> ...` flow. This was a documentation defect,
   not a code defect; no code change was made for the flow itself.

A third finding was a **contradiction that resolved to intentional design, not a bug**:
`el_apk`/`el_apk_s` appeared at first glance to be an instance of the bulk/lot shadow-table
pattern (same naming convention as the other `_s` pairs), but research confirmed it is a
deliberately unrelated split — `el_apk` (OTR module) accounts can log in to the scanner app,
`el_apk_s` (Service module) accounts cannot, the two have separate dashboards, and there is no
sync between them. Clarifying this distinction directly motivated the Step B4 UI fix, since the
frontend's prior "Main"/"Staging" labels incorrectly implied the same bulk/lot relationship as the
other shadow tables.

The fourth finding (role/permission enforcement) was confirmed thinner than assumed: only
`UserController` had any authorization check at all (an ad hoc `if (!$user->admin)`); every other
controller had zero checks, and `type`/`module` fields were never checked anywhere in the
codebase. This shaped the MVP-scoped Policy hardening decision (Step B1) and the Phase 3 backlog
item created below.

## Test Results

- Backend: `composer test` → **165 tests, 431 assertions, 0 failures** (baseline was 162/414 —
  net +3 tests/+17 assertions from B1e/B3a/B3b additions)
- Frontend: `npm run test` → **14 files, 19 tests, 0 failures** (baseline was 14/18 — net +1 test
  from B4c)
- Frontend: `npm run build` → succeeds, all 13 routes compile
- Frontend: `npm run lint` → 7 pre-existing errors + 13 pre-existing warnings, identical
  before/after this phase (confirmed via git-stash diff) — no new lint issues introduced
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0
  warnings/failures

No Phase 1 test was deleted; tests that asserted the *old buggy* behavior (pushInbound 409/500,
details() 500) were rewritten to assert the fixed behavior, per validate-contract instruction E5.

## Deviations From Plan

- **`Controller.php` required the `AuthorizesRequests` trait — an infra gap, not scope creep.**
  The plan's Blast Radius listed the 3 new Policy classes and their registration point
  (`AppServiceProvider::boot()`, corrected at PVL from the originally-assumed
  `AuthServiceProvider`, which does not exist in this Laravel 13 app), but did not separately call
  out that the base `Controller.php` was missing Laravel's standard `AuthorizesRequests` trait.
  `$this->authorize(...)` threw `Call to undefined method ...::authorize()` for all 3 Policies
  until the trait was added. This is required scaffolding for the plan's own explicit
  instructions (E1/E2) to function at all — not an expansion of scope, and is now reflected in the
  phase plan's Touchpoints/Blast Radius.
- All other implementation matched the plan and its validate-contract Execute-Agent Instructions
  (E1-E7) exactly, including the `ApkUserPolicy` authorization rule (E3: admin-or-type-1/3,
  matching the frontend's existing `canEdit` gate rather than tightening to admin-only) and the
  corrected file targets for the shipment push-to-inbound UI copy (E6: `shipment/page.tsx`, not
  `shipment-form-sheet.tsx`).

## Recommendation for Phase 3

Phase 3 (Security & Validation Audit) should treat **Policy formalization for the remaining
controllers as a real, documented security gap**, not a nice-to-have. As of this phase:
`InboundController`, `OutboundController`, `ShipmentController`, and all master-data CRUD
controllers (locations, categories, recipients) still have **zero authorization checks** — any
authenticated user can currently create/update/delete this data regardless of
`type`/`admin`/`module`. This should be one of the first items triaged in Phase 3's OWASP-style
review, since it is a known, already-confirmed finding rather than something the audit needs to
discover from scratch. See the new backlog NOTE below for the full controller list and research
references.

---

## Status

DONE
