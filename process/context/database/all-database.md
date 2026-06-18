---
name: context:all-database
description: "MySQL legacy schema shared by Yii and Laravel during transition — the Database group entrypoint/router"
keywords: database, schema, mysql, el_, eloquent, migration, model, hawb, shipment, inbound, outbound, moving, timestamp, primary key, shadow table
related: [context:all-auth]
date: 18-06-26
---

# Database Context

This file is the canonical Database context entrypoint for wmslite.

Use it after `process/context/all-context.md` when the task needs schema, Eloquent model, or query-pattern awareness for the legacy MySQL database.

---

## Scope

This group covers:

- The MySQL legacy schema shared by the old Yii app and the new Laravel backend during the transition period (source: `tables_schema.sql` and `adminer.sql` at repo root).
- Legacy table naming convention: all tables prefixed `el_` (e.g. `el_user`, `el_inbound_header`, `el_outbound_header`, `el_moving`, `el_loc`, `el_recipient`, `el_product_category`, `el_apk`, `el_option`, `el_log`, `el_demo_movement` + `el_demo_movement_detail`).
- The shadow-table `_s` pattern: many tables have a near-identical sibling with an `_s` suffix (`el_apk`/`el_apk_s`, `el_loc`/`el_loc_s`, `el_inbound_header`/`el_inbound_header_s`, `el_inbound_details`/`el_inbound_details_s`, `el_outbound_header`/`el_outbound_header_s`, `el_outbound_details`/`el_outbound_details_s`, `el_moving`/`el_moving_s`) — likely a staging/scan-in-progress vs. final/confirmed-record split, related to `flag`/`scan_time` columns on detail tables. **This meaning is UNCONFIRMED — verify directly with the user before writing any migration or model that touches these tables.**
- Inconsistent timestamp conventions: older tables use `date_created`/`date_updated` (`el_inbound_header`, `el_outbound_header`, `el_moving`, `el_inbound_details`, etc.), newer tables use `created_at`/`updated_at` (`el_recipient`, `el_product_category`, `el_demo_movement`, `el_demo_movement_detail`). Eloquent defaults to `created_at`/`updated_at` — models pointing at old-style tables MUST override `const CREATED_AT = 'date_created';` and `const UPDATED_AT = 'date_updated';` (or set `public $timestamps = false`), otherwise Eloquent inserts/updates fail looking for nonexistent columns.
- `hawb` (House Air Waybill) as the natural key linking inbound/outbound/moving/lot tables — NOT an integer foreign key. Cross-module joins typically use `WHERE hawb = ?`, not `WHERE id = ?`.
- `el_inbound_details` and `el_outbound_details` lack a clear single-column primary key (only a plain `KEY` on `hawb`) — model these with custom/natural-key handling or `incrementing = false` rather than assuming a standard auto-increment PK.
- The `status` column on header tables (`el_inbound_header`, `el_outbound_header`, `el_demo_movement`) is a free-text VARCHAR, not a DB enum. Observed values: `'inprogress'` (default), `'Requested'`. Known status flow (from prior research, unconfirmed in code): `new -> inprogress -> Custom Process -> Warehouse in Transit -> successful` — verify against `ShipmentController`/`Inbound` model if exact transitions matter.
- `el_user` role/permission columns: `type` (INT 0-3), `admin` (INT 0/1 flag), `module` (INT; 1=OTR, 2=Service per prior research) — the basis for role-based access control system-wide.
- "Shipment" has no dedicated table — `ShipmentController.php` operates on `App\Models\Inbound` (table `el_inbound_header`), distinguished via the `from_shipment` flag and/or `status`. A `pushInbound` action promotes a shipment record into an official inbound record (see git history "Push Shipment→Inbound").
- The `vw_inbound` SQL VIEW, joining `el_inbound_header` + `el_inbound_details`.
- Migration strategy: Laravel does NOT create a fresh schema — it shares the same MySQL database as the legacy Yii app during the transition (explicit user decision).

It does not cover:

- Feature-specific migration plans — those belong in `process/features/{feature}/`.
- Database hosting/infrastructure strategy (none yet; still local).
- Test database setup — backend tests use SQLite in-memory, distinct from production MySQL; that belongs in the `tests/` group.

## Read When

Read this entrypoint when:

- writing or modifying an Eloquent model, migration, or query touching any `el_*` table
- debugging data that is out of sync between the Yii app and the Laravel app
- unsure about a legacy table's primary key, timestamp columns, or natural-key join pattern

## Quick Routing

- use `tables_schema.sql` (repo root) for the raw CREATE TABLE schema reference
- use `adminer.sql` (repo root) for a full data + schema dump
- use `apps/backend/app/Models/*.php` for existing Eloquent model conventions on legacy tables
- use `apps/backend/database/migrations/` for any new-style migrations layered on top of the legacy schema (if present)

## Source Paths

- `tables_schema.sql` (repo root)
- `adminer.sql` (repo root, full dump)
- `apps/backend/app/Models/*.php`
- `apps/backend/database/migrations/`

## Update Triggers

Update this group when:

- a new table is added to the schema
- the meaning of the `_s` shadow tables is confirmed by the user
- the shared-database strategy changes (e.g. Laravel eventually moves to its own schema)

## Canonical Notes

- use `process/context/database/all-database.md` as the entrypoint
- no deeper files exist yet in this group; promote subtopics here if this file exceeds ~800 lines or gains 3+ durable sub-docs
