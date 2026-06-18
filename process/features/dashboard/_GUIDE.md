# dashboard

<!-- Part of WMS Lite -->

## Scope

Halaman overview/statistik ringkas untuk landing page setelah login (jumlah inbound/outbound
aktif, dll).

## Key Source Files

- `apps/frontend/src/app/(dashboard)/dashboard/` -- 1 file
- `apps/frontend/src/lib/api/dashboard.service.ts`
- `apps/backend/app/Http/Controllers/Api/DashboardController.php` -- endpoint `dashboard/stats`

## Related Context

Tidak ada yang spesifik -- agregat ringan dari modul lain.

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/dashboard/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
