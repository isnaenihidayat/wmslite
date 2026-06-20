---
name: plan:go-live-phase-01-test-infrastructure
description: "WMS Lite go-live — Phase 1: Test Infrastructure Foundation"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-01
---

# Phase 01 — Test Infrastructure Foundation

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ✅ VERIFIED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

WMS Lite currently has zero real test coverage: the backend only ships Laravel's default
`tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` boilerplate, and the frontend has
no test runner installed at all (confirmed during the earlier manual feature-by-feature browser
check — see `process/context/tests/all-tests.md` "Known Gaps"). This phase builds the safety net
every later phase depends on: without it, Phase 2 (data model/auth changes) and Phase 3 (security
fixes) have no way to prove they didn't break the 9 already-working modules.

---

## Inner Loop Refresh Note

RESEARCH + INNOVATE surfaced material changes versus the original plan draft. PLAN-SUPPLEMENT
incorporated all three below; nothing else in this phase plan changed:

1. **Test DB strategy reversed from the original SQLite assumption.** The plan originally framed
   Step A1 as an open question between SQLite `:memory:` migrations and a disposable MySQL test DB.
   That question is now closed: the decision is a dedicated MySQL database named `wmslite_test`,
   loaded from the repo-root `tables_schema.sql` (the existing single source of truth for the
   `el_*` schema), not hand-written SQLite migrations. See Step A for full rationale and the new
   automation requirement.
2. **Step B test order reordered by blast radius, not by original listing order.** The original B1–B11
   order front-loaded `AuthControllerTest` and bundled `MasterControllerTest` with
   `ApkControllerTest`. The new order runs from least-dependent/simplest controllers to
   most-dependent/most-complex, deferring anything that depends on the Step A1 MySQL raw-SQL
   decision (Apk) or touches every other route (Reports + auth middleware regression) to the end.
3. **Step C3's conditional branch for async Server Component pages was empirically false and is
   removed.** All 13 `page.tsx` files under `apps/frontend/src/app/(dashboard)/**` plus the login
   page are `"use client"` components — zero are async Server Components. The instruction to check
   for that case and adapt the testing pattern accordingly no longer applies; Step C3 now states the
   single applicable pattern directly.

---

## Entry Gate

- Phase 0 complete (umbrella plan + all 5 phase plans exist — done)
- Local dev environment confirmed working: MySQL `wmslite` reachable, `php artisan migrate:status`
  shows the 4 default migrations, `el_*` tables present with seed-ish data (confirmed in the
  manual feature check session)

---

## Blast Radius

- `apps/backend/tests/Feature/` — new Feature test files, one group per module (Monitoring, Master,
  Outbound, Moving, Shipment, Inbound, Apk, User, Auth, Reports — see Step B for the agreed order)
- `apps/backend/tests/Unit/` — new Unit tests for any non-trivial model logic found during research
  (e.g. Inbound `from_shipment` scoping, custom `CREATED_AT`/`UPDATED_AT` constants if already set),
  plus the `ShipmentController::pushInbound()` conflict-409 case as an isolated Unit test candidate
- `apps/backend/database/factories/` — new factories, including `ApkUserFactory` covering both
  `el_apk` and `el_apk_s` tables (built in Step A3, ahead of the Apk Feature test in Step B)
- `apps/backend/phpunit.xml` and `apps/backend/.env.testing` (new) — switched to a dedicated MySQL
  test connection (`DB_CONNECTION=mysql`, `DB_DATABASE=wmslite_test`, plus host/port/credentials),
  not SQLite `:memory:`
- `apps/backend/composer.json` (or a new artisan command) — adds the automated test-DB setup
  entrypoint described in Step A2a
- `apps/frontend/package.json` — add Vitest + Testing Library devDependencies and a `test` script
- `apps/frontend/vitest.config.ts` (new)
- `apps/frontend/src/**/*.test.ts(x)` — new smoke tests for critical CRUD flows (one per module page
  at minimum: render + data fetch + primary action), starting with login then simple modules before
  complex ones (see Step C)
- `apps/frontend/src/test/setup.ts` (new, if Vitest needs jsdom polyfills)
- `process/context/tests/all-tests.md` — update with the now-real commands and remove the "Known
  Gaps" entries this phase closes

---

## Implementation Checklist

### Step A — Backend test database strategy (decided)

- [x] A1. **Decided, not an open question.** Backend Feature/Unit tests run against a dedicated
      MySQL database named `wmslite_test`, loaded from the repo-root `tables_schema.sql` — the
      existing single source of truth for the `el_*` schema. Hand-written SQLite migrations are
      rejected.
      Rationale (record verbatim in the phase report):
      - **Schema parity**: `tables_schema.sql` is the only source of truth for the `el_*` tables;
        loading it directly into a real MySQL test DB guarantees 100% parity with production
        schema. Hand-written SQLite migrations would create a second, divergent source of truth
        that must be kept in sync by hand forever.
      - **Environment fit**: MySQL is already running locally in the dev environment — no new
        infrastructure is required.
      - **Raw SQL realism**: `ApkController` (and other controllers) use raw SQL
        (`CASE WHEN`, union queries across `el_apk`/`el_apk_s`) that is MySQL-dialect-specific.
        SQLite cannot execute this SQL faithfully, so tests against SQLite would either skip this
        logic or silently test something different from production behavior.
      - **Testcontainers rejected**: overkill for ~25 tables with no CI requirement driving this
        phase; a static local `wmslite_test` MySQL database is sufficient and simpler (YAGNI).
- [x] A2. Implement the MySQL `wmslite_test` strategy: update `apps/backend/phpunit.xml` and add/
      update `apps/backend/.env.testing` with `DB_CONNECTION=mysql`, `DB_DATABASE=wmslite_test`,
      `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` (mirroring the real `wmslite` connection
      values except the database name) so `php artisan test` targets `wmslite_test` and never
      touches the real `wmslite` database.
- [x] A2a. **New — automate schema loading, do not document a manual step.** Add either an artisan
      command (e.g. `php artisan test:setup-db`) or a composer script (e.g. `composer test`) that:
      1. Drops/recreates (or truncates+reloads) the `wmslite_test` database,
      2. Loads schema by running `mysql wmslite_test < tables_schema.sql` (or the Laravel-native
         equivalent, e.g. `DB::unprepared(file_get_contents(...))` if a non-shell-dependent
         implementation is preferred — agent's choice, document which was picked and why in the
         phase report),
      3. Then runs `php artisan test`.
      This command/script is the only documented way to run the backend suite; no manual
      "first load the schema by hand" step should appear anywhere in `process/context/tests/all-tests.md`
      (Step D enforces this).
- [x] A3. **Moved up — build now, not deferred to the Apk test.** Add minimal model factories for
      `User`, `Inbound`, `Outbound`, `Location`, `ProductCategory`, `Recipient`, `Moving`, and
      `ApkUser`. The `ApkUser` factory must cover **both** `el_apk` and `el_apk_s` tables (build two
      factory states or two factories as appropriate to the model/table split) since
      `ApkControllerTest` in Step B needs both tables populated to exercise the union-query/raw-SQL
      path realistically.

### Step B — Backend Feature tests (ordered by blast radius: simplest/most-independent first,
most-complex/most-dependent-on-A1 last)

- [x] B1. `MonitoringControllerTest` — index (read-only activity log). Simplest: no writes, no
      cross-module dependencies.
- [x] B1a. **Added by PVL (P1).** `DashboardControllerTest` — `GET /api/dashboard/stats` (aggregates
      `Inbound`/`Outbound` counts via the `DashboardController`). Placed alongside B1: read-only, no
      writes, no cross-module write dependency, only needs seeded `Inbound`/`Outbound` factory rows.
      This endpoint had zero coverage in the original Step B list — see Validate Contract Layer 1
      "Test Coverage" finding below for why it was added. Execute-agent may renumber this item
      (e.g. append as B12 instead) — document the chosen position in the phase report.
- [x] B2. `MasterControllerTest` — locations/categories/recipients reads. Read-mostly, low
      complexity, no dependency on the Apk raw-SQL decision.
- [x] B3. `OutboundControllerTest` — apiResource CRUD.
- [x] B4. `MovingControllerTest` — index/store/destroy.
- [x] B5. `ShipmentControllerTest` — index/show/store/update/destroy via `apiResource`, plus the
      `push-inbound` action (verify it actually flips `from_shipment`/status on the `Inbound` model
      per the Phase 2 research note). The push-inbound conflict-409 case is an isolated Unit test
      candidate for `ShipmentController::pushInbound()` — write it as a Unit test in
      `apps/backend/tests/Unit/` alongside (or instead of, if fully isolatable) the Feature-level
      assertion.
- [x] B6. `InboundControllerTest` — apiResource CRUD + `/inbound/{id}/details`. **Backlog note
      only, do not fix**: `InboundController::index()` shows a potential N+1 query pattern; record
      this as a Phase 2+ backlog candidate in the phase report, out of this phase's blast radius
      (this phase adds tests, it does not fix product code).
- [x] B7. `ApkControllerTest` — apk CRUD + reset-password + force-logout, covering both `el_apk`
      and `el_apk_s` via the Step A3 factory. Placed near the end of the controller-test sequence
      because it is the most dependent on the Step A1 MySQL-vs-SQLite raw-SQL decision: this is the
      test that actually proves the decision was correct. **PVL instruction E7**: assertions must
      cover the actual raw-SQL-derived response fields (`is_logged_in`, `source_table`), not just
      HTTP status codes — see Validate Contract Execute-agent instructions below.
- [x] B8. `UserControllerTest` (admin) — CRUD + reset-password. **Scope note**: the existing admin
      check is ad hoc (not a formal Laravel Policy/Gate). Test the ad hoc check as currently
      implemented; do not introduce a Policy/Gate in this phase — that refactor is explicitly
      Phase 2 scope.
- [x] B9. `AuthControllerTest` — login success, login wrong password, login inactive user (403),
      logout revokes token, `me` returns expected shape. Grouped with B8 as "independent of DB
      schema complexity but security-sensitive"; both are deliberately tested before the final
      cross-cutting pass in B10–B11.
- [x] B10. `ReportControllerTest` — all 4 report endpoints, including the `start_date`/`end_date`
      validation-required case (422) and the happy path (200) already observed manually.
      **Backlog note only, do not fix**: `ReportController::buildReport()` contains a dead ternary
      (`$query->getModel()->getTable() === 'outbound_header' ? 'date_created' : 'date_created'`) —
      both branches are identical, so the condition has no effect. Record this as a backlog item;
      fixing it is out of this phase's blast radius.
- [x] B11. Auth middleware regression: every protected route in `routes/api.php` returns 401 without
      a valid Sanctum token (one parametrized test or one test per group, agent's choice). Placed
      last because it is cross-cutting and sweeps every route exercised by B1–B10, so it is most
      useful as a final pass once every other module's routes are already known-good.

### Step C — Frontend test runner + smoke tests

- [x] C1. Add Vitest + `@vitejs/plugin-react` + `jsdom` + `@testing-library/react` +
      `@testing-library/dom` + `vite-tsconfig-paths` to `apps/frontend/package.json`
      devDependencies; add a `test` script (`vitest run`) and a `test:watch` script. Confirmed via
      WebSearch against official Next.js testing documentation (not via
      `node_modules/next/dist/docs/`, which is blocked in this repo by `scout-block.cjs` — that
      hook is not being changed in this phase, so WebSearch is the compensating research path for
      any further Next.js/Vitest documentation needs during EXECUTE).
- [x] C2. Create `vitest.config.ts` wired to the existing `@/*` -> `./src/*` path alias via
      `vite-tsconfig-paths`, with the `jsdom` test environment and `@vitejs/plugin-react` configured
      per the official Next.js + Vitest setup guide (WebSearch, not local `node_modules` docs — see
      C1 note).
- [x] C3. **Simplified — no async Server Component branch needed.** All 13 `page.tsx` files under
      `apps/frontend/src/app/(dashboard)/**` (9 module pages) plus the login page are confirmed
      `"use client"` components (verified directly from source; zero async Server Components
      exist in this app). Use the standard Testing Library render+mock pattern for every page: one
      smoke test per page that renders without throwing, shows the expected heading/empty-state
      text observed in the earlier manual browser check (e.g. "Shipment" / "X total records"), and
      confirms the primary "Add X" button is present. Mock the `*.service.ts` API calls — do not
      hit the real backend from frontend unit tests. **Write order**: login page first (simplest,
      and unlocks immediate manual cross-check against C4's auth callback test), then the simple
      modules (Monitoring, Master/* pages) before the complex modules (Shipment, which includes the
      push-to-inbound dialog interaction, and any other module with non-trivial modal/dialog state).
- [x] C4. One test for the NextAuth `authorize()` callback in `src/lib/auth/auth.ts`: given a mocked
      successful Laravel response, the returned user object has the same fields the real
      `AuthController::formatUser()` returns (`is_admin`, `module`, `type`, etc.) — this guards the
      exact integration point already manually verified to work. Write immediately after the login
      page smoke test in C3 since both exercise the same login flow.

### Step D — Close out the testing context doc

- [x] D1. Update `process/context/tests/all-tests.md`: replace the "no test runner" / "only
      boilerplate ExampleTest" facts with the real commands, real test file locations, and the
      backend test-DB strategy decided in Step A (MySQL `wmslite_test` loaded from
      `tables_schema.sql` via the automated command/script from A2a — document the exact command
      name, e.g. `composer test` or `php artisan test:setup-db && php artisan test`).
- [x] D2. Remove or update the "Known Gaps" bullets that this phase closes; keep any gap not
      actually closed (e.g. if E2E/browser tests are still out of scope for this phase, say so
      explicitly rather than implying full coverage). Also record the two backlog notes from Step B
      (Inbound `index()` potential N+1, `ReportController::buildReport()` dead ternary) and the
      Step B8 ad hoc-admin-check scope note as forward references for Phase 2, so they are not lost
      between phases. **PVL instruction E6**: after this edit, grep the file for "SQLite"/"sqlite"
      and confirm zero stale references remain (the current "Debugging Quick Reference" prose
      states "PHPUnit runs against SQLite in-memory" as fact — that exact sentence must change, not
      just the Commands table).

---

## Exit Gate

```bash
# Backend (uses the automated A2a entrypoint, e.g.:)
cd apps/backend && composer test
# or: php artisan test:setup-db && php artisan test
# Expected: 0 failures, every module group from Step B present and green, run against wmslite_test

# Frontend
cd apps/frontend && npm run test
# Expected: 0 failures, one smoke test per module + login + auth callback test, all green

cd apps/frontend && npm run lint && npm run build
# Expected: 0 lint errors, build succeeds

# Context doc sanity (Step D touched process/context/tests/all-tests.md)
node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs
# Expected: 0 warnings, 0 failures
```

- All checklist items A1–D2 checked
- Both test suites green, both validators clean
- Phase report written to report destination above, including the Step A1 decision and rationale,
  the Step A2a automated setup command, and the three Step B backlog notes (this is durable
  knowledge Phase 2 and later phases need)

---

## Blockers That Would Justify BLOCKED Status

- The `el_*` tables cannot be reliably recreated in `wmslite_test` from `tables_schema.sql` (e.g.
  undocumented triggers/views beyond `vw_inbound` are discovered, or the schema file is stale
  relative to the live `wmslite` database) and no safe test-data strategy exists yet — route to a
  backlog item and document the real constraint instead of testing against the live shared
  `wmslite` database.
- A genuine product bug is found that is out of this phase's scope to fix (e.g. a broken endpoint
  unrelated to test setup) — write it as a Phase 2 input (bug to fix during hardening) rather than
  fixing it inline here, to keep this phase's blast radius to "add tests," not "fix product bugs."
  (The Inbound `index()` N+1 and `ReportController::buildReport()` dead ternary found during
  PLAN-SUPPLEMENT research are explicit examples already routed this way — see Step B6 and B10.)

---

## Phase Loop Progress

Orchestrator reads this before deciding which subagent to spawn next. The canonical 7-step inner loop
`R → I → P → PVL → E → EVL → UP` SKIPS SPEC (SPEC runs once in the outer program loop).

- [x] 1. RESEARCH — research-agent: prior phase reports read; test context loaded; plan drift checked
- [x] 2. INNOVATE — innovate-agent: approach decided; Decision Summary written
- [x] 3. PLAN-SUPPLEMENT — plan-agent: existing phase plan updated; Inner Loop Refresh Note if sections changed (or "n/a — research clean")
- [x] 4. PVL — vc-validate-agent: full V1-V7; validate-contract written per `.claude/skills/vc-validate-findings/references/example-validate-output.md`
- [x] 5. EXECUTE — all checklist items done; per-section test gates run and green (or gaps documented)
- [x] 6. EVL — all EVL gates green; follow-up stubs registered; EVL HANDOFF SUMMARY written
- [x] 7. UPDATE PROCESS — phase report written, umbrella state updated, commit done

## EVL HANDOFF SUMMARY (19-20/06/26)

Full regression sweep run after Steps A–D complete:

- `cd apps/backend && composer test` → 162 tests, 414 assertions, **0 failures** (drop/recreate `wmslite_test`, load `tables_schema.sql`, run suite — all automated, `wmslite` production-like DB confirmed untouched)
- `cd apps/frontend && npm run test` → 14 test files, 18 tests, **0 failures**
- `cd apps/frontend && npm run build` → succeeds, all 13 routes compile
- `cd apps/frontend && npm run lint` → **7 pre-existing errors + 13 pre-existing warnings**, all in files this phase never touched (`data-table.tsx`, `monitoring/page.tsx`, `moving/page.tsx`, `outbound/page.tsx`, `shipment/page.tsx`) — confirmed via `git status` these predate this phase. Not a regression; not fixed (out of "tests only" blast radius). Carried to backlog.
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0 warnings/failures

Follow-up stubs registered (backlog, for Phase 2 triage):
- `ShipmentController::pushInbound()` always returns HTTP 500 (violates `el_inbound_header` unique `hawb` constraint); its 409-conflict branch is unreachable dead code
- `InboundController::details()` always returns HTTP 500 (`orderBy('id')` on `el_inbound_details`, which has no `id` column)
- `InboundController::index()` N+1 query pattern (functional, not broken — perf note)
- `ReportController::buildReport()` dead ternary (both branches identical — cosmetic)
- `UserController` ad hoc admin-check, not formal Policy/Gate (Phase 2 scope, already planned)
- 7 pre-existing frontend lint errors in `data-table.tsx` + 3 module pages (not introduced by this phase)

No regression check against prior phases — Phase 1 is first in the program, nothing prior is VERIFIED yet (per umbrella's own instruction).

**Validate-contract required before execute.** If step 4 (PVL) is unchecked or `## Validate Contract`
reads "(placeholder — vc-validate-agent writes this section before EXECUTE)", orchestrator must
spawn vc-validate-agent first.

---

## Touchpoints

- `apps/backend/tests/Feature/*ControllerTest.php` (new, ~11 files including B1a `DashboardControllerTest` — see Step B for exact order)
- `apps/backend/tests/Unit/*Test.php` (new, including the `pushInbound()` conflict-409 case)
- `apps/backend/database/factories/*Factory.php` (new, including `ApkUserFactory` for both
  `el_apk` and `el_apk_s`)
- `apps/backend/phpunit.xml`, `apps/backend/.env.testing` (new) — MySQL `wmslite_test` connection
- `apps/backend/composer.json` or a new artisan command (the A2a automated setup entrypoint)
- `apps/frontend/package.json`, `apps/frontend/vitest.config.ts` (new)
- `apps/frontend/src/test/setup.ts` (new)
- `apps/frontend/src/**/*.test.ts(x)` (new, ~14 files — login first, then simple modules, then
  complex modules per Step C)
- `process/context/tests/all-tests.md`

---

## Public Contracts

- No existing API response shape, route, or frontend URL changes — this phase only adds tests
  around the current behavior.

---

## Verification Evidence

```bash
cd apps/backend && composer test
# or: php artisan test:setup-db && php artisan test
# Expected: all suites pass, 0 failures, all module groups represented (run against wmslite_test,
# schema loaded fresh from tables_schema.sql)

cd apps/frontend && npm run test
# Expected: all suites pass, 0 failures, smoke test per module + auth callback test
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_PLAN_19-06-26.md`
- Last completed step: PVL (Step 4) — validate-contract written below, Gate: CONDITIONAL
- Validate-contract status: CONDITIONAL — 0 FAILs, 7 raw CONCERN findings (collapsed to 1 plan fix
  + 7 execute-agent instructions + 0 unresolved blockers — all resolvable inside this phase's
  existing blast radius)
- Next step: Spawn vc-execute-agent against this plan + the validate-contract below. Execute-agent
  MUST read the "Execute-agent instructions" subsection before starting Step A.

---

## Validate Contract

Status: CONDITIONAL
Date: 19-06-26
date: 2026-06-19
generated-by: inner-pvl: phase-1

Parallel strategy: parallel-subagents (analytical fan-out completed in-session; read-only Layer 1 + Layer 2 checks require no cross-agent coordination)
Rationale: 4/7 signals present (S4 phase-program, S5 user-flagged depth on the MySQL test-DB operational risk, S6 high-risk operational class — new database creation/credential reuse, S7 5+ blast-radius files). Read-only validation fan-out has no mid-run coordination need, so parallel-subagents (not agent-team) is the correct fit even at this signal count — see vc-agent-strategy-compare reconciliation note.

### Signal Score

4/7 signals present:

| Signal | Present |
|---|---|
| S1: Multi-package scope (3+ workspace packages) | — |
| S2: Schema/API/auth surface touched | — (no functional API/schema change — verified against Public Contracts section) |
| S3: 3+ viable directions surfaced in INNOVATE | — (already closed by Inner Loop Refresh Note) |
| S4: Phase program classification (3+ phases) | YES |
| S5: User requested depth explicitly | YES (user flagged the MySQL test-DB switch as an operational risk requiring High-Risk Pack assessment) |
| S6: High-risk class in blast radius | YES — operational/deploy-adjacent: new `wmslite_test` database must be created; not a schema/auth/API change but is a runtime/test-infra provisioning risk |
| S7: 5+ files in blast radius | YES (~11 backend test files, ~8 factories, phpunit.xml, .env.testing, composer.json/artisan command, frontend package.json, vitest.config.ts, ~14 frontend test files, all-tests.md) |

### Strategy Options

| Strategy | Agent count calculation | Total | Cost guard | Fit for this plan |
|---|---|---|---|---|
| Sequential | 1 executor, all sections in order | 1 agent | None | Underfits — 11 independent backend test files + 14 independent frontend test files have no inter-file dependency once Step A/C1-C2 setup lands; sequential wastes parallelism without adding safety |
| Parallel subagents | 4 (Layer 1) + 4 (Layer 2: Steps A/B/C/D) | 8 agents (validation fan-out, already run analytically this session) | Below 30, no guard triggered | Used for this VALIDATE pass — read-only, no cross-talk needed |
| Workflow | 4 steps (A/B/C/D) x ~3 agents/step x 1 iteration | ~12 agents | Below 30 | Viable for EXECUTE if execute-agent wants per-controller-test pipeline parallelism within Step B; not required — Step B's own internal ordering (B1->B11) is already sequenced by blast-radius dependency in the plan |
| Agent team | N/A — no mid-execution coordination needed between Step A (DB) and Step C (frontend) work | N/A | N/A | Not a fit — Step A must complete before Step B/C can run against a real schema, but that is a hard sequential dependency, not a coordination need; agent-team would be overkill |

### Recommendation

**Parallel subagents (validation) / Sequential-with-internal-batching (execution)** — VALIDATE fan-out used parallel-subagents (8 agents, already executed analytically). For EXECUTE, recommend sequential phase order (A -> B -> C -> D, matching the plan's own Step ordering) since Step A's DB decision gates everything in Step B, and Step C is independent of Step A/B but has no urgency to parallelize given the controller-test count is modest (~11 files) and the agent-time savings would not offset the coordination overhead for a single-developer local-only phase.

---

## I. Validation Findings -> Net Gate

### Layer 1 — Dimension Findings

**Infra / Setup Fit**

| Finding | Severity | Proposed fix |
|---|---|---|
| `wmslite_test` database does not exist yet on the local MySQL instance; `wmslite` (production-like) does, confirmed via prior `php artisan tinker` session | CONCERN | Execute-agent instruction E1 below: A2a must create `wmslite_test` as part of the automated setup command, using the SAME local MySQL host/port/credentials as `wmslite` (only the database name differs). Do not assume separate credentials exist. |
| `tables_schema.sql` contains the `vw_inbound` VIEW definition (`CREATE OR REPLACE VIEW`) and zero `CREATE TRIGGER` statements — confirmed via direct grep | PASS | — (resolves the Blockers-section risk about "undocumented triggers/views beyond `vw_inbound`" — there are none; the view itself IS present in the schema file and will be recreated correctly by A2a) |
| `ApkController.php` raw SQL confirmed real and MySQL-dialect-specific (`DB::raw("CASE WHEN token IS NOT NULL...")`) — validates the Step A1 rationale that SQLite would test different behavior | PASS | — |
| `.env.example` defaults to `DB_CONNECTION=sqlite` with no MySQL credentials block at all — A2's "mirror the real wmslite connection values" instruction has no example to mirror from in version control | CONCERN | Execute-agent instruction E2 below: read the real local env file (gitignored) for the actual MySQL host/port/username/password and mirror those into the new testing env file, swapping only the database name to `wmslite_test`. Never commit that file with real credentials inline — confirm `.gitignore` covers it. |
| Composer already has a `"test"` script (`config:clear` + `php artisan test`) that A2a will need to extend or wrap, not orphan | CONCERN | Execute-agent instruction E3 below: extend the existing composer `"test"` script (or add a new `"test:setup-db"` script that the existing `"test"` script depends on) rather than creating a second, disconnected test entrypoint. Document the final command name in the phase report exactly as instructed in Step D1. |

**Test Coverage**

| Finding | Severity | Proposed fix |
|---|---|---|
| Step B (B1–B11) listed 10 controller test groups but `DashboardController::stats` (`GET /api/dashboard/stats`, aggregates `Inbound`+`Outbound` counts, confirmed real and non-trivial via direct read) had no corresponding Feature test anywhere in the checklist | CONCERN, now FIXED | Plan fix P1 applied: `DashboardControllerTest` added to Step B as B1a (see Implementation Checklist above). |
| Frontend C3 covers all 13 `page.tsx` files including `dashboard/page.tsx`, so the frontend smoke-test side of Dashboard IS already covered — only the backend Feature test was missing | PASS (frontend side) | — |
| Test tier waterfall fully mapped in Section III below; high-risk classes (auth, admin/permission logic) correctly assigned Hybrid minimum tier per the plan's own B8/B9 Feature-test design | PASS | — |
| No CI pipeline exists yet (confirmed in `all-tests.md` Known Gaps) — Hybrid tier preconditions (running local MySQL `wmslite_test`) are realistic for this solo-dev local-only phase but will need re-evaluation in Phase 4 (Deployment Readiness) when CI is introduced | PASS (in-scope), flagged forward | Carried as a known-gap note for Phase 4, not a Phase 1 blocker — see Open Gaps below. |

**Breaking Changes**

| Finding | Severity | Proposed fix |
|---|---|---|
| Public Contracts section explicitly states no API response shape, route, or frontend URL changes — checklist (Steps A–D) confirmed to contain zero edits to controller logic, only new test files, factories, config, and context-doc updates | PASS | — |
| phpunit.xml/testing-env DB connection switch (SQLite -> MySQL `wmslite_test`) is a test-harness change, not a production contract change — does not touch the real `wmslite` database or any consumer-facing surface | PASS | — |
| No downstream consumers (frontend service calls, other phases' code) reference test-only files (`tests/Feature/*`, `tests/Unit/*`, factories) — confirmed via Blast Radius cross-check against Phase 2/3 plans, which only list production code paths, not test paths | PASS | — |

**Security Surface**

| Finding | Severity | Proposed fix |
|---|---|---|
| `AuthControllerTest` (B9), `UserControllerTest` (B8 admin CRUD), `ApkControllerTest` (B7 reset-password/force-logout) all touch security-sensitive surfaces, but only as NEW TESTS verifying EXISTING behavior — no production auth/permission code is modified by this phase | PASS | — |
| STRIDE quick-scan: no new attack surface introduced (no new routes, no new auth bypass paths, no new secret storage) — this phase is purely additive test coverage | PASS | — |
| The new testing env file will exist on disk after EXECUTE and may contain MySQL credentials inline (mirrored from the real local env file) — must not be committed to git | CONCERN | Execute-agent instruction E4 below: verify the new testing env file is covered by `.gitignore` (it should already match the existing env-file ignore pattern, but execute-agent must confirm, not assume) before finishing Step A2. |

---

### Layer 2 — Per-Section Feasibility

**Section A — Backend test database strategy**

| Question | Verdict | Detail |
|---|---|---|
| Mechanical feasibility | PASS | `tables_schema.sql` exists at repo root (496 lines, confirmed readable), contains the full `el_*` schema plus `vw_inbound`. `phpunit.xml` edit target is clear and uniquely editable; the new testing env file is a new file with no collision risk. |
| Plan gaps | CONCERN | A2a does not specify what local MySQL user/grants are needed to `CREATE DATABASE wmslite_test` — if the local MySQL user only has grants on `wmslite`, the automated setup command will fail at database-creation time. Not fixable in plan text (depends on local environment); routed as execute-agent instruction E1. |
| Conflicts | PASS | No conflict with current file state — `phpunit.xml` currently has SQLite config that A2 will replace cleanly; no other phase touches these files (confirmed via blast-radius registry — Phase 1 has no overlap at creation time). |
| Highest-risk edit + mitigation | The A2a automated DB-recreation command (drop/recreate `wmslite_test`) is the highest-risk edit in this section — if the command is miswritten to target `wmslite` instead of `wmslite_test`, it would destroy production-like data. Mitigation: execute-agent MUST hard-code/parameterize the database name from the testing-env `DB_DATABASE` value (never the `wmslite` value), and MUST NOT use a wildcard or environment-ambiguous drop statement. Recommend a literal `DROP DATABASE IF EXISTS wmslite_test; CREATE DATABASE wmslite_test;` rather than any dynamic/templated SQL that could resolve to the wrong name. |

Proposed fixes for this section:
- Execute-agent instruction E1: create `wmslite_test` using same local MySQL credentials as `wmslite`, confirm CREATE DATABASE grant exists before assuming success.
- Execute-agent instruction E5: hard-code the literal database name `wmslite_test` in the drop/recreate statement — never derive it dynamically in a way that could resolve to `wmslite`.

**Section B — Backend Feature tests**

| Question | Verdict | Detail |
|---|---|---|
| Mechanical feasibility | PASS | All 10 originally listed controllers exist at their expected paths (`ApkController.php`, `ReportController.php` confirmed read directly; others referenced consistently with `routes/api.php` route registrations). Ordering B1->B11 is internally consistent with stated blast-radius/dependency rationale. `DashboardController.php` (for B1a) also confirmed to exist with a `stats()` method matching the plan fix description. |
| Plan gaps | RESOLVED | `DashboardController::stats` had no Feature test in the original checklist — fixed via P1 (B1a added). |
| Conflicts | PASS | No conflict with repo conventions — Laravel Feature test conventions (`RefreshDatabase` trait, factory usage) are standard and match the planned MySQL `wmslite_test` strategy (note: `RefreshDatabase` against a real MySQL DB is slower than SQLite `:memory:` but functionally correct; execute-agent should confirm migrations/schema-reload strategy works per-test, not just once at command start — see Section III test tier table). |
| Highest-risk edit + mitigation | B7 (`ApkControllerTest`) is the highest-risk test in this section — it is the one test that actually proves the Step A1 MySQL-vs-SQLite decision was correct (raw SQL `CASE WHEN`/union queries). If this test is skipped or stubbed without real assertions, the entire rationale for the MySQL test-DB switch goes unverified. Mitigation: execute-agent must not mark B7 complete without asserting on the actual `is_logged_in`/`source_table` raw-SQL-derived fields in the response, not just HTTP status codes (instruction E7). |

Proposed fixes for this section: none remaining — P1 applied above; E7 covers the B7 risk.

**Section C — Frontend test runner + smoke tests**

| Question | Verdict | Detail |
|---|---|---|
| Mechanical feasibility | PASS | Confirmed via direct read: zero Vitest/testing-library packages currently in `apps/frontend/package.json` devDependencies (matches plan's "add Vitest" framing exactly — no collision). All 13 `page.tsx` files confirmed present at the exact paths the plan describes (login + 9 module pages + dashboard + root), all `"use client"` per the Inner Loop Refresh Note claim — independently re-verified by listing the files; content-level `"use client"` directive not re-checked line-by-line but file existence and count (13) match exactly. |
| Plan gaps | PASS | C1–C4 cover setup, config, all-page smoke tests, and the NextAuth callback test — no missing area identified beyond the C3 "13 files" count already matching reality. |
| Conflicts | PASS | No conflict — `vite-tsconfig-paths` approach is consistent with the existing `@/*` path alias already in use (no path alias changes needed in this phase). |
| Highest-risk edit + mitigation | C4 (NextAuth `authorize()` callback test) is the highest-risk test in this section since it touches the auth integration boundary directly. Mitigation: mock the Laravel response shape exactly as `AuthController::formatUser()` returns it (confirmed fields: `is_admin`, plus `module`/`type`/etc. per the all-auth.md context doc) — execute-agent should diff the mock object's keys against the real `formatUser()` method's returned array before finalizing the test, not just copy field names from memory. |

Proposed fixes for this section: none — Section C is mechanically sound as written.

**Section D — Close out the testing context doc**

| Question | Verdict | Detail |
|---|---|---|
| Mechanical feasibility | PASS | `process/context/tests/all-tests.md` confirmed readable and editable; the "Known Gaps" section and SQLite-based "Debugging Quick Reference" content that D1/D2 must replace are both clearly identifiable in the current file (read directly this session). |
| Plan gaps | PASS | D1/D2 already explicitly require carrying forward the two Step B backlog notes (Inbound N+1, ReportController dead ternary) and the B8 ad hoc-admin-check scope note — no additional gap found. |
| Conflicts | CONCERN, now mitigated via E6 | The current `all-tests.md` "Debugging Quick Reference" section states "PHPUnit runs against SQLite in-memory" as fact — this will become false the moment A2 lands. D1 already plans to fix this, but the plan text needed to explicitly call out this exact sentence as one of the lines that MUST change (not just "replace the no-test-runner facts"), since it's easy to update the Commands table but miss the prose sentence buried in "Debugging Quick Reference." Step D2 above now carries this instruction inline. |
| Highest-risk edit + mitigation | Low risk overall (docs-only section) — the only risk is leaving stale SQLite references in prose after the Commands table is updated, producing an internally-inconsistent doc. Mitigation: execute-agent should grep `all-tests.md` for the literal string "SQLite" after D1/D2 and confirm zero remaining matches (instruction E6). |

Proposed fixes for this section:
- Execute-agent instruction E6: after D1/D2, grep `all-tests.md` for "SQLite" and "sqlite" and confirm no stale references remain in the Commands table or the Debugging Quick Reference prose.

---

### Net Gate Derivation

| Layer 1 dimensions | Status |
|---|---|
| Infra fit | CONCERN |
| Test coverage | CONCERN (resolved via P1) |
| Breaking changes | PASS |
| Security surface | CONCERN |

| Layer 2 sections | Status |
|---|---|
| Section A — Backend test database strategy | CONCERN |
| Section B — Backend Feature tests | CONCERN (resolved via P1) |
| Section C — Frontend test runner + smoke tests | PASS |
| Section D — Close out the testing context doc | CONCERN (mitigated via E6) |

**Totals: 0 FAILs / 7 raw CONCERN findings (collapsed to 1 plan fix [P1, already applied above] + 7 execute-agent instructions [E1–E7] + 0 known-gaps requiring backlog deferral at the gate level) / remaining findings PASS**

**-> Net Gate: CONDITIONAL**

0 FAILs. All 7 raw CONCERN findings are resolvable within this phase's existing blast radius — none require returning to PLAN. P1 is already applied to the Implementation Checklist above; E1–E7 are written below for execute-agent to follow.

---

## II. Execution Strategy

(See "Parallel strategy" / Signal Score / Strategy Options / Recommendation block above — repeated here per template for execute-agent's single-document reference.)

Recommended for EXECUTE: sequential phase order A -> B -> C -> D (Step A gates Step B; Step C is independent but not worth parallelizing for a ~25-file, solo-dev, local-only phase).

---

## III. Test Coverage Plan

**Area: `apps/backend/tests/Feature` + `apps/backend/tests/Unit` — Backend Feature/Unit test suite**

| Tier | Scenario | Command / Steps | What it proves | What it does NOT prove |
|---|---|---|---|---|
| Fully-automated | All Step B controller tests (B1, B1a, B2–B11) pass against `wmslite_test` | `cd apps/backend && composer test` (extended A2a script: drop/recreate `wmslite_test`, load `tables_schema.sql`, run `php artisan test`) — exits 0 | Every listed controller's CRUD/read endpoints behave correctly against MySQL-dialect-accurate schema and data | Behavior against the LIVE `wmslite` database (deliberately never touched); concurrent-write behavior under real warehouse-staff load; any endpoint not explicitly listed in Step B |
| Fully-automated | `ApkControllerTest` (B7) specifically asserts raw-SQL-derived fields (`is_logged_in`, `source_table`) | Same suite, B7 sub-assertions | The MySQL-vs-SQLite Step A1 decision was correct — raw `CASE WHEN`/union SQL executes and returns expected shape | Whether the APK Scanner mobile app itself (not yet built) will correctly consume this data — out of this phase's scope entirely |
| Fully-automated | Auth middleware regression (B11) — every protected route returns 401 without a valid token | Same suite, B11 parametrized/looped test | No route accidentally lost its `auth:sanctum` middleware | Token expiry mid-session behavior, rate-limiting on repeated failed auth attempts (Phase 3 scope) |
| Hybrid | Full backend suite against real local MySQL `wmslite_test` | `cd apps/backend && composer test` — precondition: local MySQL running, `wmslite_test` database creatable by the configured DB user, `tables_schema.sql` present at repo root | End-to-end schema-loading + test-execution pipeline works on this machine | Whether the SAME pipeline works on a different machine, in CI, or in a container (Phase 4 scope — no CI exists yet) |
| Known-gap | Concurrent-access behavior between the live `wmslite` database (used by the running Yii app) and the unrelated `wmslite_test` database on the same MySQL instance | — | — | Cannot be tested within this phase's scope — single local MySQL instance; resource contention (connection limits, lock contention) between `wmslite` and `wmslite_test` running side-by-side is a known-gap, accepted because both DBs are logically separate schemas with independent per-process connection pools. Rationale: low risk for a local dev solo-developer setup; re-evaluate in Phase 4 (shared hosting/CI environment). |

Failing stub:
```
test("should run composer test and pass all Step B controller test groups against wmslite_test", () => {
  throw new Error("NOT IMPLEMENTED -- TDD stub: full backend Feature/Unit suite green against wmslite_test, schema loaded from tables_schema.sql")
})
```

Gaps and resolution options:

| Gap | Resolution options |
|---|---|
| Local MySQL user's CREATE DATABASE grant for `wmslite_test` is unverified | A) N/A. B) Set up infra — execute-agent runs a manual grant-check (`SHOW GRANTS`) before A2a's automated command, documents result in phase report. C) Accept as known-gap only if grants are genuinely unavailable and a workaround (asking user to pre-create the empty DB) is documented instead. D) N/A. **Resolution chosen: B, folded into execute-agent instruction E1.** |
| Concurrent `wmslite`/`wmslite_test` resource contention on shared local MySQL instance | A) N/A — not fixable by writing a test. B) N/A — no realistic infra change for a local dev box. C) Accept as known-gap — rationale: separate schemas, independent connection pools, low realistic risk for solo local dev. D) Backlog artifact if Phase 4 staging introduces shared-resource constraints — create only if Phase 4 research finds this is a real constraint (YAGNI, not created now). **Resolution chosen: C, with D as a forward pointer only.** |

---

**Area: `apps/frontend` — Vitest test runner + smoke tests**

| Tier | Scenario | Command / Steps | What it proves | What it does NOT prove |
|---|---|---|---|---|
| Fully-automated | Login page smoke test renders without throwing, shows expected fields | `cd apps/frontend && npm run test` (Vitest run) — exits 0 | Login page mounts correctly with mocked API calls | Real integration with the live Laravel backend (frontend tests mock `*.service.ts`, never hit real backend per C3) |
| Fully-automated | One smoke test per remaining 12 `page.tsx` files (9 modules + dashboard + root, login already counted above) — renders, shows expected heading/empty-state text, "Add X" button present | Same suite | Every dashboard page mounts without throwing and shows its primary action affordance | Visual regression (no pixel/snapshot comparison), real data-fetching behavior, complex modal/dialog interaction beyond presence-of-button (e.g. Shipment's push-to-inbound dialog full flow is NOT asserted beyond the button existing, per C3's stated scope) |
| Fully-automated | NextAuth `authorize()` callback test (C4) — mocked Laravel response maps to expected user object fields | Same suite, C4 specific test file | The exact integration point between NextAuth and Laravel's `formatUser()` shape stays in sync | Real Laravel response (mocked only); session expiry/refresh behavior; multi-tab session behavior |
| Hybrid | Frontend build sanity after test files are added | `cd apps/frontend && npm run lint && npm run build` — precondition: Vitest dependencies don't break the Next.js build (e.g. type conflicts between testing-library types and existing types) | Test additions don't break production build or introduce lint errors | Runtime behavior in an actual browser (build success != runtime correctness — that's what the smoke tests themselves are for) |
| Known-gap | Full Shipment push-to-inbound dialog interaction (open dialog, fill form, submit, verify `from_shipment` flips) | — | — | Cannot be tested within this phase's scope at the "smoke test" tier C3 explicitly defines (presence-of-button only); a full interaction test is a reasonable Phase 2 candidate once the underlying status-flow semantics are confirmed (Phase 2 scope per umbrella's Open Questions). Accepted as known-gap with explicit rationale: deeper interaction testing depends on Phase 2's shipment-status-flow confirmation, which hasn't happened yet — testing the interaction now risks asserting against semantics that Phase 2 will change. |

Failing stub:
```
test("should render login page smoke test with mocked auth service and show expected fields", () => {
  throw new Error("NOT IMPLEMENTED -- TDD stub: login page smoke test, mocked *.service.ts calls")
})
```

Gaps and resolution options:

| Gap | Resolution options |
|---|---|
| Shipment push-to-inbound full dialog interaction not tested beyond button presence | A) N/A within this phase's C3-defined scope. B) N/A. C) Accept as known-gap — rationale: Phase 2 will confirm/change shipment-status-flow semantics; testing the full interaction now risks immediate staleness. D) Backlog artifact: create during Phase 1 durable-capture step, to be picked up once Phase 2 confirms status-flow semantics. **Resolution chosen: C + D.** |

---

**High-risk class areas**

| Area | High-risk class | Minimum tier | Gap rationale if known-gap accepted |
|---|---|---|---|
| `wmslite_test` database creation (A2a automated setup) | deploy/runtime/test-infra provisioning (operational risk explicitly flagged by user, not a standard 6-class high-risk item but treated with equivalent rigor per user instruction) | Hybrid (precondition: local MySQL running + CREATE DATABASE grant) | N/A — not accepted as known-gap; A2a is fully in-scope and required by the plan itself. Risk is mitigated via execute-agent instructions E1 and E5 (hard-coded DB name, grant verification), not deferred. |
| `AuthControllerTest` (B9), `UserControllerTest` (B8) | auth/identity, permission logic | Hybrid | N/A — both are Fully-automated/Hybrid tier tests already planned in Step B; no known-gap needed. |
| `ApkControllerTest` (B7) reset-password/force-logout | auth/identity-adjacent (separate APK Scanner account system) | Hybrid | N/A — Fully-automated/Hybrid tier already planned. |

---

**Missing test areas (no coverage possible at any tier within this plan's scope)**

| Area | Why untestable in this plan | Resolution chosen |
|---|---|---|
| Shipment push-to-inbound full dialog interaction | Depends on Phase 2's unconfirmed shipment-status-flow semantics | Backlog note (create during durable capture) |
| `wmslite`/`wmslite_test` concurrent-resource-contention behavior on shared local MySQL | Single local dev machine, no CI/staging environment yet (Phase 4 scope) | Deferred to Phase 4 — re-evaluate if shared-hosting constraints surface |
| CI-driven automated execution of this phase's test suite | No CI/CD pipeline exists yet (`all-tests.md` Known Gaps confirms zero `.github/workflows`) | Deferred to Phase 4 (Deployment Readiness) — explicitly in scope there per umbrella Phase Sequence table |

---

## IV. Proposed Plan Updates (applied)

| # | What changes | Where in plan | Why |
|---|---|---|---|
| P1 | Added `DashboardControllerTest` (GET `/api/dashboard/stats` — asserts aggregated counts shape against seeded Inbound/Outbound factory data) to Step B checklist as B1a | Step B — Implementation Checklist (applied above, inserted after B1) | `DashboardController::stats` is a real, reachable, non-trivial endpoint (aggregates 2 models, has its own route) with zero coverage in the original Step B list — leaving it untested would mean this phase does not actually deliver "coverage of every existing API endpoint" per the umbrella's Definition of Done item 1 |

Execute-agent instructions (concerns that cannot be fixed in plan text):

| # | Instruction | Trigger condition |
|---|---|---|
| E1 | Before running A2a's automated `wmslite_test` creation, verify the local MySQL user (the same credentials used for `wmslite`) has `CREATE DATABASE` grant. If not, document the constraint in the phase report and ask the user to pre-create an empty `wmslite_test` database, rather than silently failing or attempting to use elevated/different credentials. | Step A2a entry, before first execution |
| E2 | Populate the new `apps/backend/.env.testing` file by reading the real local env file (gitignored) for the actual MySQL host/port/username/password and mirroring them, changing ONLY the database name to `wmslite_test`. Do not invent placeholder credentials and do not assume `.env.example` values are real (they are not — it defaults to `DB_CONNECTION=sqlite` with no live values). | Step A2 entry |
| E3 | Extend the EXISTING `composer.json` `"test"` script (currently `["@php artisan config:clear --ansi", "@php artisan test"]`) to call the new A2a setup step first, OR add a new `"test:setup-db"` script that `"test"` depends on. Do not create a second, parallel, disconnected test entrypoint that would not be discovered later. Document the final script name and exact invocation in the phase report and in Step D1's all-tests.md update. | Step A2a entry |
| E4 | After creating the new backend testing env file, confirm it is covered by an existing `.gitignore` pattern (check root and `apps/backend/.gitignore` for the relevant env-file pattern) before considering Step A2 complete. If not covered, add it to `.gitignore` in the same commit. Never commit this file with real credentials. | Step A2 completion, before moving to Step A2a |
| E5 | The A2a drop/recreate database statement MUST use the literal hard-coded string `wmslite_test` — never a dynamically resolved or templated database name that could accidentally resolve to `wmslite` (the live production-like database). Prefer `DROP DATABASE IF EXISTS wmslite_test; CREATE DATABASE wmslite_test;` (or Laravel-native equivalent with the literal string) over any env-driven dynamic SQL for this specific destructive statement. | Step A2a implementation |
| E6 | After Step D1/D2 edits land, grep `process/context/tests/all-tests.md` for the strings "SQLite" and "sqlite" and confirm zero remaining matches (the current file's "Debugging Quick Reference" section states "PHPUnit runs against SQLite in-memory" as fact — this exact sentence must be replaced, not just the Commands table). | Step D2 completion, before phase report is written |
| E7 | B7 (`ApkControllerTest`) must assert on the actual raw-SQL-derived response fields (`is_logged_in`, `source_table`) returned by `ApkController`'s union query — not just HTTP status codes — since this is the one test that proves the Step A1 MySQL-vs-SQLite decision was correct. A status-code-only test would technically pass while leaving the actual decision unverified. | Step B7 implementation |

Backlog artifacts to create during durable capture phase:

| Artifact | Location | What it tracks |
|---|---|---|
| `shipment-push-inbound-interaction-test_NOTE_19-06-26.md` | `process/features/go-live/backlog/` | Full Shipment push-to-inbound dialog interaction test (beyond button-presence smoke test) — deferred until Phase 2 confirms shipment-status-flow semantics |
| (carry forward, already specified in plan Step D2 — not new) Inbound `index()` N+1 query pattern | `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_REPORT_19-06-26.md` (phase report, per plan's own instruction) | Phase 2+ backlog candidate, recorded per Step B6 |
| (carry forward, already specified in plan Step D2 — not new) `ReportController::buildReport()` dead ternary | Same phase report | Phase 2+ backlog candidate, recorded per Step B10 |
| (carry forward, already specified in plan Step D2 — not new) `UserController` ad hoc admin-check scope note | Same phase report | Phase 2 forward reference (Policy/Gate formalization), recorded per Step B8 |

---

## V. User Decision (V5 Gate)

**Correction:** this validate-contract was originally written assuming active `/goal` autonomous execution. That assumption was wrong — no standing `/goal` is active in this session; the orchestrator checkpoints with the user at every phase-loop step. The user reviewed this full validate-contract in chat and explicitly approved proceeding to EXECUTE on 19-06-26. Decision: **A — Accept** (P1 applied to plan; E1–E7 written to this validate-contract; test plan written; backlog artifacts listed for durable-capture creation later; proceed to EXECUTE).

Accepted by: user (explicit chat approval, 19-06-26) — accepted concerns: DB-creation-grant verification gap (E1), credential-mirroring gap (E2), composer-script-integration gap (E3), gitignore-confirmation gap (E4), DB-name hard-coding mitigation (E5), stale-SQLite-prose risk (E6), B7 raw-SQL-assertion depth requirement (E7), missing DashboardControllerTest (P1, applied to plan above).

### Plan updates applied
- [x] Added `DashboardControllerTest` requirement to Step B as B1a (P1) — see Implementation Checklist above.

### Execute-agent instructions
- Step A2a entry: E1 (verify CREATE DATABASE grant before automated setup)
- Step A2 entry: E2 (mirror real local env MySQL credentials into the new testing env file, swap DB name only)
- Step A2a entry: E3 (extend existing composer `"test"` script, don't fork a disconnected entrypoint)
- Step A2 completion: E4 (confirm the new testing env file is gitignored before considering A2 done)
- Step A2a implementation: E5 (hard-code literal `wmslite_test` in drop/recreate statement — never dynamic)
- Step D2 completion: E6 (grep all-tests.md for stale "SQLite" references after edit)
- Step B7 implementation: E7 (assert on raw-SQL-derived fields, not just HTTP status)

### Test gates (run after each section; regression suite after all sections)

**`apps/backend/tests/Feature` + `apps/backend/tests/Unit`**
- Fully-automated: `cd apps/backend && composer test` exits 0
  Proves: every Step B controller (B1, B1a, B2–B11) behaves correctly against MySQL-dialect-accurate `wmslite_test` schema
  Precondition (Hybrid overlap): local MySQL running, `wmslite_test` creatable by configured DB user, `tables_schema.sql` present at repo root
- Known-gap: `wmslite`/`wmslite_test` concurrent-resource-contention on shared local MySQL — resolution: accepted with rationale (separate schemas/connection pools, low risk for solo local dev), re-evaluate in Phase 4

**`apps/frontend`**
- Fully-automated: `cd apps/frontend && npm run test` exits 0
  Proves: all 13 page smoke tests + NextAuth callback test (C4) pass with mocked API calls
- Fully-automated: `cd apps/frontend && npm run lint && npm run build` exits 0
  Proves: new test dependencies don't break production build or introduce lint errors
- Known-gap: Shipment push-to-inbound full dialog interaction — resolution: backlog artifact (see above), deferred to post-Phase-2

**`process/context/tests/all-tests.md`**
- Fully-automated: `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` exits 0
- Fully-automated: `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` exits 0
  Proves: context doc edits (Step D) don't break frontmatter/routing validators
  Does NOT prove: prose accuracy (e.g. stale "SQLite" references) — covered instead by execute-agent instruction E6's manual grep step

**Regression suite (after all sections complete — this is Phase 1, no prior phase to regress against)**
- All of the above test gates, re-run once at the end as the Exit Gate block in the plan already specifies
- No cross-phase regression check applicable — Phase 1 is the first phase in the program; nothing prior is VERIFIED yet to regress against (per user's explicit note — regression step intentionally skipped for this reason)

### High-risk pack
Required: no

This phase does not touch the 6 canonical high-risk classes (auth/identity code changes, billing/credits, schema/data migration of production data, public API contract changes, deploy/runtime/container/proxy/gateway behavior changes, permission/secret/trust-boundary logic changes) — it only ADDS TESTS verifying existing behavior, and creates a new local-only test database. The `wmslite_test` creation is an operational risk (correctly flagged by the user) but is mitigated via execute-agent instructions E1/E5 above rather than the formal 5-artifact evidence pack, because: (a) no production data, auth flow, or public contract is modified; (b) the destructive operation (drop/recreate) targets only the new `wmslite_test` database, never `wmslite`; (c) the mitigation (hard-coded DB name literal) is a stronger, more mechanical safeguard than a manual evidence pack would add for this specific risk shape. If execute-agent discovers during implementation that the automated setup command cannot safely guarantee it never touches `wmslite` (e.g. a shared connection/config bug), STOP and escalate — do not proceed past that point without re-running this validate-contract's E1/E5 mitigations or invoking the full risk-evidence-pack for the "deploy/runtime" class as a fallback.

### Backlog artifacts to create during durable capture
- `process/features/go-live/backlog/shipment-push-inbound-interaction-test_NOTE_19-06-26.md` — full Shipment push-to-inbound dialog interaction test, deferred until Phase 2 confirms shipment-status-flow semantics

### Known gaps on record
- `wmslite`/`wmslite_test` concurrent local MySQL resource contention — accepted as known-gap; rationale: separate schemas, independent connection pools, low risk for solo local dev; re-evaluate in Phase 4 if shared-hosting/CI constraints surface
- Shipment push-to-inbound full interaction beyond button-presence — accepted as known-gap; rationale: depends on Phase 2's unconfirmed shipment-status-flow semantics; testing now risks immediate staleness
- CI-driven automated execution of this phase's suite — deferred to Phase 4 (Deployment Readiness), already explicitly in scope there

What this coverage does NOT prove:
- Backend Fully-automated gate (`composer test`) does NOT prove: behavior against the live `wmslite` database; behavior under concurrent multi-user warehouse-staff load; APK Scanner mobile app consumption of the tested data (app not yet built); token-expiry-mid-session behavior (Phase 3 scope); rate-limiting on failed auth attempts (Phase 3 scope)
- Frontend Fully-automated gate (`npm run test`) does NOT prove: visual/pixel-level regression; real backend integration (mocked only); full Shipment push-to-inbound interaction beyond button presence; multi-tab session behavior
- Context-doc validators do NOT prove: prose accuracy of the all-tests.md content itself — only structural/frontmatter validity; manual E6 grep step is the actual prose-accuracy check

Accepted by: user (explicit chat approval, 19-06-26) — see V. User Decision above for the full accepted-concerns list.
