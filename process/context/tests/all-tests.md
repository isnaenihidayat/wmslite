---
name: context:all-tests
description: "Testing entrypoint — PHPUnit for Laravel backend against a dedicated MySQL wmslite_test database, Vitest for the Next.js frontend"
keywords: test, testing, phpunit, vitest, jest, verification, debugging, coverage, lint, build, wmslite_test
related: []
date: 20-06-26
---

# WMS Lite - All Tests

Last updated: 2026-06-20

Attach this file first when the task involves testing, verification, or test debugging.

This is the fast operator guide for the testing surface:

- which runner to use
- what command to start with
- how to quickly debug common failures
- which deeper file to read next

Do not load the whole `process/context/tests/` folder by default. Start here, then drill down.

---

## How This File Works

This is the `all-tests.md` entrypoint for the `tests/` context group. It follows the `all-*.md` routing convention:

1. Agents read `all-context.md` first and get routed here for testing tasks
2. This file gives quick decision rules and commands
3. For deeper details, agents follow the routing table below to specific docs

As the project grows, add deeper docs to this group (e.g., `e2e-tests.md`, `debugging-and-pitfalls.md`) and add routing entries below. This file stays the fast-start entrypoint.

---

## What This Covers

- test runner selection
- quick commands by package
- fast debugging procedures
- current testing gaps worth remembering

## Read This When

Use this file when you need to:

- run tests after implementation
- decide between test runners
- debug failing tests

## Quick Routing

(No deeper test docs yet. Add routing entries here as they are created.)

## Quick Decision Guide

WMS Lite is a monorepo with two independently-tested apps. There is no shared/unified test runner.

### Use PHPUnit (backend) when

- the change is in `apps/backend` (Laravel 13)
- the behavior is a Controller, Model, route, middleware, or any server-side logic
- run via `composer test` (preferred — see Commands below for what it does), or `php artisan test` directly if the `wmslite_test` schema is already loaded and fresh
- two suites are registered: `Unit` (`tests/Unit/`) and `Feature` (`tests/Feature/`), with ~162 tests passing as of Phase 1 (go-live program)

### Use Vitest (frontend) when

- the change is in `apps/frontend` (Next.js 16)
- the behavior is a page component, hook, service call, or the NextAuth `authorize()` callback
- run via `npm run test` (single run) or `npm run test:watch` (watch mode during development)
- smoke tests exist for every dashboard page plus the login page and the NextAuth callback, 18 tests passing as of Phase 1 (go-live program)
- frontend tests mock `*.service.ts` API calls — they never hit the real backend; do not add tests that perform real network calls

### Legacy Yii (repo root) is out of scope

- no test runner or test suite exists for the legacy Yii app
- it is not part of the current migration's test scope

## Default Verification Order

Unless the task clearly needs a different path:

1. run the narrowest existing automated test
2. use unit/integration tests before browser tests
3. use end-to-end tests only when the real UI is the thing being verified

Both `apps/backend` (`composer test`) and `apps/frontend` (`npm run test`) now have real, runnable suites — start with the narrowest matching test file before re-running the whole suite. End-to-end/browser-level testing is still out of scope (see Known Gaps).

## Commands

| Package | Runner | Command | Notes |
|---|---|---|---|
| `apps/backend` | PHPUnit | `cd apps/backend && composer test` | Preferred entrypoint. Runs `php artisan config:clear`, then `composer test:setup-db` (drops/recreates the dedicated `wmslite_test` MySQL database and reloads the `el_*` schema from the repo-root `tables_schema.sql` via the `php artisan test:setup-db` command), then `php artisan test`. Never touches the real `wmslite` database — the drop/recreate target is a hard-coded literal `wmslite_test` string, not a dynamic config value. |
| `apps/backend` | PHPUnit | `cd apps/backend && composer test:setup-db` | Runs only the schema-reload step (`php artisan test:setup-db --env=testing`) without running the suite — useful when you want to reset `wmslite_test` without re-running tests yet. |
| `apps/backend` | PHPUnit | `cd apps/backend && php artisan test` | Runs both `Unit` and `Feature` suites directly against whatever schema is currently loaded in `wmslite_test` — use this only after `composer test:setup-db` has already run at least once, otherwise the schema may be stale or missing. |
| `apps/backend` | Laravel Pint (style, not tests) | `cd apps/backend && ./vendor/bin/pint` | Code style fixer, dev dependency (`laravel/pint`) |
| `apps/frontend` | Vitest | `cd apps/frontend && npm run test` | Single run (`vitest run`), exits non-zero on any failure. Preferred for CI-style verification. |
| `apps/frontend` | Vitest | `cd apps/frontend && npm run test:watch` | Watch mode (`vitest`), re-runs affected tests on file change — use during active test/feature development, not for one-shot verification. |
| `apps/frontend` | ESLint (lint, not tests) | `cd apps/frontend && npm run lint` | Flat config, Next.js plugin |
| `apps/frontend` | Next.js build (type-check + build sanity) | `cd apps/frontend && npm run build` | `next build` fails on TypeScript errors since strict mode is active; closest thing to a build-level verification gate, complements but does not replace Vitest |

## Debugging Quick Reference

- **DB for backend tests:** PHPUnit runs against a dedicated MySQL database named `wmslite_test`, configured in `apps/backend/phpunit.xml` (`DB_CONNECTION=mysql`, `DB_DATABASE=wmslite_test`, same host/port/credentials as the real local `wmslite` connection except the database name). SQLite was explicitly evaluated and rejected for this project (see decision rationale below) — there is no SQLite test path anywhere in this repo.
  - **Why MySQL `wmslite_test` and not SQLite `:memory:`:** decided in go-live Phase 1 (Step A1). `tables_schema.sql` (repo root) is the single source of truth for the `el_*` schema; loading it into a real MySQL database guarantees 100% schema parity with production. Several controllers (e.g. `ApkController`) use MySQL-dialect-specific raw SQL (`CASE WHEN`, union queries) that SQLite cannot execute faithfully — testing against SQLite would silently test different behavior than production. Testcontainers was also considered and rejected as overkill for ~25 tables with no CI requirement driving the decision (YAGNI). A static local `wmslite_test` MySQL database, loaded fresh before each suite run, was judged sufficient and simpler.
  - **Setup is fully automated, never manual:** `composer test` (or `composer test:setup-db` alone) drops/recreates `wmslite_test` and reloads `tables_schema.sql` via the `php artisan test:setup-db` artisan command (`apps/backend/app/Console/Commands/SetupTestDatabase.php`) — a native PDO/`DB::unprepared`-style implementation, not a shell `mysql` CLI call. The command refuses to run if the configured `DB_DATABASE` is anything other than the literal string `wmslite_test`, to guard against ever targeting the real `wmslite` database. There is no manual "load the schema by hand first" step — do not document or invent one.
  - `apps/backend/.env.testing` holds the test-DB credentials (mirrored from the real local backend env, database name swapped to `wmslite_test`) and is covered by `.gitignore` — never commit it with real credentials inline.
  - `wmslite` (production-like, used by the legacy Yii app) and `wmslite_test` are separate schemas on the same local MySQL instance with independent connection pools — running both side by side locally is low-risk for solo dev use; see Known Gaps for the re-evaluation note for shared/CI environments.
- **Backend testing env overrides** (from `apps/backend/phpunit.xml`): `APP_ENV=testing`, `BCRYPT_ROUNDS=4` (faster hashing in tests), `CACHE_STORE=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `BROADCAST_CONNECTION=null`, `SESSION_DRIVER=array`. Telescope, Pulse, and Nightwatch are disabled in tests.
- **Frontend tests mock the backend, they don't call it:** Vitest smoke tests mock `*.service.ts` API calls via Testing Library render+mock patterns — they never hit the real Laravel backend. Real end-to-end integration (real backend + real browser) is still out of scope (see Known Gaps).
- **Frontend type errors surface at build time:** since there's no separate `tsc --noEmit` script, `npm run build` is the way to catch TypeScript errors; lint alone (`npm run lint`) will not catch type errors. Vitest (`npm run test`) catches runtime/render-level regressions that lint and build do not.

## Known Gaps

- **CI/CD pipeline:** there is no CI/CD pipeline that runs tests automatically — no `.github/workflows` or other pipeline configuration exists in the repo. This is scheduled for go-live program Phase 4 (Deployment Readiness), not this phase.
- **End-to-end/browser-level testing:** both suites (backend Feature/Unit, frontend Vitest smoke tests) are unit/integration-tier. No Playwright/Cypress-style real-browser end-to-end suite exists yet. Out of scope for the current go-live test-infrastructure phase.
- **Shipment push-to-inbound full dialog interaction:** the frontend smoke test for the Shipment page only asserts the "push to inbound" action button is present, not the full open-dialog/fill-form/submit/verify-status-flip interaction. Deferred until the go-live Phase 2 shipment-status-flow semantics are confirmed (testing the full interaction now risks asserting against semantics Phase 2 may change).
- **Concurrent local MySQL resource contention:** `wmslite` and `wmslite_test` running side by side on the same local MySQL instance is untested under real connection/lock contention. Accepted as low-risk for solo local dev (separate schemas, independent connection pools); re-evaluate if Phase 4 introduces shared-hosting/CI constraints.
- **Legacy Yii application (repo root):** has no test coverage and is outside the current migration's test scope.
- **Backend code-quality backlog (found during go-live Phase 1, not yet fixed — test coverage was added, the underlying behavior was not changed):**
  - `InboundController::index()` has a potential N+1 query pattern. Backlog candidate for Phase 2+.
  - `ReportController::buildReport()` contains a dead ternary (`$query->getModel()->getTable() === 'outbound_header' ? 'date_created' : 'date_created'`) — both branches are identical, so the condition has no effect. Backlog candidate for Phase 2+.
  - `UserController`'s admin check is ad hoc (not a formal Laravel Policy/Gate). Formalizing this into a Policy/Gate is explicitly Phase 2 scope.
  - `ShipmentController::pushInbound()` always returns HTTP 500 in the conflict case instead of the intended 409 — the unique constraint on `hawb` is violated before the controller's own conflict-check branch can run, so the dead 409-conflict branch never executes. Found during go-live Phase 1 Step B (`ShipmentControllerTest`). Backlog candidate for Phase 2+.
  - `InboundController::details()` always returns HTTP 500 — the query calls `orderBy('id')` but the `el_inbound_details` table has no `id` column. Found during go-live Phase 1 Step B (`InboundControllerTest`). Backlog candidate for Phase 2+.
