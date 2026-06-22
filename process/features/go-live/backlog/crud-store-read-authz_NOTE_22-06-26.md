---
name: note:crud-store-read-authz
description: "store/index/show access control for Inbound/Outbound/Shipment (and master-data CRUD) deferred — no confirmed business rule yet"
date: 22-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: CRUD `store`/`index`/`show` Authorization

**Found during:** go-live Phase 3 (Security & Validation Audit), Section 1
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`

**Target phase:** unscheduled — needs a product/user decision before a phase can be planned

---

## Problem

Phase 3 Section 1 gated only `update`/`destroy` on `InboundController`, `OutboundController`, and
`ShipmentController` (via `InboundPolicy`/`OutboundPolicy`/`ShipmentPolicy`, admin-only). `store`,
`index`, and `show` on those same 3 controllers remain entirely ungated — any authenticated user can
create new Inbound/Outbound/Shipment records and read all of them, regardless of `type`/`module`.

The same gap exists for all master-data CRUD controllers (locations, product categories,
recipients) — none of their `store`/`index`/`show` methods were touched by this phase either.

## Why this was deferred (not fixed in Phase 3)

Unlike `update`/`destroy` (where "destructive/mutating action → admin-only" is an unambiguous,
already-established pattern from Phase 2's `MovingPolicy`/`UserPolicy`), there is no confirmed
business rule for **who may create or read** records per module. The two scanner-account modules
(OTR vs Service, see `el_apk`/`el_apk_s` in `process/context/database/all-database.md`) suggest
warehouse staff may be segmented by module, but nothing in the current codebase enforces or even
references that segmentation for Inbound/Outbound/Shipment/master-data records — `type`/`module`
fields are user attributes only, never checked against these tables anywhere (see also the related
Section 7 backlog note on module-scoped read filtering, which hit the identical "no schema/business
rule" blocker).

## Recommendation

This needs a dedicated product/user decision before a plan can be written:

- Should any authenticated user be able to `store` an Inbound/Outbound/Shipment record, or only
  certain `type`/`module` combinations?
- Should `index`/`show` results be filtered by the requesting user's `module`, or is full visibility
  across modules intentional today?
- Does the answer differ per module (OTR vs Service) or per resource (Inbound vs Outbound vs
  Shipment vs master-data)?

Once those are answered, the implementation pattern itself is already established (Policy class per
resource, `Gate::policy()` registration in `AppServiceProvider::boot()`, `$this->authorize(...)` in
the controller) — see `InboundPolicy`/`OutboundPolicy`/`ShipmentPolicy` (Phase 3) and
`MovingPolicy`/`UserPolicy`/`ApkUserPolicy` (Phase 2) for the convention to extend.

## Related

- `process/features/go-live/backlog/module-scoped-data-filtering_NOTE_21-06-26.md` — the read-side
  counterpart of this same underlying gap (no per-record module attribution exists in the schema)
- `process/features/go-live/backlog/policy-formalization-remaining-controllers_NOTE_21-06-26.md` —
  the original Phase 2 note that first flagged the broader authorization gap, now partially closed
  by Phase 3 Section 1 (update/destroy only)
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md` —
  Section 1 (Critical CRUD authorization gap), item 1j
