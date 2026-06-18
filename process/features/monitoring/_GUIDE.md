# monitoring

<!-- Part of WMS Lite -->

## Scope

Log aktivitas read-only untuk audit trail pergerakan barang/perubahan status di gudang.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/monitoring/` -- 1 file
- `apps/frontend/src/lib/api/monitoring.service.ts`
- `apps/backend/app/Http/Controllers/Api/MonitoringController.php` -- index saja, read-only
- `apps/backend/app/Models/ActivityLog.php`
- Tabel DB: `el_log`

## Related Context

- `process/context/database/all-database.md`

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/monitoring/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
