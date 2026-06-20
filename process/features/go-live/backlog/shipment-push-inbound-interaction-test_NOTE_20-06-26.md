---
name: note:shipment-push-inbound-interaction-test
description: "Deferred — full frontend interaction test for the Shipment push-to-inbound dialog"
date: 20-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Deferred: Shipment Push-to-Inbound Dialog Interaction Test

**Originated during:** go-live Phase 1 (Test Infrastructure Foundation) validate-contract (PVL),
Section III "Missing test areas" / Known-gap accepted with rationale
**Confirmed still deferred at:** Phase 1 EVL/closeout, 20-06-26

---

## What This Covers

A full frontend interaction test for the Shipment module's "push to inbound" dialog: open dialog,
fill form, submit, verify the resulting `from_shipment`/inbound-header state change — going beyond
the Phase 1 smoke-test tier, which only asserts the dialog's trigger button is present
(`apps/frontend/src/app/(dashboard)/shipment/page.test.tsx`).

## Why Deferred

Two compounding reasons, both still valid as of Phase 1 closeout:

1. **Original PVL rationale (19-06-26):** the underlying shipment-status-flow semantics are an
   unconfirmed Open Question (`process/context/all-context.md`), explicitly Phase 2 scope. Writing
   a deep interaction test now risks asserting against semantics Phase 2 will change.
2. **New finding at Phase 1 closeout (20-06-26):** the backend endpoint this dialog calls
   (`ShipmentController::pushInbound()`) is currently broken — always returns HTTP 500 (see
   `shipment-push-inbound-bug_NOTE_20-06-26.md`). Writing this interaction test before that bug is
   fixed would either require mocking around broken behavior (testing nothing useful) or fail
   immediately against the real flow.

## Recommendation

Stay deferred until BOTH of the following are true:
- `shipment-push-inbound-bug_NOTE_20-06-26.md` is fixed (as part of Phase 2, per that note's
  recommendation — fix happens alongside the status-flow confirmation, not before it)
- Phase 2's shipment-status-flow Open Question has a written, user-confirmed answer

Then write this test against the confirmed, working behavior — not before.

## Related

- `process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md`
- `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
