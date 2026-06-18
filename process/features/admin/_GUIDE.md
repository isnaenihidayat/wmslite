# admin

<!-- Part of WMS Lite -->

## Scope

Manajemen user staff internal (dashboard web), bukan akun APK Scanner -- termasuk CRUD user dan
reset password.

## Key Source Files

- `apps/frontend/src/app/(dashboard)/admin/` -- 1 file
- `apps/frontend/src/lib/api/user.service.ts`
- `apps/backend/app/Http/Controllers/Api/UserController.php` -- CRUD + reset-password, route
  prefix `admin/users`
- `apps/backend/app/Models/User.php`
- Tabel DB: `el_user` (kolom role: `type` 0-3, `admin` flag, `module` 1=OTR/2=Service)

## Related Context

- `process/context/auth/all-auth.md`
- `process/context/database/all-database.md`

## Current Status

Status: in-progress

Implementasi dasar sudah ada dari sprint sebelumnya, tapi belum ada test coverage. Testing
direncanakan sebagai fase berikutnya sebelum modul baru ditambahkan.

## Folder Contents

```
process/features/admin/
  active/       -- in-progress plans for this feature
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans
  reports/      -- feature-specific operational reports
  references/   -- feature-specific research and reference docs
```
