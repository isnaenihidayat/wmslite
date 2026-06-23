---
name: note:rate-limiter-cache-backend
description: "Login rate limiter cache backend (Redis vs shared in-memory) for multi-instance VPS deployment — blocked on deployment topology"
date: 23-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: Rate Limiter Cache Backend for Multi-Instance Deployment

**Found during:** go-live Phase 4 (Deployment Readiness) RESEARCH + INNOVATE, 2-Tier Scope Decision
(originally flagged in Phase 3 closeout notes, carried forward)
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`,
`process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md` (Phase 3
closeout notes for Phase 4 RESEARCH)

**Target phase:** unscheduled — blocked on VPS deployment topology being decided

---

## Problem

Phase 3 added login rate limiting (`email+ip`, 5/min) using Laravel's default rate limiter, backed
by whatever cache driver the app is configured to use. Phase 3's closeout note flagged: "Phase 4
should confirm the rate limiter's cache backend is consistent across however many app instances the
deployment target runs — a single shared cache store assumption is currently undocumented."

Phase 4 RESEARCH confirmed: no VPS exists yet, so no deployment topology (single instance vs.
multiple instances behind a load balancer) has been decided. Deciding a cache backend (e.g. Redis)
now, before a topology exists, would be speculative over-engineering — there is nothing to validate
the decision against yet.

## Why this matters (when it does matter)

If the eventual VPS deployment ever runs **more than one app instance** of the Laravel backend
(e.g. behind a load balancer, or multiple worker processes that don't share process memory), a
rate limiter backed by a non-shared cache driver (e.g. the default `array` or `file` driver, or an
in-process cache) will not see requests handled by other instances — each instance enforces its own
independent 5/min counter, effectively multiplying the real rate limit by the instance count and
defeating the control.

If the deployment only ever runs **a single instance**, the current cache driver is almost
certainly fine and no change is needed.

## Recommendation when unblocked (VPS topology decided)

1. Confirm the actual deployment topology from the Tier 2 VPS deploy work
   (`process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`).
2. If single-instance: close this note as "not needed" with a one-line confirmation in the relevant
   phase report.
3. If multi-instance: add Redis (or another shared cache store reachable by all instances) as the
   cache driver for rate limiting specifically (does not need to be the app's general-purpose
   cache driver if a narrower scope is preferred), update `apps/backend/.env.example` with the new
   variable names, and verify the rate limit is correctly shared across instances (e.g. hit the
   login endpoint from two different instances/processes and confirm the combined count, not the
   per-instance count, triggers the limit).

## Related

- `apps/backend/app/Http/Middleware/` (Phase 3) — wherever the login rate limiter is configured
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
  (Section 3 — Login rate limiting) — the original implementation
- `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
  — Phase 4 plan, 2-Tier Scope Decision section
- `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` — the topology decision
  this note is blocked on
