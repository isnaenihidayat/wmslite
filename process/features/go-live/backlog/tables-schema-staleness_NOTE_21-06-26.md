---
name: note:tables-schema-staleness
description: "tables_schema.sql is stale relative to the live Yii codebase — re-dump recommended if needed in a future phase"
date: 21-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: `tables_schema.sql` Staleness

**Found during:** go-live Phase 2 (Data Model & Auth Hardening), Step A/C RESEARCH
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`,
`process/context/database/all-database.md`

---

## Problem

`tables_schema.sql` (and `adminer.sql`) at the repo root are stale relative to the live Yii
codebase. Confirmed during Phase 2 RESEARCH: the live Yii code references at least a `warehouse`
column and a set of Schenker integration tables/columns that are **not present** in either schema
file.

This was not blocking for Phase 2 (no Phase 2 work depended on the missing column/tables), but it
is a real gap that could mislead future work that relies on `tables_schema.sql` as a complete
schema reference.

## Recommendation

No action needed now — this is logged as an accepted limitation, not an open task, per
`process/context/database/all-database.md`. If a future phase (most likely Phase 4 Deployment
Readiness or Phase 5 Yii Cutover) needs accurate column-level detail beyond what
`tables_schema.sql` provides, re-dump the schema directly from the live production/shared MySQL
database (or `adminer.sql` regenerated fresh) rather than trusting the existing committed dump.

## Related

- `process/context/database/all-database.md` — documents this as a "Known limitation" with the
  same recommendation
- `process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md` — Phase 4/5
  scope, the most likely place this would resurface
