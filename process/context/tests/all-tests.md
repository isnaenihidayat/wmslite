---
name: context:all-tests
description: "Testing entrypoint — PHPUnit for Laravel backend, no test runner yet on the Next.js frontend"
keywords: test, testing, phpunit, vitest, jest, verification, debugging, coverage, lint, build
related: []
date: 18-06-26
---

# WMS Lite - All Tests

Last updated: 2026-06-18

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
- run via `php artisan test` (preferred, nicer output) or `./vendor/bin/phpunit` directly
- two suites are registered: `Unit` (`tests/Unit/`) and `Feature` (`tests/Feature/`)

### Frontend (apps/frontend, Next.js 16) has no test runner yet

- there is no `test` script in `package.json` and no test framework installed (no Vitest, Jest, Playwright, Cypress, etc.)
- the only available verification commands are `npm run lint` (ESLint) and `npm run build` (Next.js build, which also type-checks because TypeScript strict mode is on and `next build` fails on type errors)
- do not invent or assume a frontend test command — none exists. If a task requires frontend test execution, say so explicitly rather than guessing a command.

### Legacy Yii (repo root) is out of scope

- no test runner or test suite exists for the legacy Yii app
- it is not part of the current migration's test scope

## Default Verification Order

Unless the task clearly needs a different path:

1. run the narrowest existing automated test
2. use unit/integration tests before browser tests
3. use end-to-end tests only when the real UI is the thing being verified

In this repo today, step 1 only applies meaningfully to `apps/backend`, and only against the two boilerplate `ExampleTest.php` files — there is no real coverage to run narrowly against yet (see Known Gaps).

## Commands

| Package | Runner | Command | Notes |
|---|---|---|---|
| `apps/backend` | PHPUnit | `cd apps/backend && php artisan test` | Runs both `Unit` and `Feature` suites; preferred entrypoint |
| `apps/backend` | PHPUnit | `cd apps/backend && ./vendor/bin/phpunit` | Direct PHPUnit invocation, same suites |
| `apps/backend` | Laravel Pint (style, not tests) | `cd apps/backend && ./vendor/bin/pint` | Code style fixer, dev dependency (`laravel/pint`) |
| `apps/frontend` | none | — | No test runner installed; do not assume one |
| `apps/frontend` | ESLint (lint, not tests) | `cd apps/frontend && npm run lint` | Flat config, Next.js plugin |
| `apps/frontend` | Next.js build (type-check + build sanity) | `cd apps/frontend && npm run build` | `next build` fails on TypeScript errors since strict mode is active; closest thing to a verification gate today |

## Debugging Quick Reference

- **DB for backend tests:** PHPUnit runs against SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), configured in `apps/backend/phpunit.xml`. Production targets MySQL (shared with the legacy Yii app), so any new Laravel migration must remain valid on SQLite for tests to keep passing even though production runs MySQL — watch for MySQL-only column types/features that SQLite doesn't support.
- **Backend testing env overrides** (from `apps/backend/phpunit.xml`): `APP_ENV=testing`, `BCRYPT_ROUNDS=4` (faster hashing in tests), `CACHE_STORE=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `BROADCAST_CONNECTION=null`, `SESSION_DRIVER=array`. Telescope, Pulse, and Nightwatch are disabled in tests.
- **No frontend test command exists:** if a task or CI step references a frontend "test" step, it does not exist yet — confirm with the user before assuming `npm test` works.
- **Frontend type errors surface at build time:** since there's no separate `tsc --noEmit` script, `npm run build` is the way to catch TypeScript errors; lint alone (`npm run lint`) will not catch type errors.

## Known Gaps

- No real tests exist in the Laravel backend yet — `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` are untouched Laravel boilerplate. There is no Controller, Model, or route Feature test coverage for any WMS Lite functionality.
- The Next.js frontend has zero test infrastructure: no unit test runner, no e2e runner, nothing in `devDependencies`, no `test` script.
- This is a deliberate, documented next priority, not overlooked debt: the user plans to start a dedicated testing phase (backend and/or frontend test infrastructure + real test coverage) before adding further feature modules.
- The legacy Yii application (repo root) has no test coverage and is outside the current migration's test scope.
- There is no CI/CD pipeline that runs tests automatically — no `.github/workflows` or other pipeline configuration exists in the repo.
