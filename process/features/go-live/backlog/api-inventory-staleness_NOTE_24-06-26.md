---
name: note:api-inventory-staleness
description: "API_INVENTORY.md is a pre-implementation target doc, not a record of actual implementation — full re-audit against the live Laravel backend is recommended before relying on it again"
date: 24-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: `API_INVENTORY.md` Full Re-Audit

**Found during:** go-live Phase 5 (Legacy Yii Cutover), Step A1/D1
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md`

---

## Problem

`API_INVENTORY.md` (~134 endpoints) was generated as a **pre-implementation target** document —
an audit of the legacy `AjaxController.php`/`ApiController.php` intended to guide construction of
the Laravel REST API, written before that Laravel backend existed. It was never updated to reflect
what was actually built.

Phase 5's RESEARCH confirmed this document cannot be treated as ground truth for "100% endpoint
parity" decisions: go-live Phases 2 and 3 independently found material gaps between the
`API_INVENTORY.md` target list and the live Laravel implementation in at least **Inbound,
Outbound, Moving, and Reports**. The clearest concrete example: `MovingController` exposes only
`index`, `store`, and `destroy` in the live codebase — there is no `update` method at all, despite
whatever the target inventory implies about Moving's endpoint coverage.

Phase 5 worked around this by classifying modules using 3 directly-observable signals (test
coverage depth, implementation complexity, FK/independence) instead of `API_INVENTORY.md` parity,
and added a staleness header annotation to the top of the document (see Step D1) pointing future
readers at the Step A1 classification table and the Phase 2/3 findings. No full re-audit was
performed — that was explicitly out of scope for Phase 5.

## Recommendation

Before `API_INVENTORY.md` is relied on again for any cutover-readiness or endpoint-parity
decision, perform a full re-audit: for each of the ~134 entries, confirm whether the corresponding
Laravel endpoint actually exists, with what method signature, and whether its behavior matches the
original Yii action's contract. Record actual vs. target gaps explicitly (module by module),
rather than assuming the document is current. This is a moderate-effort, low-risk documentation
task — no behavior change required, but real effort to verify each row against the live
`apps/backend/routes/api.php` and corresponding controllers.

## Related

- `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md` — Step
  A1 classification table, Step D1 header annotation
- `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
  — original `MovingController` CRUD-gap finding
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
- `API_INVENTORY.md` — staleness header annotation added by this same phase (Step D1)
