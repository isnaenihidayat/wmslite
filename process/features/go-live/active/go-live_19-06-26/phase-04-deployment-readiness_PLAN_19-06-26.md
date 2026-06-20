---
name: plan:go-live-phase-04-deployment-readiness
description: "WMS Lite go-live — Phase 4: Deployment Readiness"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-04
---

# Phase 04 — Deployment Readiness

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ⏳ PLANNED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

WMS Lite has run local-only since inception — no staging, no production, no CI/CD, no Docker. This
phase stands up the minimum needed to actually run the app somewhere real: an environment, a CI
pipeline that runs Phase 1's test suite on every push, and — critically, because the MySQL database
is shared live with the still-running legacy Yii app — a tested backup/rollback plan before anyone
deploys anything that touches that database.

---

## Entry Gate

- Phase 1 + Phase 2 + Phase 3 exit gates passed

---

## Blast Radius

- New CI config (exact platform TBD in research — e.g. `.github/workflows/*` if GitHub Actions)
- `apps/backend/.env.example` (production-relevant variable documentation, no real values)
- `apps/frontend/.env.local.example` (production-relevant variable documentation, no real values)
- New deployment/runbook documentation (where it lives is a research decision — likely
  `process/features/go-live/active/go-live_19-06-26/` as a `_REF_` file, or a new `infra` context
  group if it grows past a single doc)

---

## Implementation Checklist

### Step A — Choose and document the target environment

- [ ] A1. With the user, decide the staging/production hosting target (this is a solo-dev, local-DB,
      shared-with-legacy-Yii setup — the decision must account for the Yii app's own hosting, since
      they need to reach the same MySQL instance).
- [ ] A2. Document the chosen environment's deploy process for both `apps/frontend` (Next.js) and
      `apps/backend` (Laravel).

### Step B — CI pipeline

- [ ] B1. Add a CI workflow that runs `php artisan test` (backend) and `npm run test` + `npm run
      lint` + `npm run build` (frontend) on every push/PR.
- [ ] B2. Confirm the CI test run does NOT touch the real shared `wmslite` database (uses the
      Phase 1 test-DB strategy).

### Step C — Backup/rollback plan

- [ ] C1. Write and test a MySQL backup procedure for the shared `wmslite` database (this protects
      both the new stack and the legacy Yii app).
- [ ] C2. Write a rollback procedure for a bad deploy of either app that does NOT require touching
      the database (since the DB is shared and live).
- [ ] C3. Actually run the backup procedure once against a real (non-production) copy to prove it
      works — do not just document it unverified.

---

## Exit Gate

```bash
# CI pipeline run (exact command depends on Step A/B platform choice — document the real command here once decided)

cd apps/backend && php artisan test
cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: all green, matching what CI runs
```

- CI pipeline green on at least one real push/PR
- Backup procedure documented AND executed once successfully with recorded evidence
- Rollback procedure documented (execution proof optional unless a real rollback is exercised)

---

## Blockers That Would Justify BLOCKED Status

- No hosting budget/target is available yet — document the constraint and keep this phase open
  rather than forcing a choice the user hasn't approved
- Backup procedure cannot be safely tested without touching the live shared database — find a safe
  copy-based approach before marking this phase done; do not skip the verification step

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

- New CI config files (path TBD)
- `apps/backend/.env.example`, `apps/frontend/.env.local.example`
- New backup/rollback runbook doc

---

## Public Contracts

- No application behavior changes — this phase is infrastructure/process only.

---

## Verification Evidence

```bash
# CI run link/log (recorded in phase report once Step A/B decided)
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
- Last completed step: not started (blocked behind Phase 1 + Phase 2 + Phase 3)
- Validate-contract status: pending
- Next step: After Phase 3 UPDATE-PROCESS, spawn vc-research-agent for Phase 4 Step A (hosting decision needs user input).

---

## Validate Contract

(placeholder — vc-validate-agent writes this section before EXECUTE)
