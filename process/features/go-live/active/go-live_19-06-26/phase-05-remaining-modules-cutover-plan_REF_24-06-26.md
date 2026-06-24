---
name: ref:go-live-phase-05-remaining-modules-cutover-plan
description: "WMS Lite go-live — Phase 5: local cutover plan for the 8 modules not covered by the Tier 1 Monitoring proof"
date: 24-06-26
metadata:
  node_type: memory
  type: reference
  feature: go-live
  phase: phase-05
---

# Phase 05 — Remaining 8 Modules: Local Cutover Plan

**Program:** go-live
**Phase plan:** `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md`
(Step C1)

This document is the **local mechanism plan** only — it covers cutting each module's local Yii
route over to the equivalent local Next.js page, the same proof pattern used for Monitoring (Tier
1, this phase). It does **not** cover cutting any module over on the live `elog.id` production
server. Real production cutover for all 9 modules is Tier 2, deliberately kept as a separate
backlog item: see
`process/features/go-live/backlog/yii-production-cutover-elogid_NOTE_24-06-26.md`.

Order below follows the Step A2 risk classification (lowest risk / most isolated first), reusing
the Step A1 table verbatim from the phase plan. Each module gets one paragraph: order rationale,
validation approach, owner, target timeframe.

---

## 1. Master Data (locations, categories, recipients, apk accounts)

**Order rationale:** second-lowest risk after Monitoring — low-moderate implementation complexity
(straightforward CRUD across several small controllers), good Phase 1 test coverage, and low FK
coupling in the dependency direction that matters for cutover safety (other modules reference
Master Data records via FK, but Master Data itself does not depend on any other module's state).
Cutting this module over first, right after Monitoring, keeps the early cutover sequence
low-risk and reversible. **Validation approach:** manual side-by-side check, same pattern as
Monitoring — open the Yii master-data pages and the Next.js `/master` pages locally, compare list
contents and CRUD behavior (create/edit/delete a test record on one side, confirm it appears
correctly on the other via the shared MySQL database) before and after the redirect is applied.
No scripted diff needed at this complexity level. **Owner:** the user. **Target timeframe:** TBD
(unscheduled).

## 2. Moving

**Order rationale:** would otherwise rank alongside Master Data for risk (good coverage on
existing methods, moderate complexity, low-moderate coupling — it mostly functions as a movement
ledger referencing Location/Inbound), but carries an **explicit, documented prerequisite that
blocks cutover**: the Laravel `MovingController` currently exposes only `index`, `store`, and
`destroy` — there is no `update` method (confirmed directly in Phase 2/3 RESEARCH and reconfirmed
in this phase's RESEARCH/INNOVATE pass, 24-06-26). Any Yii `Moving` edit/update workflow that gets
redirected to the Next.js page before this gap closes would silently lose update functionality for
warehouse staff, which is a regression, not a cutover. **This gap must be closed first** —
likely as a small Phase 2/3-style follow-up plan adding `MovingController::update()` (with a
matching frontend edit flow and test coverage) — before Moving can be added to the local cutover
sequence. **Validation approach (once the gap is closed):** manual side-by-side check identical to
Master Data's, plus an explicit edit/update scenario to confirm the new `update` endpoint behaves
correctly end-to-end. **Owner:** the user. **Target timeframe:** TBD (unscheduled; gated on the
CRUD-gap follow-up being scheduled and completed first — no date implied by this document).

## 3. Reports

**Order rationale:** read-mostly module with moderate test coverage; it aggregates data from
multiple other modules (Inbound/Outbound/Shipment) but exposes few or no write actions, so the
cutover risk is bounded by "does the aggregation render correctly," not by any write-path
correctness concern. Ordered after the two lowest-risk modules, ahead of the three remaining
moderate-risk modules since reports are the least likely to surprise users with a behavior change.
**Validation approach:** manual side-by-side check comparing rendered report figures/tables
between the Yii and Next.js pages for the same underlying data; given Reports aggregates several
modules, a scripted diff tool would be a reasonable later investment if discrepancies prove hard to
spot by eye — flagged here as a tooling suggestion only, not built now. **Owner:** the user.
**Target timeframe:** TBD (unscheduled).

## 4. Admin / User Management

**Order rationale:** moderate risk — covered by the `UserPolicy` formalization from Phase 2, with
5 `UserController` methods and low cross-module FK coupling (it operates mostly on its own `User`
model). Grouped with Reports and Dashboard as a "read-mostly/moderate" band; ordered here because
user-management actions (approve/decline/block/delete) are write-paths, slightly riskier than pure
read-only Reports, but still well isolated from the inventory-data modules. **Validation approach:**
manual side-by-side check covering both the read views (user list, user info) and a representative
write action (e.g. approve or block a test user) confirmed against the shared database from both
the Yii and Next.js sides. **Owner:** the user. **Target timeframe:** TBD (unscheduled).

## 5. Dashboard

**Order rationale:** read-mostly, smoke-level test coverage, moderate risk because it aggregates
summary data across Inbound/Outbound/Shipment/Moving — its correctness depends on those modules'
data being in a consistent state, but it makes no writes of its own. Ordered last among the
moderate-risk band since it is the most purely observational (no write-path risk at all) but its
correctness is hardest to fully verify by eye given how many modules feed into it. **Validation
approach:** manual side-by-side check of dashboard summary figures/widgets against the Yii
dashboard for the same point in time; same tooling-suggestion caveat as Reports — a scripted diff
could help here too if visual comparison proves unreliable, not built now. **Owner:** the user.
**Target timeframe:** TBD (unscheduled).

## 6. Shipment

**Order rationale:** high risk — full status-flow lifecycle
(`Air/Ocean Intransit -> Custom Process -> Warehouse in Transit -> successful`), Schenker sync
integration, and high coupling (depends on Outbound completion). Known gaps surfaced during Phase
2/3 audits (e.g. the previously-buggy push-inbound interaction, now fixed — see
`process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md`) mean this module
needs the most careful validation of any module before Inbound/Outbound. **Validation approach:**
manual side-by-side check is likely insufficient alone given the multi-state lifecycle and the
Schenker sync side effects — a scripted/repeatable diff tool comparing shipment status transitions
between Yii and Next.js across a representative set of test shipments is recommended as a tooling
investment before this module's cutover, flagged here, not built now. **Owner:** the user.
**Target timeframe:** TBD (unscheduled).

## 7. Inbound

**Order rationale:** high risk — the bulk-vs-per-lot/serial (`_s` table) dual pattern adds real
implementation complexity, many distinct Yii actions to replace, and it is upstream of Outbound
(the origin of all stock), so any cutover mistake here propagates downstream. Material gaps were
already found between `API_INVENTORY.md`'s target endpoint list and the live Laravel
implementation during Phase 2/3 audits — see the `API_INVENTORY.md` header annotation (Step D1)
for where to find those findings. Ordered ahead of Outbound because Outbound depends on Inbound's
stock data, so Inbound logically cuts over first within the highest-risk pair. **Validation
approach:** manual side-by-side check covering both the bulk and per-lot/serial code paths
explicitly (not just one), given the dual-pattern complexity; a scripted diff tool is a reasonable
investment here too given the stakes of getting stock-origin data wrong — flagged, not built now.
**Owner:** the user. **Target timeframe:** TBD (unscheduled).

## 8. Outbound

**Order rationale:** highest risk of all 9 modules — same bulk/lot dual pattern as Inbound, plus
direct linkage to Shipment, and it depends on Inbound stock being correct. Material gaps vs.
`API_INVENTORY.md`'s target list were also found here during Phase 2/3 audits. Ordered last
because it has the most downstream dependents (Shipment) and the most upstream dependencies
(Inbound), making it the module where a cutover mistake has the widest blast radius across the
whole warehouse workflow. **Validation approach:** manual side-by-side check covering both code
paths (bulk and per-lot/serial) plus an end-to-end check that a completed Outbound correctly feeds
into Shipment on both the Yii and Next.js sides; a scripted diff tool is the strongest
recommendation of any module in this list, given it is both highest-complexity and
highest-coupling. **Owner:** the user. **Target timeframe:** TBD (unscheduled).

---

## Notes

- This plan is local-mechanism-only. None of the above modules should be cut over against the live
  `elog.id` production server using this document as authority — see
  `process/features/go-live/backlog/yii-production-cutover-elogid_NOTE_24-06-26.md` for the
  separately-tracked Tier 2 production scope.
- Moving (module 2) has a hard prerequisite blocking its place in this sequence: the
  `MovingController` CRUD gap (no `update` method) must close first.
- Several modules (Shipment, Inbound, Outbound, and optionally Reports/Dashboard) flag a scripted
  diff tool as a worthwhile future investment. None of these tools are built as part of this
  phase — manual side-by-side validation, the same pattern proven on Monitoring, remains the
  baseline approach for every module until/unless the user decides to invest in tooling.
