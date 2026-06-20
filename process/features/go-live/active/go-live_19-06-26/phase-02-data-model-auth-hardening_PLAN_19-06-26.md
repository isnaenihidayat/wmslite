---
name: plan:go-live-phase-02-data-model-auth-hardening
description: "WMS Lite go-live — Phase 2: Data Model & Auth Hardening"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-02
---

# Phase 02 — Data Model & Auth Hardening

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ⏳ PLANNED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

`process/context/all-context.md` and `process/context/database/all-database.md` already log a set
of unresolved "Open Questions" from the initial harness STUDY pass: the meaning of legacy shadow
`_s` tables, the exact shipment status flow, and whether `type`/`admin`/`module` role checks are
formalized as Laravel Policy/Gate classes or still ad hoc per-controller. This phase closes those
questions with the user, updates the code and context docs to match, and fixes any product bugs
Phase 1's new test suite surfaced. Nothing in Phase 3 (security audit) can be trusted until the
data model and authZ model are actually understood and documented correctly.

---

## Entry Gate

- Phase 1 exit gate passed: `php artisan test` and `npm run test` both green, covering all 9 modules
- Phase 1 report read for any product bugs logged as "Phase 2 input" rather than fixed inline

---

## Blast Radius

- `apps/backend/app/Policies/*` (new — one per model needing role-based authorization)
- `apps/backend/app/Providers/AuthServiceProvider.php` (Policy registration)
- `apps/backend/app/Http/Controllers/Api/*Controller.php` (replace ad hoc `if ($user->admin)`-style
  checks with `$this->authorize(...)` calls, where such ad hoc checks are found)
- `apps/backend/app/Models/Inbound.php` (shipment/`from_shipment` status-flow logic, if confirmed
  to need changes)
- `process/context/all-context.md` (remove resolved items from "Open Questions")
- `process/context/database/all-database.md` (replace "needs verification" language with confirmed
  facts about `_s` shadow tables)
- `process/context/auth/all-auth.md` (replace "needs verification" language about Policy/Gate with
  confirmed implementation)

---

## Implementation Checklist

### Step A — Resolve open questions with the user

- [ ] A1. Present findings on the `_s` shadow tables (inspect `app/Models/*`, any code that reads
      `el_inbound_header_s`/`el_outbound_header_s`/`el_moving_s`/`el_loc_s`/`el_apk_s`, and the
      `flag`/`scan_time` columns on detail tables) and ask the user to confirm the actual semantics
      (staging/scan-in-progress vs. confirmed record, or something else).
- [ ] A2. Trace `ShipmentController` + `Inbound` model to confirm the exact status flow and
      `from_shipment` semantics; confirm with the user whether the previously-assumed flow
      (`new -> inprogress -> Custom Process -> Warehouse in Transit -> successful`) is accurate.
- [ ] A3. Decide with the user whether `type`/`admin`/`module` checks should become formal Laravel
      Policy classes now, or whether that is deferred — if deferred, document why and update the
      charter's scope tier mapping accordingly.

### Step B — Implement confirmed decisions

- [ ] B1. If Policies are in scope: create one Policy per model that needs role gating; register in
      `AuthServiceProvider`; replace ad hoc checks in controllers with `$this->authorize(...)`.
- [ ] B2. Apply any shipment status-flow corrections confirmed in A2.
- [ ] B3. Fix any product bugs carried over from the Phase 1 report.

### Step C — Update context docs

- [ ] C1. Update `process/context/all-context.md` "Open Questions" — remove resolved items, keep
      genuinely still-open ones.
- [ ] C2. Update `process/context/database/all-database.md` and `process/context/auth/all-auth.md`
      with the confirmed facts.

---

## Exit Gate

```bash
cd apps/backend && php artisan test
# Expected: 0 failures, including any new Policy-related tests

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: 0 failures, 0 lint errors, build succeeds

node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs
# Expected: 0 warnings, 0 failures
```

- All checklist items checked
- Every all-context.md Open Question item either resolved (code + docs updated) or explicitly
  deferred with a documented reason
- Phase 1 regression suite still green

---

## Blockers That Would Justify BLOCKED Status

- The user is not available to confirm A1/A2/A3 and the agent cannot safely guess (these touch the
  shared production database's data integrity — guessing wrong here risks Phase 4/5 data loss)
- A confirmed fix requires a schema change on the shared `wmslite` database without a tested backup
  path yet (defer that specific fix to align with Phase 4, document in backlog)

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

- `apps/backend/app/Policies/*` (new)
- `apps/backend/app/Providers/AuthServiceProvider.php`
- `apps/backend/app/Http/Controllers/Api/*Controller.php`
- `apps/backend/app/Models/Inbound.php`
- `process/context/all-context.md`, `process/context/database/all-database.md`, `process/context/auth/all-auth.md`

---

## Public Contracts

- API response shapes must not change as a side effect of adding Policy-based authorization — only
  the authorization mechanism changes, not the success-path response.

---

## Verification Evidence

```bash
cd apps/backend && php artisan test
# Expected: green, including Policy-gated endpoint tests
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
- Last completed step: not started (blocked behind Phase 1)
- Validate-contract status: pending
- Next step: After Phase 1 UPDATE-PROCESS, spawn vc-research-agent for Phase 2 Step A.

---

## Validate Contract

(placeholder — vc-validate-agent writes this section before EXECUTE)
