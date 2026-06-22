# Module-Scoped Data Filtering — Deferred (Phase 3 Section 7 gap)

**Origin:** go-live Phase 3 (Security & Validation Audit), validate-contract PVL pass, 21-06-26.

## What was planned

Phase 3 Section 7 intended to filter read-only endpoints (`ReportController`, `MonitoringController`,
`DashboardController`, `MasterController`) so a user only sees data belonging to their own `module`
(1=OTR, 2=Service) — preventing cross-module information disclosure between the two business units.

## Why it's blocked

`module` only exists as a column on `el_user` (the logged-in user's own attribute). None of the data
tables (`el_inbound_header`, `el_outbound_header`, `el_moving`, `el_loc`, `el_log`, master-data tables)
carry a `module` column or any other module-ownership marker. There is currently no way to know which
module a given inbound/outbound/moving/location row "belongs to" — the data model doesn't express this
relationship at all.

Confirmed via direct grep of `tables_schema.sql` during PVL — `module` appears exactly once, on `el_user`.

## What this actually needs

A data-model decision, not a security fix:
1. Decide whether module-ownership should be tracked per-record (new `module` column + migration on
   each relevant table) or derived some other way (e.g. via `created_by` → joined user's module).
2. If a new column: decide backfill strategy for existing rows (unknown/ambiguous module for historical
   data created before this concept existed).
3. Confirm with the user whether OTR/Service data really should be hard-separated, or whether the
   `module` field is informational only (matches the el_apk OTR/Service split confirmed in Phase 2 —
   two parallel business units that may legitimately need to see each other's warehouse data anyway).

## Recommended routing

Not Phase 3 (security audit) scope — raise as a Phase 2-style "Data Model & Auth Hardening" follow-up,
or a standalone feature/plan once the business answers point 3 above. Do not attempt a quick column-add
without that product decision; guessing wrong here risks either a false sense of isolation (if the
column is added but backfilled wrong) or unnecessary access restriction (if OTR/Service should actually
share visibility).

**Status:** Deferred, not started. Phase 3 proceeds without this item (Sections 1-6, 8, 9 unaffected).
