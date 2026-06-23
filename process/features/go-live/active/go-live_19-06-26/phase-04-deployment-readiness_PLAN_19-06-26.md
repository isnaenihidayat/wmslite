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
phase stands up the minimum needed to actually be ready to run the app somewhere real: a CI
pipeline that runs the Phase 1 test suite on every push, complete production-relevant env variable
documentation, and — critically, because the MySQL database is shared live with the still-running
legacy Yii app — a tested backup mechanism and a documented rollback plan, before anyone ever
deploys anything that touches that database.

**Scope is split into 2 tiers (RESEARCH + INNOVATE, 22/23-06-26, user-approved).** This phase
EXECUTES Tier 1 only and targets `✅ VERIFIED` for Tier 1 scope alone. Tier 2 is explicitly deferred
to the backlog — see "2-Tier Scope Decision" below and the Exit Gate.

### 2-Tier Scope Decision (RESEARCH + INNOVATE, 22/23-06-26)

**Hosting context confirmed during RESEARCH + user clarification:**
- The legacy Yii app already has its own separate server/domain (`elog.id`) used live by warehouse
  staff today. That hosting is **out of scope for this repo/phase** — it will not be touched,
  migrated, or referenced as a deploy target for the new stack.
- The target architecture for the new Next.js/Laravel stack is a dedicated VPS — but **no VPS
  exists yet**. Actual deployment is deferred. Development and testing remain local-only for now.
- CI platform: GitHub Actions (confirmed, no other CI platform under consideration).

**Tier 1 — executed THIS phase (target: phase VERIFIED for this tier):**
1. CI test-only workflow (`.github/workflows/ci.yml`) — runs backend `composer test` and frontend
   `npm run test && npm run lint && npm run build` on push/PR to `main`, using a MySQL service
   container for `wmslite_test`, with composer + npm dependency caching. No deploy job. Must be
   **actually pushed and observed green on GitHub** — writing the YAML is not sufficient proof.
2. `apps/backend/.env.example` (update) + `apps/frontend/.env.local.example` (new) — document every
   variable name (never real values), including the Phase 3 additions (CORS origin, rate-limiter
   cache driver, Sanctum token expiration). `TBD` markers for values that genuinely don't exist yet
   (staging/production domains).
3. MySQL backup/restore runbook (new doc) — generic enough to apply to any future VPS. Tested once
   against `wmslite_test` (never the live `wmslite` database) as a proof-of-concept of the dump +
   restore mechanism. The runbook must say explicitly that this is a **mechanism proof**, not an
   operational guarantee against a real production host's network/disk/IO conditions.
4. Code rollback runbook (new doc) — git tag + restart strategy, documentation only. No execution
   required (there is nothing deployed yet to roll back).

**Tier 2 — BACKLOG, not executed this phase (blocked on VPS provisioning):**
- Real deploy to a VPS, a CD pipeline (deploy job added to CI), backup procedure proven against the
  live `wmslite` database on a real production host, and an actually-executed rollback.
- Redis (or other shared cache backend) for the Phase 3 login rate limiter, needed only if/when the
  VPS deployment runs multiple app instances. Not decided now — topology doesn't exist yet, and
  deciding a cache backend ahead of a real topology would be over-engineering.

See "Inner Loop Refresh Note" below for the full RESEARCH/INNOVATE narrative, and the Backlog
section for the 2 new NOTE files this split produced.

---

## Entry Gate

- Phase 1 + Phase 2 + Phase 3 exit gates passed

---

## Blast Radius

- `.github/workflows/ci.yml` (new)
- `apps/backend/.env.example` (updated — Phase 3 vars added)
- `apps/frontend/.env.local.example` (new)
- New backup/restore runbook doc (flat in this program task folder, e.g.
  `process/features/go-live/active/go-live_19-06-26/phase-04-backup-restore-runbook_REF_23-06-26.md`)
- New rollback runbook doc (flat in this program task folder, e.g.
  `process/features/go-live/active/go-live_19-06-26/phase-04-rollback-runbook_REF_23-06-26.md`)
- `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` (new)
- `process/features/go-live/backlog/rate-limiter-cache-backend_NOTE_23-06-26.md` (new)

---

## Implementation Checklist

### Step 1 — CI test-only workflow (GitHub Actions)

- [x] 1a. Create `.github/workflows/ci.yml` with two jobs (or one job, two stages): `backend` and
      `frontend`.
- [x] 1b. Backend job: spin up a MySQL service container for `wmslite_test` (matching the Phase 1
      test-DB strategy — confirm Phase 1's actual DB driver/setup approach before wiring this; do
      NOT reintroduce sqlite if Phase 1 standardized on MySQL for parity with the shared production
      schema). Cache composer dependencies. Run `composer test`.
- [x] 1c. Frontend job: cache npm dependencies (`npm ci`). Run `npm run test`, then `npm run lint`,
      then `npm run build` — fail fast on the first failing step.
- [x] 1d. Trigger on `push` and `pull_request` targeting `main`. No deploy/CD job in this workflow.
- [ ] 1e. Push the workflow file and a trivial follow-up commit (or open a PR) to actually trigger a
      real GitHub Actions run. Confirm the run is green in the GitHub Actions UI — record the run
      URL/ID as evidence. A workflow file that has never executed does not satisfy this step.

### Step 2 — Environment variable documentation

- [ ] 2a. Audit `apps/backend/.env.example` against the live `apps/backend/.env` and all Phase 3
      additions (CORS allowed origin var, rate-limiter cache driver var if one was introduced,
      Sanctum token expiration var). Add any missing variable names with `TBD`/placeholder values
      only — never copy real secrets.
- [ ] 2b. Create `apps/frontend/.env.local.example` (does not exist yet) documenting
      `NEXT_PUBLIC_LARAVEL_API_URL`, `NEXTAUTH_URL`, `NEXTAUTH_SECRET`, and any other frontend env
      var confirmed in `process/context/all-context.md`. Mark staging/production URL values as
      `TBD` since no such environment exists yet.
- [ ] 2c. Cross-check both files against `process/context/all-context.md` "Env var groups" section
      — update that context section if Phase 3 introduced a var not yet documented there.

### Step 3 — MySQL backup/restore runbook (proof-of-concept tested)

- [ ] 3a. Write a new runbook doc covering: `mysqldump` backup command (with flags suitable for a
      shared, live, multi-consumer database — e.g. consistent single-transaction dump), restore
      command, and a recommended backup schedule/retention note (advisory only, no scheduler is
      being built this phase).
- [ ] 3b. Execute the dump + restore mechanism once against the `wmslite_test` database (never
      `wmslite`). Record the exact commands run and their output/exit codes as evidence in the
      runbook or phase report.
- [ ] 3c. Add an explicit caveat section to the runbook: this is a **mechanism proof against a local
      test database**, not an operational guarantee for a real production VPS — host disk space,
      network reliability, backup storage location, and retention automation are all Tier 2/backlog
      concerns that remain unverified until a real VPS exists.

### Step 4 — Code rollback runbook (documentation only)

- [ ] 4a. Write a new runbook doc describing a git-tag-based rollback strategy: tag every deploy,
      keep the previous tag's build artifact reachable, and document the restart/redeploy steps to
      revert to the previous tag for both `apps/backend` and `apps/frontend`.
- [ ] 4b. Explicitly state that no rollback is executed this phase — there is no live deployment to
      roll back from. This is a documentation-only deliverable; execution proof is deferred to
      whichever Tier 2 phase performs the first real deploy.

### Step 5 — Backlog capture for Tier 2

- [ ] 5a. Write `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` covering:
      real VPS deploy, CD pipeline (deploy job in CI), backup procedure proven against live
      `wmslite` on a real host, and an actually-executed rollback. Blocked on VPS provisioning.
- [ ] 5b. Write `process/features/go-live/backlog/rate-limiter-cache-backend_NOTE_23-06-26.md`
      covering: whether the Phase 3 login rate limiter's cache backend needs to move to Redis (or
      similar) for correctness once the VPS deployment runs multiple app instances. Blocked on VPS
      topology being decided.

---

## Exit Gate

**Tier 1 (must pass for this phase to reach VERIFIED):**

```bash
# Local equivalent of what CI runs (for pre-push sanity; the binding proof is the real GitHub Actions run)
cd apps/backend && composer test
cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: all green, matching what CI runs
```

- `.github/workflows/ci.yml` pushed and observed green on at least one real GitHub Actions run
  (run URL/ID recorded as evidence) — no deploy job present
- `apps/backend/.env.example` updated with all Phase 1-3 variables; `apps/frontend/.env.local.example`
  created and documents all frontend variables — no real values in either file
- Backup/restore runbook written AND executed once against `wmslite_test` with recorded command
  output as evidence; runbook explicitly states it is a mechanism proof, not an operational
  guarantee for a real production host
- Rollback runbook written (documentation only; no execution required — nothing is deployed yet)
- Both backlog NOTE files written for Tier 2 items

**Tier 2 (explicitly deferred — NOT blocking this phase's closure):**

- Real VPS deploy, CD pipeline (deploy job in CI), backup procedure proven against the live
  `wmslite` database on a real production host, an actually-executed rollback, and any rate-limiter
  cache backend decision (Redis or otherwise) — all tracked in the two backlog NOTE files above and
  picked up once a VPS is provisioned.

---

## Blockers That Would Justify BLOCKED Status

- The CI workflow cannot be pushed/observed running (e.g. no GitHub Actions access, repo not
  connected to a remote that runs Actions) — this blocks Tier 1 Step 1 specifically; document and
  escalate rather than marking the step done unverified.
- The backup/restore proof-of-concept cannot be safely executed even against `wmslite_test` (e.g.
  the test DB itself is unreachable or misconfigured) — find a safe local alternative before
  marking Tier 1 Step 3 done; do not skip the verification step.
- "No hosting target decided" no longer applies — the target (a dedicated VPS) is confirmed; what
  applies instead is "the VPS has not been provisioned yet," which is the documented reason Tier 2
  is deferred to backlog, not a reason to block this phase's Tier 1 scope.

---

## Phase Loop Progress

- [x] 1. RESEARCH — research-agent: prior phase reports read; test context loaded; plan drift checked
- [x] 2. INNOVATE — innovate-agent: approach decided; Decision Summary written
- [x] 3. PLAN-SUPPLEMENT — plan-agent: existing phase plan updated (or "n/a — research clean")
- [x] 4. PVL — vc-validate-agent: full V1-V7; validate-contract written
- [ ] 5. EXECUTE — all checklist items done; per-section test gates run and green
- [ ] 6. EVL — all EVL gates green; follow-up stubs registered; EVL HANDOFF SUMMARY written
- [ ] 7. UPDATE PROCESS — phase report written, umbrella state updated, commit done

**Validate-contract required before execute.**

---

## Touchpoints

- `.github/workflows/ci.yml` (new)
- `apps/backend/.env.example` (updated), `apps/frontend/.env.local.example` (new)
- New backup/restore runbook doc, new rollback runbook doc (flat in this program task folder)
- `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` (new)
- `process/features/go-live/backlog/rate-limiter-cache-backend_NOTE_23-06-26.md` (new)
- `process/context/all-context.md` (env var section cross-check, if drift found)

---

## Public Contracts

- No application behavior changes — this phase is infrastructure/process only.

---

## Verification Evidence

```bash
# Local pre-push sanity (binding proof is the real GitHub Actions run URL/ID recorded in the phase report)
cd apps/backend && composer test
cd apps/frontend && npm run test && npm run lint && npm run build

# Backup/restore proof-of-concept (against wmslite_test ONLY — never wmslite)
mysqldump --single-transaction wmslite_test > /tmp/wmslite_test_backup.sql
mysql wmslite_test < /tmp/wmslite_test_backup.sql
# Expected: dump succeeds, restore succeeds, exact commands + exit codes recorded as evidence
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
- Last completed step: PVL (Phase Loop Progress step 4) — validate-contract written, gate CONDITIONAL.
- Validate-contract status: written (23-06-26), gate CONDITIONAL, pending user review.
- Next step: User reviews validate-contract (Open gaps + Execute-agent instructions E1-E4 below). On
  acceptance, spawn vc-execute-agent for Step 5 (PHASE LOOP), per the accepted CONDITIONAL gate.

---

## Inner Loop Refresh Note (23-06-26)

RESEARCH + INNOVATE materially changed this phase's scope and structure from the original generic
Step A/B/C plan (hosting decision -> CI -> backup/rollback, written before any research had run).

**What RESEARCH found:**
1. The legacy Yii app already runs on its own separate server/domain (`elog.id`), live, used by
   warehouse staff today. This hosting is entirely outside this repo's/phase's scope — it will not
   be touched, migrated to, or treated as a deploy target. The original plan's Step A1 framing
   ("the decision must account for the Yii app's own hosting, since they need to reach the same
   MySQL instance") assumed tighter coupling than actually exists; the two apps share only the
   MySQL database, not a hosting target.
2. The user confirmed the target architecture for the new stack is a dedicated VPS, but no VPS
   exists yet — provisioning is deferred. This means the original Step A ("choose and document the
   target environment") cannot produce a real deploy target this phase; it can only confirm the
   architecture decision (VPS) and defer everything that requires an actual host.
3. CI platform is confirmed as GitHub Actions — no further research needed there.

**What INNOVATE decided (user-approved):**
- Split the phase into Tier 1 (CI test-only workflow, env var docs, backup/rollback runbooks with a
  test-DB proof-of-concept) — executed this phase — and Tier 2 (real deploy, CD pipeline, live
  backup proof, executed rollback, rate-limiter cache backend decision) — deferred to backlog,
  blocked on VPS provisioning.
- Rejected alternative: attempting to provision a real VPS this phase to fully satisfy the original
  single-tier exit gate. Rejected because no VPS budget/target was approved yet, and forcing a VPS
  decision under phase time pressure risked a rushed, unreviewed hosting choice — the same class of
  risk the original Blockers section was written to avoid ("document the constraint and keep this
  phase open rather than forcing a choice the user hasn't approved"). The 2-tier split honors that
  original caution while still making real, verifiable progress (CI, env docs, tested backup
  mechanism) instead of leaving the entire phase blocked indefinitely.
- Rejected alternative: deciding the rate-limiter cache backend (Redis vs. shared in-memory) now.
  Rejected as premature — no deployment topology exists yet to inform whether multiple app instances
  will ever share that cache. Logged as a backlog note instead of a decision.

This Inner Loop Refresh Note supersedes the original 3-step (A/B/C) generic checklist with the
5-step Tier-1-scoped checklist above (Steps 1-5), and the single-tier Exit Gate with the 2-tier
Exit Gate above.

---

## Validate Contract

Status: CONDITIONAL
Date: 23-06-26
date: 2026-06-23
generated-by: inner-pvl: phase-04

Parallel strategy: parallel-subagents
Rationale: 4 Layer-1 dimension agents + 5 Layer-2 section agents = 9 independent read-only checks, no agent needed to see another's output mid-run (score 2/7 — MEDIUM; phase has no schema/auth/API surface, no multi-package scope beyond the existing 2 apps, no 5+ blast-radius files in a single app). Parallel subagents is the correct fit; agent-team not warranted — no mid-run coordination needed.

Test gates (C3 5-column table):

| criterion id | behavior | strategy | proving test | gap-resolution |
|---|---|---|---|---|
| step-1-ci-workflow | Backend suite green locally (pre-push sanity) | Fully-Automated | `cd apps/backend && composer test` | A |
| step-1-ci-workflow | Frontend suite/lint/build green locally (pre-push sanity) | Fully-Automated | `cd apps/frontend && npm run test && npm run lint && npm run build` | A |
| step-1-ci-workflow | Real GitHub Actions run triggered by push, observed green | Hybrid | Push CI workflow file plus a trivial follow-up commit to `main` (after E4 user go-ahead); observe Actions UI; record run URL/ID in phase report | B |
| step-1-ci-workflow | No hardcoded secrets in pushed CI YAML | Agent-Probe | Manual review of the final CI workflow file for any GitHub Secrets context usage vs hardcoded credential-shaped value | B |
| step-2-env-docs | No real secret values committed in example env files | Fully-Automated | Grep example env files for non-placeholder, non-`TBD`, non-comment value lines (manual eyeball plus grep) | A |
| step-2-env-docs | All known env vars documented and cross-checked against all-context.md | Agent-Probe | Manual diff of documented var names vs frontend auth config and backend env usage, cross-checked against the all-context.md Env var groups section | A |
| step-3-backup-runbook | Dump+restore mechanism works against `wmslite_test` | Hybrid | `mysqldump --single-transaction wmslite_test` then restore via `mysql wmslite_test` — precondition: local MySQL running, `wmslite_test` schema loaded | A |
| step-4-rollback-runbook | Executed rollback | Known-Gap | — (nothing deployed yet to roll back from) | D |

gap-resolution legend:
- A — proven now (gate passes in this cycle)
- B — fixed in this plan (gate added by this plan's checklist, executed this phase)
- C — deferred to a named later phase/plan
- D — backlog test-building stub (named residual; keep-active; continue)

C-4 reconciliation: the strategy column carries only Fully-Automated / Hybrid / Agent-Probe. Known-Gap (rollback execution) is a named residual row only, tracked via `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` (Tier 2, blocked on VPS provisioning) — not a strategy that proves the behavior.

Legacy line form (retained for existing consumers):
- CI workflow: Fully-automated: local `composer test` + frontend test/lint/build sanity | Hybrid: real GitHub Actions push-and-observe run, URL/ID recorded | Agent-probe: manual secrets-hygiene review of pushed YAML
- Env var docs: Fully-automated: grep for non-placeholder values | Agent-probe: manual completeness cross-check against all-context.md
- Backup/restore runbook: Hybrid: dump+restore proof-of-concept against `wmslite_test` only (never `wmslite`)
- Rollback runbook: Known-gap: execution deferred to Tier 2 (no live deployment exists yet) — documented in `vps-deploy-cd-pipeline_NOTE_23-06-26.md`

Dimension findings:
- Infra fit: CONCERN — MySQL service container wiring for GitHub Actions must exactly match the test-database setup command's literal database-name guard (Phase 1's confirmed MySQL-not-SQLite decision is correctly referenced in the plan, but the literal-string guard's implication for CI env wiring is not spelled out in plan text — addressed via execute-agent instruction E2).
- Test coverage: CONCERN — "CI run is green" is inherently a Hybrid/Agent-Probe-tier behavior (requires a real push to a real remote); the plan correctly scopes this as push-and-observe rather than claiming false automation. No vacuous-green risk — every developed surface in this phase has at least a Hybrid or Agent-Probe gate; rollback execution is the sole Known-Gap and is justified (nothing exists yet to roll back).
- Breaking changes: PASS — plan explicitly states no application behavior changes; infra/process only. No schema, API, or auth surface touched.
- Security surface: CONCERN — real risk correctly raised by the request brief: CI workflow YAML must use the GitHub Secrets mechanism for any credential-shaped value, never a literal real value. Mitigated: the test database is an ephemeral CI-only DB (a throwaway CI-only password is not a real secret); both example env files are documentation-only with TBD placeholders (confirmed via direct read: both apps' `.gitignore` exclude all real env files from git); no production secrets exist yet (no VPS/staging). Residual risk addressed via execute-agent instruction E1, not a blocking FAIL.
- Step 1 (CI workflow): CONCERN — mechanically feasible (commands real and runnable; dump/restore CLI tools confirmed present locally); gaps: missing explicit secrets-handling instruction and MySQL container wiring detail (addressed via E1/E2); highest-risk edit: Step 1e push-to-main (mitigated by umbrella hard-stop + E4 — requires explicit user go-ahead before pushing).
- Step 2 (env var docs): PASS — mechanically feasible, no collisions, no real-value risk (TBD placeholders only).
- Step 3 (backup runbook): PASS — mechanically feasible, dump/restore CLI tools confirmed present, safe target (test database only, never the production-shared database).
- Step 4 (rollback runbook): PASS — documentation-only, explicitly no execution required this phase.
- Step 5 (backlog notes): PASS — both target NOTE files already exist (created during INNOVATE per the Inner Loop Refresh Note); checklist phrasing says "write" but should read as "verify/finalize" (addressed via E3).

Open gaps:
- CI-secrets handling discipline not explicit in plan text — resolved via execute-agent instruction E1.
- Test-database setup command's literal-DB-name guard not cross-referenced for CI wiring — resolved via E2.
- Step 5 checklist phrasing ("write") vs already-existing backlog files — resolved via E3.
- Push-to-main approval gate for Step 1e — resolved via E4 (umbrella hard-stop already covers this; flagged so execute-agent does not auto-push without asking).

Execute-agent instructions:
- E1: When writing the CI workflow file, every credential-shaped value (test DB password, any token) MUST use the GitHub Actions Secrets context, or a clearly non-sensitive ephemeral placeholder (a throwaway CI-only MySQL password for the service container is acceptable as a literal, since the container is destroyed after the run) — never hardcode a real-looking secret. Document the choice made in the phase report.
- E2: The MySQL service container's env must set the test database name to exactly match the literal-string guard in the existing test-database setup artisan command. Confirm the schema-setup composer script succeeds in the CI job before the test runner executes; if the guard rejects the run, the CI env wiring is wrong, not the guard.
- E3: Before writing the two Tier-2 backlog NOTE files (Step 5a/5b), read the existing files first (already present in the feature's backlog folder) — verify/finalize content rather than overwriting; only update if incomplete relative to the plan's stated scope.
- E4: Step 1e (push to trigger a real CI run) requires explicit user go-ahead before pushing to `origin/main`, per the umbrella Program Goal Charter's hard stop "Any git push to origin/main" — this phase plan does not waive that constraint.

What this coverage does NOT prove:
- The Fully-Automated local pre-push gates prove the existing Phase 1-3 baseline still passes locally — they do NOT prove the GitHub-hosted runner environment behaves identically (different OS image, network conditions, dependency-resolution timing, MySQL service-container startup race).
- The Hybrid push-and-observe gate proves the workflow runs green once — it does NOT prove long-term CI stability across future dependency upgrades, concurrent/parallel run behavior, or secret rotation handling.
- The Agent-Probe secrets review proves no real secret is visible in the reviewed diff — it does NOT prove a future edit by a different contributor won't reintroduce a hardcoded value; this is a process discipline gate, not a technical control.
- The backup/restore Hybrid gate proves the dump+restore mechanism works once against a local, low-volume test-database copy — it does NOT prove production-scale data volume handling, real-host disk/network/IO reliability, or automated retention/scheduling (explicitly out of scope per plan Step 3c and deferred to Tier 2 backlog).
- The rollback runbook proves a documented strategy exists — it does NOT prove the strategy works in practice; execution proof is deferred to whichever Tier 2 phase performs the first real deploy.

Gate: CONDITIONAL (4 concerns noted above — infra fit, test coverage tier mix, security surface secrets-discipline, and Layer-2 Step 1/Step 5 phrasing gaps — all addressed via execute-agent instructions E1-E4 rather than plan-text changes, since the underlying checklist scope is already correct; 0 FAILs)
Accepted by: PENDING USER REVIEW — no /goal is active for this session; this CONDITIONAL gate requires explicit user acceptance (or a requested re-validate/plan-change) before EXECUTE may begin.

## Feasibility Probes Resolved

None — no VC-FEASIBILITY-PROBE-NEEDED signal was raised during this V2 pass. All Layer 2 questions were answerable via static file/codebase inspection (composer.json scripts, the test-database setup command's source, phpunit.xml structure, .gitignore contents, CLI tool presence) without requiring a live runtime probe.
