# outbound

<!-- Part of WMS Lite -->

## Scope

Pencatatan barang keluar dari gudang (outbound shipment dispatch), termasuk header
tujuan/destination, detail item, dan cetak picking list.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/outbound/` -- page + detail/components (4 files)
- `apps/frontend/src/lib/api/outbound.service.ts`
- `apps/backend/app/Http/Controllers/Api/OutboundController.php` -- apiResource
- `apps/backend/app/Models/Outbound.php`, `apps/backend/app/Models/OutboundDetail.php`
- Tabel DB: `el_outbound_header` (+`_s`), `el_outbound_details` (+`_s`)
- Sprint 5 menambahkan "Outbound Detail Items + Print Picking List"

## Related Context

- `process/context/database/all-database.md`

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/outbound/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
