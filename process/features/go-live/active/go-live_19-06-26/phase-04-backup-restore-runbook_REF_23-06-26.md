---
name: ref:go-live-phase-04-backup-restore-runbook
description: "MySQL backup/restore runbook for WMS Lite — generic procedure + tested proof-of-concept evidence"
date: 23-06-26
metadata:
  node_type: memory
  type: reference
  feature: go-live
  phase: phase-04
---

# MySQL Backup/Restore Runbook — WMS Lite

**Status:** Proof-of-concept tested (23-06-26) against `wmslite_test` only. Generic procedure —
applicable to any future VPS host once one is provisioned. **This is NOT yet an operational
guarantee for a real production host** — see "What This Does NOT Prove" below.

---

## Why This Runbook Exists

WMS Lite's MySQL database (`wmslite`) is shared live between the legacy Yii 1.x application and
the new Next.js/Laravel stack during the migration. Before any future deploy that touches this
database, a working backup/restore mechanism must exist and be understood. This phase (Phase 4,
Tier 1) only proves the *mechanism* works — it does not (and cannot yet) prove it against a real
production host, because no VPS exists yet (Tier 2, backlog).

---

## Backup Procedure

### Command

```bash
mysqldump --single-transaction --routines --triggers <database_name> > <backup_file>.sql
```

### Flag rationale

- `--single-transaction` — takes a consistent snapshot via a single InnoDB transaction (REPEATABLE
  READ) instead of locking tables. Critical for a **live, multi-consumer database**: the legacy
  Yii app and (eventually) the Laravel app will both be reading/writing `wmslite` concurrently.
  Without this flag, `mysqldump` falls back to table-locking by default, which would block writes
  from the live Yii app for the duration of the dump — unacceptable for a database actively used
  by warehouse staff. With `--single-transaction`, the dump is non-blocking and consistent as of
  the moment the transaction started, at the cost of not reflecting writes that commit *during*
  the dump (acceptable for a periodic backup; not a replacement for binlog-based point-in-time
  recovery).
- `--routines` — includes stored procedures/functions, if any exist or are added later.
- `--triggers` — includes triggers, if any exist or are added later. (Confirmed neither currently
  exist in `wmslite`/`wmslite_test`'s schema as of Phase 4, but the flags are forward-safe to
  include now so a future schema addition is captured automatically.)

### Example (production-shaped, NOT yet executed against the real `wmslite` database)

```bash
mysqldump --single-transaction --routines --triggers wmslite > /path/to/backups/wmslite_$(date +%Y%m%d_%H%M%S).sql
```

Replace `/path/to/backups/` with wherever backup storage is decided once a VPS exists (Tier 2,
backlog — see `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`).

---

## Restore Procedure

### Command

```bash
mysql <database_name> < <backup_file>.sql
```

### Notes

- The target database must already exist (`CREATE DATABASE <database_name>;`) before restoring —
  `mysqldump`'s default output does not include a `CREATE DATABASE` statement for the dumped
  database itself unless `--databases` is also passed naming it explicitly.
- Restoring into an existing non-empty database will attempt to re-create tables that already
  exist and will fail on the first `CREATE TABLE` collision unless the target database is empty,
  or the dump is loaded with `--force` (continues past errors — **not recommended for production
  restores**, since it can silently skip a failed statement). The safe pattern for a real disaster
  recovery is: drop/recreate the target database, then restore into the fresh empty database.

---

## Recommended Backup Schedule / Retention (Advisory Only)

No scheduler is being built this phase. Once a VPS exists (Tier 2), consider:

- **Frequency:** daily full dump during low-traffic hours (warehouse operations are unlikely to run
  24/7, but the exact low-traffic window should be confirmed with warehouse staff once real usage
  patterns are observed on the new stack).
- **Retention:** keep at least 7 daily backups plus 4 weekly backups, pruning older ones — a common
  starting point, not a requirement; tune to actual disk budget once a VPS is sized.
- **Storage location:** off the database host itself if possible (e.g. a separate disk, object
  storage, or pulled to a secondary machine) — a backup stored only on the same disk as the live
  database does not protect against disk failure. Exact mechanism is a Tier 2 decision, deferred
  to `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`.
- **Automation:** a cron job or systemd timer running the backup command above, once a host exists.
  Not built this phase — there is no host to run it on yet.

---

## Proof-of-Concept: Tested 23-06-26

Executed once against the local `wmslite_test` database (the dedicated Phase 1 test-suite
database — **never** the live `wmslite` database). All commands and exact output recorded below.

### Step 1 — Baseline: insert sample data

`wmslite_test` is normally reset to an empty schema between test runs (see
`apps/backend/app/Console/Commands/SetupTestDatabase.php`). To make the proof-of-concept
meaningful (an empty-table row-count match is a weak proof), 5 sample rows were inserted first:

```sql
USE wmslite_test;
INSERT INTO el_product_category (name, created_at, created_by) VALUES
  ('PoC Category A', NOW(), 1),
  ('PoC Category B', NOW(), 1),
  ('PoC Category C', NOW(), 1);
INSERT INTO el_loc (loc_name, loc_descr) VALUES
  ('PoC Location A', 'Backup proof-of-concept location A'),
  ('PoC Location B', 'Backup proof-of-concept location B');
```

Verified: `el_product_category` count = 3, `el_loc` count = 2.

### Step 2 — Backup

```bash
mysqldump --single-transaction --routines --triggers wmslite_test > /tmp/wmslite-backup-poc/wmslite_test_backup.sql
```

**Result:** exit code `0`. Output file: 34,960 bytes, 1,125 lines.

### Step 3 — Restore into an isolated temporary database (never overwriting `wmslite_test`)

A separate temporary database, `wmslite_test_restore_check`, was created specifically for this
verification so the restore target was never the live `wmslite_test` database that the Phase 1-3
test suite depends on:

```bash
mysql -e "CREATE DATABASE wmslite_test_restore_check;"
mysql wmslite_test_restore_check < /tmp/wmslite-backup-poc/wmslite_test_backup.sql
```

**Result:** create exit code `0`. Restore exit code `0`.

### Step 4 — Verification

**Table count:** source `wmslite_test` = 34 tables; restored `wmslite_test_restore_check` = 34
tables. Match.

**Row counts (all 34 tables, via `information_schema.tables`):** `diff` between source and
restored row-count listings returned **no differences** (exit code `0`).

**Checksums (`CHECKSUM TABLE`) on 5 representative tables:**

| Table | Source checksum | Restored checksum | Match |
|---|---|---|---|
| `el_product_category` | 541156043 | 541156043 | yes |
| `el_loc` | 3840795157 | 3840795157 | yes |
| `el_user` | 0 | 0 | yes |
| `migrations` | 2654110636 | 2654110636 | yes |
| `el_log` | 0 | 0 | yes |

**Direct content comparison** (`el_product_category.name`, ordered by `id`): source and restored
returned identical row sets in identical order (`PoC Category A`, `PoC Category B`,
`PoC Category C`).

**Conclusion:** the dump + restore mechanism reproduces the source database exactly — same table
count, same row counts across all tables, same checksums, same row content and ordering.

### Step 5 — Cleanup (performed immediately after verification)

```bash
mysql -e "DROP DATABASE IF EXISTS wmslite_test_restore_check;"
mysql wmslite_test -e "DELETE FROM el_product_category WHERE name IN ('PoC Category A', 'PoC Category B', 'PoC Category C');"
mysql wmslite_test -e "DELETE FROM el_loc WHERE loc_name IN ('PoC Location A', 'PoC Location B');"
```

**Result:** `wmslite_test_restore_check` dropped (confirmed absent from `SHOW DATABASES`).
`wmslite_test` PoC sample rows removed (`el_product_category` count back to 0, `el_loc` count back
to 0) — `wmslite_test` restored to its original empty-schema state used by the Phase 1-3 test
suite. The local dump file (`/tmp/wmslite-backup-poc/wmslite_test_backup.sql`) is in `/tmp`, not
part of the repository, and was not retained beyond this verification session.

---

## What This Does NOT Prove (Explicit Caveat — Read Before Relying on This for a Real Deploy)

This proof-of-concept demonstrates that the `mysqldump` + `mysql` restore **mechanism** works
correctly against a local, low-volume MySQL/MariaDB instance on a developer machine. It does
**NOT** prove:

- **Production-scale data volume handling.** `wmslite_test` is schema-only with a handful of test
  rows. The real `wmslite` database's actual size, dump duration, and restore duration at
  production scale are unknown and unverified.
- **Real production host disk/network/IO reliability.** No VPS exists yet. Disk space
  availability for storing dumps, network reliability for transferring backups off-host, and IO
  contention with a live, concurrently-used database under real warehouse-staff load are all
  unverified and explicitly out of scope until a real VPS is provisioned.
- **Backup storage location.** This phase did not decide or build where production backups would
  be stored (local disk, object storage, secondary host, etc.) — see the Recommended Schedule
  section above; this is advisory only.
- **Retention automation.** No scheduler (cron/systemd timer) was built or tested. The recommended
  schedule above is advisory only.
- **Restore against the live, shared `wmslite` database.** This proof-of-concept deliberately
  never touched `wmslite` — only `wmslite_test` and a disposable temporary database. Restoring
  against the real production database, under real concurrent load from the legacy Yii app, is
  untested and is a Tier 2 concern.

All of the above are tracked in
`process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md` and are blocked on VPS
provisioning, per the Phase 4 2-tier scope decision (RESEARCH + INNOVATE, 22/23-06-26).

---

## References

- Phase 4 plan: `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
- Test database setup command (the literal-string `wmslite_test` guard this runbook respects):
  `apps/backend/app/Console/Commands/SetupTestDatabase.php`
- Tier 2 backlog: `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`
