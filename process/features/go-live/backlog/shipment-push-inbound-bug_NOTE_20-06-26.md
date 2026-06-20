---
name: note:shipment-push-inbound-bug
description: "Bug — ShipmentController::pushInbound() always returns HTTP 500; dead 409-conflict branch"
date: 20-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Bug: `ShipmentController::pushInbound()` Always Returns HTTP 500

**Found during:** go-live Phase 1 (Test Infrastructure Foundation), Step B5/B7 test writing
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_REPORT_19-06-26.md`

---

## Problem

`ShipmentController::pushInbound()` (the action behind the frontend's "push to inbound" button on
the Shipment module) always returns HTTP 500 in practice, regardless of input.

## Root Cause

The method creates a new `el_inbound_header` row using the shipment's `hawb` value. The
`el_inbound_header` table has a unique constraint on `hawb`. Every existing shipment in this
codebase's typical data shape already has at least one prior inbound record sharing the same
`hawb`, so the `INSERT` throws a SQL unique-constraint-violation exception before any of the
method's own conflict-handling logic runs.

The method DOES contain a 409-conflict branch intended to handle exactly this duplicate case
gracefully (return a structured 409 response instead of a raw 500) — but that branch is
unreachable dead code, because the unique-constraint violation is thrown by the database during
the `INSERT` itself, before the conditional duplicate-check logic that would route to the 409
branch is ever evaluated.

## Recommendation

**Do not fix this as an isolated dirty fix.** Fix it as part of Phase 2's planned work, not before
or separately from it.

Rationale: Phase 2 (`process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`)
already has explicit scope to resolve the `process/context/all-context.md` Open Question on
shipment-status-flow semantics — specifically, how `from_shipment`/inbound-header linkage is
*supposed* to behave when a shipment is pushed to inbound. This bug sits exactly inside that
semantic question: a correct fix requires first deciding what should happen when a shipment with
an already-used `hawb` is pushed (update the existing inbound record? reject with a clear error?
allow duplicate inbound entries with a different uniqueness scope?). Fixing the symptom (catching
the exception, returning the already-written 409 response) without first confirming the intended
behavior risks fixing it twice — once as a patch, again when Phase 2 changes the underlying
semantics.

**Action for Phase 2:** fold this bug into Phase 2's first RESEARCH step as "confirm intended
push-to-inbound behavior when `hawb` collides, then implement the fix as part of that confirmed
behavior" — not as a standalone hotfix ticket.

## Related

- `process/features/go-live/backlog/shipment-push-inbound-interaction-test_NOTE_20-06-26.md` — the
  deferred frontend interaction test for this same dialog/flow; should stay deferred until this bug
  is fixed and status-flow semantics are confirmed (testing now would assert against broken
  behavior).
