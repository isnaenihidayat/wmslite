---
name: plan:go-live-umbrella
description: "WMS Lite go-live readiness — umbrella/orchestration plan for the 5-phase program"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: umbrella
---

# WMS Lite Go-Live Readiness — Umbrella Plan

**Date:** 19-06-26
**Complexity:** COMPLEX
**Status:** ⏳ PLANNED

- Program type: PHASE PROGRAM (5 phases, sequential with gated joins)
- Date: 19-06-26
- Feature folder: `process/features/go-live/`

---

## Program Goal Charter

```
WMS Lite Go-Live Readiness — Program Goal Charter

North star:
- Take WMS Lite from "all 9 modules functionally verified working locally, zero test coverage"
  to a state where it can be safely run in production by warehouse staff, with the legacy Yii
  app retired module by module.

Definition of done (an unattended agent must be able to do all of these):
1. Run `php artisan test` in apps/backend and `npm run test` in apps/frontend and get green
   coverage of every existing API endpoint and critical CRUD UI flow across all 9 modules.
2. Point to a written, user-confirmed answer for every item in the all-context.md "Open
   Questions" section (shadow `_s` tables, shipment status flow, Policy/Gate formalization,
   NextAuth env var names), with the code updated to match.
3. Point to a staging or production deployment of both apps with CI running the Phase 1 test
   suite on every push, and a written per-module Yii retirement plan with at least one module
   already cut over.

What "verified" means (program level):
- Each phase is VERIFIED only when its own exit gate passes AND a regression check against
  every previously verified phase's surface still passes.
- validate-contract gates must be recorded alongside phase gates and regression evidence for a
  phase to reach VERIFIED. A phase without a validate-contract (or documented skip reason)
  cannot be marked VERIFIED.

Scope tiers → phase mapping:
- Tier 1 Safety net (tests) → Phase 1
- Tier 2 Correctness (data model, auth, security) → Phases 2, 3
- Tier 3 Production exposure (deploy, legacy retirement) → Phases 4, 5
- This program retires Tiers 1-3.

Explicitly out of scope (deferred tier):
- Building the actual APK Scanner mobile/scanner application (only its account-management
  backend/frontend exists today) — tracked in `process/features/go-live/backlog/`.
- Any new product module beyond the current 9 (inbound, outbound, shipment, moving,
  monitoring, master data, admin, reports, dashboard).

Hard safety constraints (non-negotiable, per phase):
- Never run a destructive migration or schema change directly against the shared production
  MySQL database (`wmslite`) without an explicit, user-approved backup step first (Phase 4/5
  territory) — the Yii legacy app reads/writes the same tables live.
- Never delete or truncate any `el_*` table or its `_s` shadow counterpart without explicit
  user confirmation of what that table is for (see Phase 2 — this is currently unconfirmed).
- Never commit real user credentials, `.env` contents, or Sanctum tokens to git.
- Never push to `origin/main` without the user's explicit go-ahead for that specific push
  (existing repo convention — solo dev reviews before every push).
- Commit each phase's execution changes before starting the next phase. Keep process/plan/context
  commits separate from execution commits.
```

---

## Stable Program Goal (copy-paste this to start autonomous execution)

```
SESSION GOAL: go-live — WMS Lite Go-Live Readiness
Ref: process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md

TARGET: Complete ALL 5 phases until:
- Phase 1: php artisan test (backend) and npm run test (frontend) both exit 0 with coverage of all 9 modules' endpoints/critical flows.
- Phase 2: every all-context.md Open Question has a written, user-confirmed answer + matching code change.
- Phase 3: OWASP-style audit findings triaged to zero open High/Critical items.
- Phase 4: CI pipeline green on a staging deploy; backup/rollback plan documented and tested once.
- Phase 5: written per-module Yii cutover plan with at least one module cut over and verified.
- Test tiers: automated (iterate-until-green) / hybrid (fix-if-in-blast-radius) / agent-probe (record-judgment)

AUTONOMY: Before ANY subagent spawn, read:
1. Umbrella ## Current Execution State -> loop step + validate-contract status
2. Phase plan ## Phase Loop Progress -> first unchecked box = next subagent to spawn

PER-PHASE LOOP (7-step inner loop R -> I -> P -> PVL -> E -> EVL -> UP, never skip, never reorder; SKIPS SPEC):
  1. RESEARCH -> 2. INNOVATE -> 3. PLAN-SUPPLEMENT -> 4. PVL -> 5. EXECUTE -> 6. EVL -> 7. UPDATE-PROCESS
- PLAN-SUPPLEMENT: plan-agent writes research/innovate gaps into phase plan (or marks "n/a - clean")
- PVL NEVER skipped; contract must follow example-validate-output.md full format
- Every subagent FIRST ACTION: vc-context-discovery + vc-plan-discovery
- Every phase-END: invoke vc-agent-strategy-compare for next step strategy recommendation

Report via phase reports. No approval between phases unless a hard stop is hit.

HARD STOPS (pause, wait for user):
- Any destructive op on the shared production MySQL DB (wmslite) without an approved backup step first
- Deleting/truncating any el_* or el_*_s table without confirmed table semantics
- Any git push to origin/main
- Net gate = BLOCKED with no backlog resolution path
- Validate-contract is placeholder and vc-validate-agent cannot run

SAFETY (never override):
- Shared DB is live and also used by the running legacy Yii app — assume every other phase's writes are concurrent
- Never commit .env contents or tokens
- Commit each phase before advancing; process and execution commits separate

TEST GATES (every phase exit):
  cd apps/backend && php artisan test
  cd apps/frontend && npm run test
  cd apps/frontend && npm run lint && npm run build
  node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
  node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs

VALIDATE CONTRACT: Per-phase contracts written by vc-validate-agent into each phase plan before EXECUTE.

START: Phase 1, loop step RESEARCH (pending). Spawn vc-research-agent for Phase 1.
```

---

## Phase Sequence

| Phase | Plan file | Scope summary | Depends on |
|---|---|---|---|
| 0 (pre-program) | this file | Confirm folder structure, baseline audit, create sub-phase plans | — |
| 1 — Test Infrastructure Foundation | `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_PLAN_19-06-26.md` | Real PHPUnit Feature tests for all 9 backend modules; stand up frontend test runner (Vitest) with smoke coverage of critical CRUD flows | Phase 0 |
| 2 — Data Model & Auth Hardening | `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md` | Resolve all-context.md Open Questions (shadow `_s` tables, shipment status flow, role checks -> Policy/Gate), fix bugs Phase 1 surfaced | Phase 1 |
| 3 — Security & Validation Audit | `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md` | OWASP-style review: authZ per endpoint, input validation, Sanctum token policy, login rate limiting | Phase 1 + Phase 2 |
| 4 — Deployment Readiness | `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md` | Staging/production environment, CI running Phase 1 tests on push, production `.env`, DB backup/rollback plan | Phase 1 + Phase 2 + Phase 3 |
| 5 — Legacy Yii Cutover Plan | `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md` | Per-module Yii retirement plan using `API_INVENTORY.md`, parallel-run validation, rollback plan | Phase 1 + Phase 2 + Phase 3 + Phase 4 |

### Join Conditions

- Phase 1 MUST NOT start until Phase 0 exit gate passes (this umbrella + all 5 phase plans exist).
- Phase 2 MUST NOT start until Phase 1 exit gate passes.
- Phase 3 MUST NOT start until Phase 1 AND Phase 2 exit gates both pass.
- Phase 4 MUST NOT start until Phases 1, 2, AND 3 exit gates all pass.
- Phase 5 MUST NOT start until Phases 1, 2, 3, AND 4 exit gates all pass.

---

## Per-Phase Entry / Exit Gates

| Phase | Entry | Exit gate |
|---|---|---|
| 0 | Program start | Phase plan files created; baseline validators recorded |
| 1 | Phase 0 complete | `php artisan test` green for all 9 modules' endpoints; frontend test runner exists with smoke coverage; both commands exit 0 |
| 2 | Phase 1 exit met | Every all-context.md Open Question has a written, user-confirmed answer; matching code/migration changes merged; Phase 1 regression suite still green |
| 3 | Phases 1+2 exit met | Zero open High/Critical findings in security audit report; any fixes applied and regression-tested |
| 4 | Phases 1+2+3 exit met | CI pipeline green on at least one staging deploy; documented backup/rollback procedure tested once |
| 5 | Phases 1+2+3+4 exit met | Written per-module cutover plan; at least one Yii module retired and verified live on the new stack |

---

## Per-Phase Loop

Each phase executes the canonical 7-step inner loop `R → I → P → PVL → E → EVL → UP`. This inner
loop SKIPS SPEC — SPEC runs once in the outer program loop, not per phase. The 7 steps map to:

1. **RESEARCH** — spawn research-agent: load context, read prior phase reports, check plan drift, document findings
2. **INNOVATE** — spawn innovate-agent: decide approach; write Decision Summary (chosen approach + rejected alternatives)
3. **PLAN-SUPPLEMENT** — spawn plan-agent: if research/innovate found gaps/pre-conditions not in checklist, add them; otherwise mark "n/a — research clean" and tick step 3
4. **PVL** — spawn vc-validate-agent: full V1-V7; validate-contract written per `.claude/skills/vc-validate-findings/references/example-validate-output.md` format
5. **EXECUTE** — spawn vc-execute-agent per approved plan and validate-contract
6. **EVL** — spawn vc-tester: run phase test gates to green; register follow-up stubs; write EVL HANDOFF SUMMARY
7. **UPDATE-PROCESS** — write phase report to durable report path, rewrite umbrella `## Current Execution State` section (overwrite, not append)

**PVL is NEVER skipped.** A placeholder `## Validate Contract` = blocked. Do not spawn execute-agent while the Validate Contract section reads "(placeholder — vc-validate-agent writes this section before EXECUTE)".

---

## Autonomous Execution Rules (During /goal)

During /goal execution of a phase program:
- Agent self-decides at all V5 gates — no user approval needed between phases, EXCEPT the hard stops listed in the Program Goal Charter (destructive DB ops, table deletion, git push to main).
- CONDITIONAL net gate: proceed autonomously, fixes applied in-flight, gaps on record.
- BLOCKED net gate: document items in backlog, continue with remaining phase plans; backlog is always a valid resolution.
- Agent writes phase reports, updates phase plans, creates new sub-plans as needed — all autonomously.
- The phase report is the communication channel for conflicts, errors, and learnings — not inline questions.

---

## Global Constraints

- Never widen scope to include APK Scanner app development or new modules — those are out of scope per the charter.
- Treat the shared MySQL `wmslite` database as live production data shared with the running Yii app at every phase; never assume exclusive access.
- After every phase that touches `process/context/` files, run `validate-all-context.mjs` and `validate-context-discovery.mjs` and confirm both exit 0 before declaring the phase done.
- Commit each phase's execution changes before starting the next phase. Keep process/plan/context commits separate from execution commits.

---

## Durable Report Destinations

| Phase | Report path (inside task folder) |
|---|---|
| 0 (pre-program) | `process/features/go-live/active/go-live_19-06-26/phase-00-planning_REPORT_19-06-26.md` |
| 1 — Test Infrastructure Foundation | `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_REPORT_19-06-26.md` |
| 2 — Data Model & Auth Hardening | `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_REPORT_19-06-26.md` |
| 3 — Security & Validation Audit | `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md` |
| 4 — Deployment Readiness | `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_REPORT_19-06-26.md` |
| 5 — Legacy Yii Cutover Plan | `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_REPORT_19-06-26.md` |

---

## Program Status Table

| Phase | Status |
|---|---|
| 0 — Pre-program (plan creation) | ✅ COMPLETE |
| 01 — Test Infrastructure Foundation | ✅ VERIFIED |
| 02 — Data Model & Auth Hardening | ✅ VERIFIED |
| 03 — Security & Validation Audit | ✅ VERIFIED |
| 04 — Deployment Readiness | ⏳ PLANNED |
| 05 — Legacy Yii Cutover Plan | ⏳ PLANNED |

Status values: ⏳ PLANNED | 🔨 CODE DONE | 🧪 TESTING | ✅ VERIFIED | 🚧 BLOCKED | ✅ COMPLETE

---

## Touchpoints

- `apps/backend/tests/Feature/*` and `apps/backend/tests/Unit/*` (Phase 1)
- `apps/frontend/vitest.config.ts`, `apps/frontend/src/**/*.test.ts(x)` (Phase 1, new)
- `apps/backend/app/Policies/*`, `apps/backend/app/Models/*`, `apps/backend/app/Http/Controllers/Api/*` (Phase 2)
- `process/context/all-context.md`, `process/context/database/all-database.md`, `process/context/auth/all-auth.md` (Phase 2)
- `apps/backend/app/Http/Middleware/*`, `apps/backend/config/sanctum.php` (Phase 3)
- CI config (new, e.g. `.github/workflows/*`), `apps/backend/.env` / `apps/frontend/.env.local` production variants (Phase 4)
- `API_INVENTORY.md`, legacy Yii route files at repo root and `protected/` (Phase 5)

---

## Public Contracts

- Existing API response shapes (`{data, message, status}` for Laravel target endpoints) must not change shape as part of this program — only coverage and correctness improve.
- Existing frontend routes/URLs must not change as part of this program.
- Legacy Yii endpoints retired in Phase 5 must have a confirmed Laravel parity endpoint per `API_INVENTORY.md` before retirement.

---

## Blast Radius

Files directly modified or created (program-wide; each phase plan lists its own exact subset):

- New backend test files under `apps/backend/tests/Feature/` and `apps/backend/tests/Unit/`
- New frontend test config + test files under `apps/frontend/`
- New/modified Laravel Policy classes under `apps/backend/app/Policies/`
- `process/context/all-context.md`, `process/context/database/all-database.md`, `process/context/auth/all-auth.md` (Open Questions resolved)
- New CI config files (exact path TBD in Phase 4 research)
- Legacy Yii files at repo root and `protected/` (Phase 5 retirement only, module by module)

---

## Verification Evidence

```bash
# Backend test suite (Phase 1+ exit gate)
cd apps/backend && php artisan test
# Expected: all suites pass, 0 failures

# Frontend test suite (Phase 1+ exit gate, once created)
cd apps/frontend && npm run test
# Expected: all suites pass, 0 failures

# Frontend build sanity (every phase touching frontend)
cd apps/frontend && npm run lint && npm run build
# Expected: 0 lint errors, build succeeds

# Context validators (every phase touching process/context/)
node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs
# Expected: 0 warnings, 0 failures
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md`
- Last completed phase: Phase 0 (this umbrella plan file = Phase 0 artifact)
- Validate-contract status: pending (vc-validate-agent writes per-phase)
- Next step for a fresh agent: Read this umbrella plan, read the Phase 1 plan, then run Phase 1 research subagent before any EXECUTE work.
- Current phase: Phase 1 — Test Infrastructure Foundation
- Next action: Spawn vc-research-agent for Phase 1
- Execute-agent start instruction: Read this file. Read Phase 1 plan. Run research subagent first.

---

## Current Execution State

Last updated: 22-06-26
Completed phases: Phase 0, Phase 1, Phase 2, Phase 3
Current phase: Phase 4 — Deployment Readiness
Current loop step: RESEARCH (pending — not yet started)
Validate-contract status: Phase 1+2+3 written and accepted (Phase 3 CONDITIONAL, accepted as-is); Phase 4 pending
Program Net Gate: PENDING
Latest validator run: 22-06-26 — backend `composer test` 181 tests/474 assertions 0 failures; frontend `npm run test` 14 files/19 tests 0 failures; `npm run build` succeeds (17 routes); `npm run lint` 7 pre-existing errors/13 pre-existing warnings unchanged; `validate-all-context.mjs` and `validate-context-discovery.mjs` both 0 warnings/0 failures

Phase 3 report: `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_REPORT_19-06-26.md`

Phase 3 closeout notes for Phase 4 RESEARCH:
- CRUD authorization for Inbound/Outbound/Shipment `update`/`destroy` is now gated (admin-only, via
  `InboundPolicy`/`OutboundPolicy`/`ShipmentPolicy`). `store`/`index`/`show` on those 3 controllers
  and all master-data CRUD remain ungated — see
  `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` (needs a product
  decision, not a Phase 4 task, but worth knowing before deployment).
- Token storage architecture changed: the Sanctum bearer token no longer touches browser
  `sessionStorage`/`localStorage` at all (`token-sync.tsx` deleted). All frontend API calls now
  thread the token via `useSession()` -> `createAuthenticatedClient(token)`. Any new frontend
  feature work after this point must follow that pattern, not the old `apiClient` singleton.
- Sanctum token expiration is now 480 minutes (8h), matching the frontend session `maxAge`. Login is
  rate-limited (`email+ip`, 5/min). Phase 4 should confirm the rate limiter's cache backend is
  consistent across however many app instances the deployment target runs — a single shared cache
  store assumption is currently undocumented.
- CSP header explicitly deferred (`process/features/go-live/backlog/csp-header_NOTE_22-06-26.md`) —
  needs dedicated browser testing, not a quick add-on; do not assume it ships with Phase 4 unless
  separately scoped.
- Module-scoped read filtering (Section 7) deferred — no data table carries a per-record `module`
  column (`process/features/go-live/backlog/module-scoped-data-filtering_NOTE_21-06-26.md`). This is
  a data-model decision, unrelated to deployment readiness.
- Also still open from Phase 2: `process/features/go-live/backlog/tables-schema-staleness_NOTE_21-06-26.md`
  — schema-dump staleness note that may resurface in Phase 4/5.

Loop step values: RESEARCH | INNOVATE | PLAN-SUPPLEMENT | PVL | EXECUTE | EVL | UPDATE-PROCESS
Orchestrator rule: read "Current loop step" and "validate-contract status" before spawning any subagent. Never spawn execute-agent when loop step is RESEARCH, INNOVATE, PLAN-SUPPLEMENT, or PVL.

Note: The Stable Program Goal above is fixed. This section is the only part that changes — update-process-agent rewrites it after every phase closeout (overwrite, not append — git history is the audit log).

---

## Validate Contract

(placeholder — vc-validate-agent writes this section before EXECUTE)
