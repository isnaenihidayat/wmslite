# shipment

<!-- Part of WMS Lite -->

## Scope

Pencatatan pengiriman yang belum resmi jadi inbound -- tahap awal sebelum barang dikonfirmasi
masuk gudang. Punya action "push to inbound" untuk konversi.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/shipment/` -- 3 file termasuk `_components/columns.tsx`,
  `_components/shipment-form-sheet.tsx`
- `apps/frontend/src/lib/api/shipment.service.ts`
- `apps/backend/app/Http/Controllers/Api/ShipmentController.php` -- apiResource + endpoint
  `shipments/{id}/push-inbound`
- PENTING: Shipment TIDAK punya Model/tabel sendiri -- `ShipmentController` memakai
  `App\Models\Inbound` (tabel `el_inbound_header`), dibedakan lewat kolom `from_shipment`
  dan/atau `status`. Verifikasi detail logika filter ini langsung ke kode kalau perlu.

## Related Context

- `process/context/database/all-database.md`
- `process/features/inbound/` -- saling terhubung lewat push-inbound

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/shipment/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
