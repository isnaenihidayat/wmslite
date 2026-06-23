---
name: report:go-live-phase-04-deployment-readiness
description: "WMS Lite go-live — Phase 4 closeout report: Deployment Readiness (Tier 1)"
date: 23-06-26
metadata:
  node_type: memory
  type: report
  feature: go-live
  phase: phase-04
---

# Phase 04 — Deployment Readiness — Closeout Report

**Program:** go-live
**Phase plan:** `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
**Status:** ✅ VERIFIED (Tier 1 scope only — CONDITIONAL gate, accepted; Tier 2 deferred to backlog)
**Date:** 23-06-26

---

## Summary

Phase 4 stood up the minimum deployment-readiness infrastructure WMS Lite has never had: a real CI
pipeline, complete env variable documentation, and a tested database backup/restore mechanism plus
a documented rollback strategy — all before any actual hosting target exists.

RESEARCH + INNOVATE (22/23-06-26) found the original single-tier plan assumed a hosting decision
that couldn't actually be made yet (no VPS provisioned), and split scope into **Tier 1** (executed
this phase) and **Tier 2** (real deploy/CD/live backup/executed rollback — blocked on VPS
provisioning, deferred to backlog). This phase targets and reaches `✅ VERIFIED` for Tier 1 only.

PVL returned **CONDITIONAL** (0 FAILs, 4 concerns — infra fit, test-tier mix, security-surface
secrets discipline, Step 1/5 checklist phrasing gaps), all resolved via execute-agent instructions
E1-E4 rather than plan-text changes. User accepted the CONDITIONAL gate before EXECUTE began.

### What was built

1. **CI workflow** (`.github/workflows/ci.yml`) — backend (`composer test` against a MySQL service
   container) and frontend (`npm run test && npm run lint && npm run build`) jobs, triggered on
   push/PR to `main`. No deploy job. **Actually pushed and observed green** — not just written:
   - Run 1 (`28008703575`): failed both jobs. Found 2 real latent issues with no prior automated
     check: `composer.lock` requires PHP >=8.4 (CI was pinned to 8.3) and 7 pre-existing
     `react-hooks/set-state-in-effect` lint errors across 7 dashboard pages.
   - Run 2 (`28009522620`): frontend green, backend still failed — `apps/backend/bootstrap/cache/`
     is untracked by git, so a fresh CI checkout lacked it and Laravel's `package:discover`
     post-autoload script aborted.
   - Run 3 (`28009757927`): **both jobs green.**
     Evidence: https://github.com/isnaenihidayat/wmslite/actions/runs/28009757927
2. **Env var documentation** — `apps/backend/.env.example` audited and updated (Phase 3 additions:
   CORS origin placeholder, Sanctum config vars, rate-limiter cache driver note);
   `apps/frontend/.env.local.example` created (new file, documents `NEXT_PUBLIC_LARAVEL_API_URL`,
   `NEXTAUTH_URL`, `NEXTAUTH_SECRET`). No real values in either file. `all-context.md` Env var
   groups section cross-checked and updated in the same pass (corrected: `SANCTUM_STATEFUL_DOMAINS`
   and `SANCTUM_TOKEN_PREFIX` are real env-backed config keys, previously undocumented; documented
   the CORS hardcoded-origin gap explicitly).
3. **Backup/restore runbook** (`phase-04-backup-restore-runbook_REF_23-06-26.md`) — `mysqldump`
   single-transaction backup + restore commands, executed once as proof-of-concept against
   `wmslite_test` only (never `wmslite`): 34/34 tables match, checksums identical, test DB left
   clean. Runbook explicitly states this is a mechanism proof, not an operational guarantee for a
   real production host.
4. **Rollback runbook** (`phase-04-rollback-runbook_REF_23-06-26.md`) — git-tag-based rollback
   strategy, documentation only; explicitly states no rollback was executed (nothing deployed yet).
5. **2 Tier-2 backlog NOTEs** — `vps-deploy-cd-pipeline_NOTE_23-06-26.md` and
   `rate-limiter-cache-backend_NOTE_23-06-26.md` (written during INNOVATE, verified present/complete
   during EXECUTE Step 5).

### Bugs fixed during EXECUTE (CI-discovery-driven, user-approved scope addition)

- CI PHP version bumped 8.3 → 8.5 to match the actual dev environment and `composer.lock`'s real
  resolved minimum.
- 7 pre-existing `react-hooks/set-state-in-effect` lint errors across 7 dashboard pages fixed via a
  shared `useResetPageOnFilterChange` hook — no behavior change. These predated Phase 4 but had
  never been caught by any automated gate before CI existed; CI couldn't go green otherwise.
- `mkdir -p bootstrap/cache` step added before `composer install` in the CI backend job —
  `apps/backend/bootstrap/cache/` is git-ignored, so a fresh CI checkout needs this directory
  created explicitly before Laravel's `package:discover` post-autoload script runs.

### New backlog finding (not previously tracked)

- `apps/backend/composer.json` declares `"php": "^8.3"` but `composer.lock` resolves dependencies
  requiring PHP >=8.4 — stale constraint string. Out of Phase 4's docs/CI-only blast radius; logged
  to `process/features/go-live/backlog/composer-php-constraint-stale_NOTE_23-06-26.md`.

---

## EVL Regression Check (against Phase 1+2+3, all VERIFIED)

- `cd apps/backend && composer test` → 181 tests, 474 assertions, **0 failures** (unchanged count —
  Phase 4 added no new backend tests, only fixed CI portability)
- `cd apps/frontend && npm run test` → 14 test files, 19 tests, **0 failures** (unchanged count)
- `cd apps/frontend && npm run lint` → **0 errors** (13 pre-existing unrelated warnings), down from
  7 errors fixed this phase
- `cd apps/frontend && npm run build` → succeeds, all routes compile
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0 warnings/failures
- Real CI evidence: 3 GitHub Actions runs observed, final run both jobs green (see above)
- Backup/restore proof-of-concept: 34/34 tables match, checksums identical

No prior test broken. Only the 7 pre-existing lint errors (present since Phase 1, never blocking
before CI existed) were fixed as part of getting CI green.

---

## Tier 2 — Explicitly Deferred (Backlog)

Not executed this phase, blocked on VPS provisioning:

- Real VPS deploy, CD pipeline (deploy job added to CI)
- Backup procedure proven against the live `wmslite` database on a real production host
- An actually-executed rollback
- Rate-limiter cache backend decision (Redis or similar) — only relevant once multiple app
  instances exist

Tracked in `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` and
`process/features/go-live/backlog/rate-limiter-cache-backend_NOTE_23-06-26.md`.

---

## Closeout Notes for Phase 5 RESEARCH

- CI now exists and is green — any Phase 5 Yii cutover work that touches backend/frontend code
  should expect CI to run on every push to `main` and must keep it green.
- `apps/backend/composer.json`'s PHP constraint is stale (`^8.3` vs actual `>=8.4` resolved) — worth
  fixing in a future small cleanup pass, not blocking for Phase 5.
- Tier 2 deployment items (real VPS, CD, live backup, rollback execution) remain blocked on VPS
  provisioning — Phase 5's Yii cutover plan should account for this; cutover likely cannot fully
  complete until a real hosting target exists, but the *plan* itself can still be written.
- Env var documentation is now complete and current for everything that exists today (Phase 1-4) —
  any new env var introduced in Phase 5 should be added to both `.env.example` files and the
  `all-context.md` Env var groups section in the same pass it's introduced.

---

## Status

**Phase 4 (Tier 1):** ✅ VERIFIED
**Validate-contract:** CONDITIONAL gate, accepted by user before EXECUTE; all 4 concerns resolved
via execute-agent instructions E1-E4.
**Regression:** No prior phase's tests broken.
