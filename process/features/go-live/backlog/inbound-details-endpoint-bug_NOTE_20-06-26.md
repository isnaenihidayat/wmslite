---
name: note:inbound-details-endpoint-bug
description: "Bug — InboundController::details() always returns HTTP 500 (orderBy on nonexistent column)"
date: 20-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Bug: `InboundController::details()` Always Returns HTTP 500

**Found during:** go-live Phase 1 (Test Infrastructure Foundation), Step B6 test writing
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_REPORT_19-06-26.md`

---

## Problem

The `/inbound/{id}/details` endpoint always returns HTTP 500.

## Root Cause

The query orders results with `orderBy('id')` against `el_inbound_details`. Confirmed directly
against `tables_schema.sql`: `el_inbound_details` has no `id` column at all. Every call throws a
SQL error ("Unknown column 'id'") before any rows can be returned.

## Recommendation

**Simple, low-risk fix — no semantic ambiguity, no overlap with Phase 2's Open Questions.** Change
`orderBy('id')` to an existing column on `el_inbound_details`, e.g. `hawb` or `scan_time` (confirm
exact intended sort order against the actual table columns and any frontend display expectation
before picking the final column).

Unlike the `ShipmentController::pushInbound()` bug (see
`shipment-push-inbound-bug_NOTE_20-06-26.md`), this fix does not depend on any unresolved
status-flow or data-model decision. It can be picked up as an early Phase 2 quick-fix, independent
of the auth/data-model hardening work, or even as a standalone hotfix before Phase 2 RESEARCH
starts if the user wants it fixed immediately.

## Related

- `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
  — Phase 2 phase plan, natural home for this quick-fix
