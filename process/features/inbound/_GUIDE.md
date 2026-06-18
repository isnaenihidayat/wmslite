# inbound

<!-- Part of WMS Lite -->

## Scope

Pencatatan barang masuk ke gudang (inbound shipment receiving), termasuk header HAWB, detail item
per koli/lot, dan tracking lokasi penyimpanan.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/inbound/` -- page + detail/components (4 files)
- `apps/frontend/src/lib/api/inbound.service.ts`
- `apps/backend/app/Http/Controllers/Api/InboundController.php` -- apiResource + endpoint detail `inbound/{id}/details`
- `apps/backend/app/Models/Inbound.php`, `apps/backend/app/Models/InboundDetail.php`
- Tabel DB: `el_inbound_header` (+`_s`), `el_inbound_details` (+`_s`, +`_backup`), `el_inbound_lots`

## Related Context

- `process/context/database/all-database.md` -- konvensi tabel hawb/timestamp
- `process/context/tests/all-tests.md` -- testing quick-start dan routing

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/inbound/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
