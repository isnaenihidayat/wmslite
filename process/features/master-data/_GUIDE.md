# master-data

<!-- Part of WMS Lite -->

## Scope

Data referensi master (lokasi gudang, kategori produk, penerima/recipient) dan manajemen akun
APK Scanner (aplikasi scanner mobile terpisah yang menggunakan akun sendiri, belum dibangun
aplikasinya -- baru account management-nya).

## Key Source Files

- `apps/frontend/src/app/(dashboard)/master/{apk,recipient,product-category,locations}/page.tsx`
- `apps/frontend/src/lib/api/master.service.ts`, `apps/frontend/src/lib/api/apk.service.ts`
- `apps/backend/app/Http/Controllers/Api/MasterController.php` -- locations/categories/recipients
- `apps/backend/app/Http/Controllers/Api/ApkController.php` -- CRUD + reset-password +
  force-logout, sprint terbaru
- `apps/backend/app/Models/Location.php`, `ProductCategory.php`, `Recipient.php`, `ApkUser.php`
- Tabel DB: `el_loc` (+`_s`), `el_product_category`, `el_recipient`, `el_apk` (+`_s`)

## Related Context

- `process/context/database/all-database.md`
- `process/context/auth/all-auth.md` -- akun APK Scanner adalah sistem auth terpisah dari
  NextAuth/Sanctum dashboard

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/master-data/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
