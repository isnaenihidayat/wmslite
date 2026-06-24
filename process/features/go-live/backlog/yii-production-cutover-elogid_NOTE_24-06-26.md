---
name: note:yii-production-cutover-elogid
description: "Tier 2 backlog — real cutover of all 9 modules against the live elog.id production Yii server, including a webserver-level redirect mechanism; blocked on a user decision to schedule production changes"
date: 24-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: Tier 2 — Real Production Cutover Against `elog.id`

**Found during:** go-live Phase 5 (Legacy Yii Cutover), 2-Tier Scope Decision (RESEARCH + INNOVATE,
24-06-26)
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md`

---

## Problem / Scope

Phase 5 split its scope into two tiers. **Tier 1** (executed in Phase 5) proved the cutover
mechanism end-to-end, but **locally only**: an in-controller redirect inside the local filesystem
copy of the Yii app (`OtrController::actionMonitoring()`) pointing the Monitoring module's route at
the local Next.js dev server. This proof has **zero effect on the live production server**.

**Tier 2** — explicitly deferred to this backlog item — is the real cutover:

1. Apply the same redirect pattern (or an equivalent, production-appropriate mechanism — likely a
   webserver-level rewrite/redirect at the nginx/Apache layer rather than the in-controller
   approach used for the local proof, since editing live PHP source directly on a production host
   carries more risk than a reversible webserver config change) for all 9 modules, not just
   Monitoring, against the live `elog.id` host.
2. Sequence the 9 modules using the risk ordering already established in Phase 5 Step A2 and
   detailed per-module in
   `process/features/go-live/active/go-live_19-06-26/phase-05-remaining-modules-cutover-plan_REF_24-06-26.md`:
   Monitoring (already proven, Tier 1) -> Master Data / Moving (Moving blocked on closing its
   `MovingController` missing-`update` CRUD gap first) -> Reports / Admin/User Management /
   Dashboard -> Shipment / Inbound / Outbound (highest coupling and complexity, last).
3. Each module's production cutover needs its own parallel-run validation against real production
   data and real warehouse-staff traffic — meaningfully higher stakes than the local Tier 1 proof,
   which used no live traffic.
4. A production-grade rollback mechanism (webserver config revert, not just `git revert` on a local
   PHP file) must be confirmed working before any module is cut over in production.

## Why This Is Blocked, Not Scheduled

`elog.id` is confirmed (Phase 4 RESEARCH) to be a live production server actively used by warehouse
staff today. Cutting any module over there is a real-traffic change with real operational risk —
unlike every other change in this go-live program so far, which has been local-only. This requires
an explicit decision from the user about *when* to schedule downtime/risk windows against a system
people currently depend on for their work. No such scheduling decision has been made as of Phase 5
close.

## Recommendation

When the user is ready to schedule this:

1. Re-confirm the Step A1/A2 module risk ordering and the per-module notes in the remaining-modules
   plan doc are still accurate (re-check for drift, especially the Moving CRUD gap and any new
   findings from modules touched between Phase 5 and the scheduling decision).
2. Design the production redirect mechanism explicitly (webserver-level recommended over editing
   live PHP source) before touching `elog.id`.
3. Cut over Monitoring first in production (lowest risk, already proven locally) to validate the
   production mechanism itself before applying it to the remaining 8 modules.
4. Treat each subsequent module as its own small cutover with its own parallel-run validation and
   rollback dry-run, mirroring the Phase 5 Tier 1 proof pattern but against live data and traffic.

## Related

- `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md` — 2-Tier
  Scope Decision, Step D4
- `process/features/go-live/active/go-live_19-06-26/phase-05-remaining-modules-cutover-plan_REF_24-06-26.md`
  — per-module local-mechanism plan for the remaining 8 modules (local-only scope; this NOTE is the
  separate production-`elog.id` scope, kept apart on purpose)
