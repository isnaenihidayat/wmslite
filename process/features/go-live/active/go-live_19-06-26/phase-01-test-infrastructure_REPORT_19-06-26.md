---
name: report:go-live-phase-01-test-infrastructure
description: "WMS Lite go-live — Phase 1 closeout report: Test Infrastructure Foundation"
date: 20-06-26
metadata:
  node_type: memory
  type: report
  feature: go-live
  phase: phase-01
---

# Phase 01 — Test Infrastructure Foundation — Closeout Report

**Program:** go-live
**Phase plan:** `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_PLAN_19-06-26.md`
**Status:** ✅ VERIFIED
**Date:** 20-06-26

---

## Summary

Phase 1 built the test safety net the rest of the go-live program depends on. WMS Lite went from
zero real test coverage (Laravel default `ExampleTest.php` boilerplate only, no frontend test
runner at all) to 162 backend tests (414 assertions) covering all 10 controller groups + the
Dashboard endpoint, and 18 frontend smoke tests across 14 files covering every page plus the
NextAuth integration boundary. Work proceeded in the four planned steps (A → B → C → D), each
gated by the validate-contract's execute-agent instructions (E1–E7).

### Step A — Backend test database strategy

Decision (already locked at PLAN time, implemented as planned): a dedicated MySQL database named
`wmslite_test`, loaded from the repo-root `tables_schema.sql`, replaces the original SQLite
`:memory:` assumption. Rationale carried forward verbatim from the plan: schema parity with the
single source of truth, no new infra needed (MySQL already running locally), and raw-SQL realism
for MySQL-dialect-specific queries (`ApkController`'s `CASE WHEN`/union queries) that SQLite
cannot execute faithfully.

Implementation:
- `apps/backend/phpunit.xml` updated to `DB_CONNECTION=mysql`, `DB_DATABASE=wmslite_test`, mirroring
  the real local `wmslite` connection's host/port/credentials (E2 satisfied — credentials read from
  the real gitignored local env file, not invented).
- New artisan command `apps/backend/app/Console/Commands/SetupTestDatabase.php` (`php artisan
  test:setup-db`) drops/recreates `wmslite_test` using the literal hard-coded database name (E5
  satisfied — no dynamic/templated SQL that could resolve to `wmslite`), then loads schema from
  `tables_schema.sql`.
- `apps/backend/composer.json` `"test"` script extended (not forked) to call `composer
  test:setup-db` before `php artisan test` (E3 satisfied). Final command: `composer test` runs the
  full setup + suite in one invocation.
- `apps/backend/.gitignore` confirmed/extended to cover the new testing env file (E4 satisfied — no
  credentials committed).
- CREATE DATABASE grant on the local MySQL user verified present before relying on the automated
  command (E1 satisfied).
- Factories added: `UserFactory` (extended), `InboundFactory`, `OutboundFactory`, `LocationFactory`,
  `ProductCategoryFactory`, `RecipientFactory`, `MovingFactory`, `ApkUserFactory` (covers both
  `el_apk` and `el_apk_s`), plus two factories not explicitly named in the original plan text:
  `ActivityLogFactory` and `InboundDetailFactory` (needed for `MonitoringControllerTest` and
  `InboundControllerTest`'s `/details` coverage respectively — see Deviations below).

### Step B — Backend Feature/Unit tests

All 11 planned test files written, plus the PVL-added `DashboardControllerTest` (B1a):
`MonitoringControllerTest`, `DashboardControllerTest`, `MasterControllerTest`,
`OutboundControllerTest`, `MovingControllerTest`, `ShipmentControllerTest`,
`ShipmentControllerPushInboundTest` (Unit, isolated 409-conflict case), `InboundControllerTest`,
`ApkControllerTest`, `UserControllerTest`, `AuthControllerTest`, `ReportControllerTest`,
`AuthMiddlewareRegressionTest`. `ApkControllerTest` (B7) asserts on the actual raw-SQL-derived
response fields (`is_logged_in`, `source_table`), not just HTTP status codes, per E7.

During this step, two genuine product bugs were discovered (not introduced by this phase — see
Backlog below) and are now on record as 500-returning endpoints rather than silently
under-tested.

### Step C — Frontend test runner + smoke tests

Vitest + `@vitejs/plugin-react` + `jsdom` + `@testing-library/react` + `@testing-library/dom` +
`vite-tsconfig-paths` added to `apps/frontend/package.json` devDependencies. `vitest.config.ts`
wired to the existing `@/*` path alias. One smoke test written per page (login, dashboard, 9
module pages, admin/users) plus the NextAuth `authorize()` callback test (`auth.test.ts`) verifying
field-for-field parity with `AuthController::formatUser()`'s real response shape. 14 test files, 18
tests total, all green.

### Step D — Testing context doc closeout

`process/context/tests/all-tests.md` updated: real commands (`composer test`, `npm run test`)
replace the "no test runner" facts; the backend test-DB strategy (Step A) is documented with
rationale. Grep confirmed (E6) zero stale "SQLite"/"sqlite" references remain — the two current
matches in the file are the new, accurate decision-rationale prose explaining why SQLite was
rejected, not stale claims that tests run against it. Carried-forward backlog notes (Inbound
`index()` N+1, `ReportController::buildReport()` dead ternary, `UserController` ad hoc admin-check
scope note) recorded in "Known Gaps" as forward references for Phase 2.

---

## Final Verification Commands

```bash
cd apps/backend && composer test
# -> 162 tests, 414 assertions, 0 failures

cd apps/frontend && npm run test
# -> 14 test files, 18 tests, 0 failures

cd apps/frontend && npm run build
# -> succeeds, all 13 routes compile

node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs
# -> both 0 warnings / 0 failures
```

`npm run lint` reports 7 pre-existing errors + 13 pre-existing warnings, all in files this phase
never touched (`data-table.tsx`, `monitoring/page.tsx`, `moving/page.tsx`, `outbound/page.tsx`,
`shipment/page.tsx`) — confirmed via `git status` these predate this phase. Not a regression, not
fixed (out of this phase's "tests only" blast radius). Carried to backlog.

---

## Deviations From Plan

1. **`@vitejs/plugin-react` version pinned to 5.2.0, not 6.x.** The plan's Step C1 did not specify
   an exact version. During implementation, the 6.x major was found to have a peer-dependency
   conflict with the installed Vite/Next.js toolchain in this repo. 5.2.0 was installed instead —
   functionally equivalent for this phase's needs (jsdom + Testing Library smoke tests), no feature
   used by this phase's tests requires 6.x. No further action needed; record only for future
   dependency-bump awareness.
2. **Two factories built that were not explicitly named in the original Blast Radius list:**
   `ActivityLogFactory` (needed for `MonitoringControllerTest`'s read-only activity-log fixture
   data) and `InboundDetailFactory` (needed for `InboundControllerTest`'s `/details` endpoint
   coverage). Both are mechanically identical in spirit to the planned factory list (minimal model
   factories for Feature test fixtures) — not a scope change, just an implementation-time
   discovery that the controller-test list implied two more tables needed factories than the plan
   text enumerated by name. Registry updated accordingly (see `phase-blast-radius-registry.md`).
3. **New artisan command file path not named in the plan text verbatim.** The plan's Step A2a left
   the choice between an artisan command and a composer script to the execute-agent's judgment. The
   command was implemented at `apps/backend/app/Console/Commands/SetupTestDatabase.php`
   (`php artisan test:setup-db`), wrapped by the composer `test:setup-db` script, which the
   existing `test` script now depends on. This is the documented choice per Step A2a's own
   "document which was picked and why" instruction, not an undocumented deviation.

No deviations required returning to PLAN. All three items above were resolvable within this
phase's existing blast radius per the validate-contract's Net Gate (CONDITIONAL, 0 FAILs).

---

## Backlog Items Found (5 total)

**Anticipated by PVL/plan text (3):**

1. `InboundController::index()` — potential N+1 query pattern. Functional, not broken — perf note
   only. Phase 2+ candidate.
2. `ReportController::buildReport()` — dead ternary (`$query->getModel()->getTable() ===
   'outbound_header' ? 'date_created' : 'date_created'`); both branches identical, condition has no
   effect. Cosmetic, no behavior change if fixed. Phase 2+ candidate.
3. `UserController` admin check is ad hoc (not a formal Laravel Policy/Gate). Already explicitly
   planned as Phase 2 scope (Policy/Gate formalization is one of the umbrella's Open Questions).

**New bugs discovered during Step B (2) — NOT anticipated by PLAN or PVL:**

4. **`ShipmentController::pushInbound()` always returns HTTP 500.** Root cause: the method
   attempts to create a new `el_inbound_header` row using the shipment's `hawb`, but the unique
   constraint on `hawb` in `el_inbound_header` is violated whenever that HAWB already has any
   inbound record (which is the common case in this codebase's data shape). The method's own
   409-conflict branch — written to handle exactly this duplicate case — is unreachable dead code
   because the unique-constraint violation throws before the conditional duplicate-check logic is
   ever reached. This is a genuine product bug, not a test-infra gap.
5. **`InboundController::details()` always returns HTTP 500.** Root cause: the query calls
   `orderBy('id')` against `el_inbound_details`, which has no `id` column at all in the schema
   (confirmed in `tables_schema.sql`). Every call to this endpoint throws a SQL error.

Both new bugs are written up as standalone backlog NOTE files (see below) rather than fixed inline,
per this phase's "add tests, do not fix product code" blast-radius boundary (explicitly stated in
the plan's "Blockers That Would Justify BLOCKED Status" section).

---

## Recommendation for Phase 2

**Triage backlog items 4 and 5 at the START of Phase 2, before auth/data-model hardening begins —
do not treat them as a separate, deferred dirty-fix pass.**

Rationale: Phase 2's planned scope explicitly includes resolving the shipment-status-flow Open
Question (`process/context/all-context.md`) and confirming how `from_shipment`/inbound-header
linkage should behave. `ShipmentController::pushInbound()` (backlog item 4) is the exact code path
that Phase 2's status-flow confirmation work will touch — fixing the 500 bug in isolation, before
Phase 2's semantics are confirmed, risks fixing it twice (once as a bug fix, once again when
Phase 2 changes the intended behavior). Recommend folding item 4's fix into Phase 2's first research
step as a "confirm intended behavior, then fix the bug as part of that confirmed behavior" task,
not a standalone hotfix.

Backlog item 5 (`InboundController::details()`) has no such semantic ambiguity — it is a one-line
fix (change `orderBy('id')` to an existing column, e.g. `hawb` or `scan_time`) with no overlap with
Phase 2's Open Questions. It can be picked up as a quick, low-risk fix early in Phase 2 independent
of the status-flow work, or even before Phase 2 RESEARCH starts if the user wants an immediate
hotfix — either timing is safe.

The previously-deferred Shipment push-to-inbound interaction test (already on the backlog from PVL,
see `shipment-push-inbound-interaction-test_NOTE_19-06-26.md`) should stay deferred until AFTER
item 4 is fixed and Phase 2's status-flow semantics are confirmed — writing that interaction test
now would assert against a currently-broken endpoint.

---

## Validate-Contract Outcome

Net Gate: CONDITIONAL at PVL (0 FAILs, 7 raw CONCERN findings — 1 plan fix [P1] + 7 execute-agent
instructions [E1–E7]). All 7 instructions were followed during EXECUTE (see Step A/B notes above);
no instruction was skipped or found infeasible. No High-Risk Pack was required (this phase only
adds tests; the one operational risk — `wmslite_test` creation — was mitigated via E1/E5 rather
than the formal 5-artifact evidence pack, as documented in the plan's "High-risk pack" section).

---

## Regression Note

No regression check was run against a prior phase, per the umbrella's own instruction: Phase 1 is
first in the program, so nothing prior is VERIFIED yet to regress against.

---

## Status

✅ VERIFIED — both test suites green (162 backend / 18 frontend, 0 failures), both context
validators clean, all checklist items A1–D2 complete, phase report written.
