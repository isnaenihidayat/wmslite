---
name: note:vps-deploy-cd-pipeline
description: "Real VPS deploy, CD pipeline, live backup proof, and executed rollback — Tier 2, blocked on VPS provisioning"
date: 23-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: VPS Deploy + CD Pipeline (Tier 2 Deployment)

**Found during:** go-live Phase 4 (Deployment Readiness) RESEARCH + INNOVATE, 2-Tier Scope Decision
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`

**Target phase:** unscheduled — blocked on VPS provisioning (no VPS exists yet)

---

## Problem

Phase 4's original plan assumed a single-tier exit gate: choose a hosting target, stand up CI, and
prove a tested backup/rollback procedure, all in one phase. RESEARCH confirmed the target
architecture for the new Next.js/Laravel stack is a dedicated VPS, but **no VPS has been
provisioned yet** — there is no host, no domain, no real network/disk/IO conditions to deploy to or
test against. Forcing a VPS decision under this phase's time budget risked an unreviewed, rushed
hosting choice.

Phase 4 was split into Tier 1 (CI test-only workflow, env var documentation, backup/restore runbook
proven only against the local `wmslite_test` database, rollback runbook documentation-only) —
executed in Phase 4 — and Tier 2 (everything below) — deferred here.

## What's deferred

1. **Real VPS deploy** for both `apps/frontend` (Next.js) and `apps/backend` (Laravel) — actually
   provisioning a host, DNS, TLS, process manager/reverse proxy, and a real production `.env` with
   real values (filled in from the Tier 1 `.env.example` templates).
2. **CD pipeline** — adding a deploy job to `.github/workflows/ci.yml` (or a new workflow) that
   actually pushes a build to the VPS on merge to `main`, gated behind the existing test job.
3. **Backup procedure proven against the live `wmslite` database** — Tier 1 only proved the
   dump/restore mechanism against `wmslite_test`. This item requires running the same procedure
   against the real shared production database on the real host, including real-world constraints
   (disk space for dump files, network reliability for off-host backup storage, retention/rotation
   automation) that cannot be tested locally.
4. **Rollback actually executed** — Tier 1 only documented a git-tag-based rollback strategy. This
   item requires exercising a real rollback against a real bad deploy at least once to prove the
   documented steps work in practice.

## Why this is blocked, not just deferred-by-priority

Every item above requires a VPS to exist. There is nothing to deploy to, no real host conditions to
test backup/rollback against, and no CD target to wire CI into. This is a hard precondition, not a
scheduling choice.

## Recommendation when unblocked (VPS provisioned)

1. Re-read the Tier 1 runbooks (backup/restore, rollback) written in Phase 4 — they are the starting
   point, not a from-scratch effort.
2. Provision the VPS, point DNS, set up TLS, fill in the real `.env` values from the Tier 1
   `.env.example` templates (never commit the filled values to git).
3. Add the CD deploy job to CI, gated behind the existing test job passing.
4. Re-run the backup/restore proof-of-concept against the live `wmslite` database on the real host
   (with appropriate care — this is the production database also used live by the Yii app) and
   record evidence.
5. Exercise one real rollback (e.g. deploy a trivial intentional break, then roll back via the
   documented git-tag strategy) and record evidence.
6. Cross-check `process/features/go-live/backlog/rate-limiter-cache-backend_NOTE_23-06-26.md` —
   once the real deployment topology is known (single instance vs. multiple), resolve that note too.

## Related

- `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
  — Phase 4 plan, 2-Tier Scope Decision section and Inner Loop Refresh Note
- `.github/workflows/ci.yml` (Phase 4, Tier 1) — the test-only workflow this item extends with a
  deploy job
- `apps/backend/.env.example`, `apps/frontend/.env.local.example` (Phase 4, Tier 1) — the variable
  name templates this item fills in with real values
- New backup/restore and rollback runbooks (Phase 4, Tier 1) — the documents this item proves out
  against a real host
