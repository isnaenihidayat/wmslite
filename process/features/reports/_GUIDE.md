# reports

<!-- Part of WMS Lite -->

## Scope

Laporan agregat untuk shipment, inbound, outbound, dan inventory.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/reports/` -- 1 file
- `apps/backend/app/Http/Controllers/Api/ReportController.php` -- endpoint: shipment, inbound,
  outbound, inventory di bawah prefix `reports/`

## Related Context

- `process/context/database/all-database.md`

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/reports/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
