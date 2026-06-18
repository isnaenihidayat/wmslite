# moving

<!-- Part of WMS Lite -->

## Scope

Perpindahan lokasi barang di dalam gudang (stock transfer antar lokasi/rak).

## Key Source Files

- `apps/frontend/src/app/(dashboard)/moving/` -- 1 file
- `apps/frontend/src/lib/api/moving.service.ts`
- `apps/backend/app/Http/Controllers/Api/MovingController.php` -- index/store/destroy
- `apps/backend/app/Models/Moving.php`
- Tabel DB: `el_moving` (+`_s`)

## Related Context

- `process/context/database/all-database.md`

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/moving/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
