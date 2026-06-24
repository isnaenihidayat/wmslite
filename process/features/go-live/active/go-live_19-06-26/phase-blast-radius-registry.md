# go-live — Phase Blast Radius Registry

One registry for the whole program. Each phase's blast radius must stay disjoint from concurrently
active phases — phases in this program run sequentially (each depends on the previous), so overlap
across phases is expected and fine; overlap WITHIN a phase's own concurrent work is what this
registry guards against.

| Phase | Primary blast radius | Shared/overlapping paths with other phases |
|---|---|---|
| 01 — Test Infrastructure Foundation | `apps/backend/tests/**`, `apps/backend/database/factories/**` (incl. `ActivityLogFactory.php` and `InboundDetailFactory.php`, not named in the original plan text — needed for `MonitoringControllerTest`/`InboundControllerTest`), `apps/backend/app/Console/Commands/SetupTestDatabase.php` (new artisan command, the Step A2a automated test-DB setup entrypoint — not named verbatim in the original plan), `apps/backend/phpunit.xml`, `apps/backend/composer.json`, `apps/backend/.gitignore`, `apps/frontend/vitest.config.ts`, `apps/frontend/src/test/**`, `apps/frontend/src/**/*.test.ts(x)`, `apps/frontend/package.json`, `process/context/tests/all-tests.md`, `process/context/all-context.md` | None at creation time — first phase |
| 02 — Data Model & Auth Hardening | `apps/backend/app/Policies/**` (new: `ApkUserPolicy.php`, `UserPolicy.php`, `MovingPolicy.php`), `apps/backend/app/Http/Controllers/Api/{Apk,User,Moving,Shipment,Inbound}Controller.php`, `apps/backend/app/Http/Controllers/Controller.php` (deviation — `AuthorizesRequests` trait added, required infra for E1/E2, not named in original plan), `apps/backend/app/Providers/AppServiceProvider.php` (Policy registration), `apps/backend/tests/Feature/{Apk,User,Moving,Shipment,Inbound}ControllerTest.php`, `apps/backend/tests/Unit/ShipmentControllerPushInboundTest.php`, `apps/frontend/src/app/(dashboard)/master/apk/page.tsx` + `page.test.tsx`, `apps/frontend/src/app/(dashboard)/shipment/page.tsx`, `process/context/all-context.md`, `process/context/database/all-database.md`, `process/context/auth/all-auth.md` | Controllers also touched by Phase 1 (tests) and Phase 3 (security fixes) — sequential, not concurrent |
| 03 — Security & Validation Audit | `apps/backend/app/Http/Middleware/**`, `apps/backend/config/sanctum.php`, `apps/backend/app/Http/Requests/**`, `apps/backend/routes/api.php` | Controllers/routes also touched by Phase 2 — sequential, not concurrent |
| 04 — Deployment Readiness | New CI config, `.env.example` files, new runbook docs | No app code overlap |
| 05 — Legacy Yii Cutover Plan | `protected/controllers/OtrController.php` (`actionMonitoring()`, line 565 — actual touched file, confirmed; redirect + revert + re-apply, 3 commits), `API_INVENTORY.md` (header annotation only), `process/features/go-live/active/go-live_19-06-26/phase-05-remaining-modules-cutover-plan_REF_24-06-26.md` (new), 3 new backlog NOTE files under `process/features/go-live/backlog/` | No app code overlap with Phases 1-4; touches legacy Yii surface only. Status: DONE — Tier 1 VERIFIED 24-06-26. |

Update this table whenever a phase's actual touched-files list (recorded in its `_REPORT_`) differs
from what was planned here.
