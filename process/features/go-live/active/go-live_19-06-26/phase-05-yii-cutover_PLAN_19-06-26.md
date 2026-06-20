---
name: plan:go-live-phase-05-yii-cutover
description: "WMS Lite go-live — Phase 5: Legacy Yii Cutover Plan"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-05
---

# Phase 05 — Legacy Yii Cutover Plan

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ⏳ PLANNED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

The legacy Yii 1.x app (`protected/`, `yiiframework/`, root-level `*.php` entrypoints) still runs in
parallel against the same MySQL database. WMS Lite is not truly "siap pakai" (production-ready)
until there is a concrete, safe, module-by-module plan to retire it — not just a working Laravel/
Next.js replacement sitting alongside it forever. This phase produces that plan and proves it works
on at least one module.

---

## Entry Gate

- Phase 1 + Phase 2 + Phase 3 + Phase 4 exit gates passed (tests green, data model/auth confirmed,
  security audited, deployment pipeline exists)

---

## Blast Radius

- `API_INVENTORY.md` (update with cutover status per endpoint)
- Legacy Yii route files at repo root (e.g. `inbound.php`, `outboundx.php`, `index.php`) and
  `protected/controllers/*` — modified or removed only for the module(s) actually cut over in this
  phase, one at a time
- New cutover runbook doc (path TBD in research, likely `process/features/go-live/active/go-live_19-06-26/`)

---

## Implementation Checklist

### Step A — Build the per-module cutover plan

- [ ] A1. Using `API_INVENTORY.md` (the existing ~134-endpoint Yii→Laravel mapping), classify each
      of the 9 modules by cutover readiness: which modules have 100% endpoint parity already
      confirmed (via Phase 1 tests + manual browser check), and which still have gaps.
- [ ] A2. Order the 9 modules by cutover risk (lowest risk / most isolated first). Document the
      order and rationale with the user.
- [ ] A3. Define the parallel-run validation approach: how to confirm the Laravel/Next.js version
      produces identical results to the Yii version for a module before flipping traffic
      (e.g. shadow-read comparison, manual side-by-side check, or a scripted diff).
- [ ] A4. Define the rollback approach per module: how to point back at the Yii page(s) for that
      module if the cutover causes a problem.

### Step B — Execute the first module cutover

- [ ] B1. Pick the lowest-risk module from A2 and execute its cutover: redirect/disable the legacy
      Yii entrypoint(s) for that module, confirm the Laravel/Next.js path is the only one serving
      real traffic for it.
- [ ] B2. Run the Step A3 parallel-run validation for that module before and after the cutover.
- [ ] B3. Update `API_INVENTORY.md` to mark that module's endpoints as "cut over" with the date.

### Step C — Document the remaining modules' plan

- [ ] C1. For the 8 remaining modules, write a one-paragraph cutover plan each (order, validation
      approach, owner = the user, target timeframe if known) — these become backlog items, not
      executed in this phase.

---

## Exit Gate

```bash
cd apps/backend && php artisan test
cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: still green after the cutover module's legacy endpoint changes
```

- Written per-module cutover plan exists for all 9 modules (1 executed, 8 documented for later)
- At least one module's Yii endpoints are confirmed retired and the Laravel/Next.js replacement is
  verified live (manual browser check + API check, same method used in the original full-feature
  verification session)
- Rollback procedure for the cut-over module is documented and was at least dry-run once

---

## Blockers That Would Justify BLOCKED Status

- Parallel-run validation reveals a real behavior mismatch between Yii and Laravel for the chosen
  module — do not cut over; fix the mismatch (likely routes back to Phase 2 scope) and retry
- The user wants to choose a different first module than the lowest-risk one identified in A2 —
  honor that, but document the risk tradeoff explicitly in the phase report

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

- `API_INVENTORY.md`
- Legacy Yii files for the one cut-over module only (exact files named once A2 picks the module)
- New cutover runbook doc

---

## Public Contracts

- The cut-over module's external behavior (URLs warehouse staff actually use, or the redirect
  target) must not silently change without the user knowing — document the user-facing change.

---

## Verification Evidence

```bash
# Manual verification of the cut-over module: same method as the original full-feature browser
# check (login -> navigate to module -> confirm data renders, no console errors)
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md`
- Last completed step: not started (blocked behind Phases 1-4)
- Validate-contract status: pending
- Next step: After Phase 4 UPDATE-PROCESS, spawn vc-research-agent for Phase 5 Step A.

---

## Validate Contract

(placeholder — vc-validate-agent writes this section before EXECUTE)
