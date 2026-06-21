---
name: plan:go-live-phase-02-data-model-auth-hardening
description: "WMS Lite go-live — Phase 2: Data Model & Auth Hardening"
date: 19-06-26
metadata:
  node_type: memory
  type: plan
  feature: go-live
  phase: phase-02
---

# Phase 02 — Data Model & Auth Hardening

**Program:** go-live
**Umbrella plan:** process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md
**Phase status:** ✅ VERIFIED
**Report destination:** process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_REPORT_19-06-26.md (flat in the program task folder)

---

## Purpose

`process/context/all-context.md` and `process/context/database/all-database.md` logged a set of
unresolved "Open Questions" from the initial harness STUDY pass: the meaning of legacy shadow `_s`
tables, the exact shipment status flow, and whether `type`/`admin`/`module` role checks are
formalized as Laravel Policy/Gate classes or still ad hoc per-controller. RESEARCH + INNOVATE for
this phase are now complete and user-approved: all open questions are resolved (two of the four
original assumptions turned out to be wrong — see Inner Loop Refresh Note below), and INNOVATE
added four concrete implementation items beyond documentation updates: a UI mislabeling fix, two
product bug fixes (one of which is a corrected design, not a guess), and a scoped MVP Policy
hardening pass for the three highest-risk controller clusters. Nothing in Phase 3 (security audit)
can be trusted until the data model and authZ model are actually understood, documented correctly,
and the highest-risk gaps are closed.

---

## Entry Gate

- Phase 1 exit gate passed: `php artisan test` and `npm run test` both green, covering all 9 modules
- Phase 1 report read for any product bugs logged as "Phase 2 input" rather than fixed inline
  (two were logged as backlog NOTEs — see Step B and "Phase 1 Bug Closure" below)

---

## Blast Radius

- `apps/backend/app/Policies/ApkUserPolicy.php` (new)
- `apps/backend/app/Policies/UserPolicy.php` (new)
- `apps/backend/app/Policies/MovingPolicy.php` (new — gates `destroy` only)
- `apps/backend/app/Providers/AppServiceProvider.php` (Policy registration — **corrected at PVL**:
  this Laravel 13 app has no `AuthServiceProvider`; registration goes in
  `AppServiceProvider::boot()` via `Gate::policy(...)`. See validate-contract Execute-Agent
  Instruction E1.)
- `apps/backend/app/Http/Controllers/Api/ApkController.php` (`resetPassword`, `logout` gated via
  `$this->authorize(...)` — class-string form, see E2)
- `apps/backend/app/Http/Controllers/Api/UserController.php` (all 5 methods: replace ad hoc
  `if (!$request->user()->admin)` with `$this->authorize(...)`)
- `apps/backend/app/Http/Controllers/Api/MovingController.php` (`destroy` gated)
- `apps/backend/app/Http/Controllers/Api/ShipmentController.php` (`pushInbound()` — fix to update
  in-place instead of creating a duplicate `Inbound` row; remove now-irrelevant 409 duplicate-hawb
  check)
- `apps/backend/app/Http/Controllers/Api/InboundController.php` (`details()` — fix ordering from
  `orderBy('id')` to `orderBy('date_created')`, or `scan_time` if frontend consumer needs scan order
  — confirm during EXECUTE)
- `apps/backend/tests/Feature/MovingControllerTest.php`,
  `apps/backend/tests/Feature/ApkControllerTest.php`,
  `apps/backend/tests/Feature/UserControllerTest.php` (existing tests use non-admin actors for
  actions that will become Policy-gated — **must be updated as part of B1, not left red**; see E4)
- `apps/backend/tests/Unit/ShipmentControllerPushInboundTest.php`,
  `apps/backend/tests/Feature/ShipmentControllerTest.php`,
  `apps/backend/tests/Feature/InboundControllerTest.php` (existing tests assert the *current buggy*
  500/dead-branch behavior — **must be rewritten to assert the fixed behavior**, not left red or
  deleted; see E5)
- `apps/frontend/src/app/(dashboard)/master/apk/page.tsx` (label fix: "Main"/"Staging" →
  "OTR Module"/"Service Module"; remove incorrect "— Testing" suffix; add permanent warning on
  Service Module account creation/edit; fix table badge ~line 152-158)
- `apps/frontend/src/app/(dashboard)/master/apk/page.test.tsx` (update assertions for the label
  rename, see E7)
- `apps/frontend/src/lib/api/apk.service.ts` (check for user-facing "staging"/"main" strings)
- `apps/frontend/src/lib/api/shipment.service.ts` (`pushToInbound()` — update response handling to
  reflect in-place transition, not a new record)
- `apps/frontend/src/app/(dashboard)/shipment/page.tsx` (**corrected at PVL** — the actual
  push-to-inbound confirmation copy lives here, in the `AlertDialogDescription` around lines
  219-228: "akan di-salin ke modul Inbound sebagai record baru" / "Jika HAWB sudah ada di Inbound,
  operasi ini akan gagal" — both describe the OLD create-new-record semantics. `shipment-form-sheet.tsx`
  has NO push-related copy and is NOT a touchpoint for this fix — removed from blast radius. See E6.)
- `apps/frontend/src/app/(dashboard)/shipment/page.test.tsx` (update if it asserts the old copy)
- `process/context/all-context.md` (remove resolved items from "Open Questions"; add confirmed
  facts, including the shipment status flow)
- `process/context/database/all-database.md` (replace "needs verification" language with confirmed
  facts about `_s` shadow tables — both the bulk/lot pattern and the `el_apk`/`el_apk_s` exception)
- `process/context/auth/all-auth.md` (replace "needs verification" language about Policy/Gate with
  confirmed implementation — MVP scope only, not full coverage)
- `process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md` (mark resolved once
  fix lands in EXECUTE)
- `process/features/go-live/backlog/inbound-details-endpoint-bug_NOTE_20-06-26.md` (mark resolved
  once fix lands in EXECUTE)

**Explicitly NOT in this phase's blast radius:** `apps/backend/app/Models/Inbound.php` — no enum or
status constant is added to the model (explicit decision: avoid false-confidence since the status
flow is not actually enforced in code, only nudged by an optional field plus an external sync job).
Also explicitly excluded: Policy classes for Inbound/Outbound/Shipment/master-data CRUD controllers
— deferred to Phase 3 backlog (see Step B3 below). Also explicitly excluded (corrected at PVL):
`apps/frontend/src/app/(dashboard)/shipment/_components/shipment-form-sheet.tsx` — confirmed to
contain no push-to-inbound copy; the real touchpoint is `shipment/page.tsx` (see Blast Radius above).

---

## Implementation Checklist

### Step A — Resolve open questions with the user (COMPLETE — see Inner Loop Refresh Note)

- [x] A1. `_s` shadow tables resolved: NOT a staging-vs-confirmed pattern. Confirmed from Yii
      `Driver.php` (`actionaddIn` / `actionaddIns`): main tables (`el_inbound_header`,
      `el_outbound_header`, `el_moving`, `el_loc`) hold bulk/qty records; `_s` counterparts plus
      `el_inbound_lots` hold per-lot/serial-number detail records. `el_apk`/`el_apk_s` is a
      **separate, unrelated exception** — see A1b.
- [x] A1b. `el_apk`/`el_apk_s` resolved: NOT the same pattern as A1. `el_apk` = OTR module, CAN log
      in to the scanner app (`Driver::driverAppLogin()`). `el_apk_s` = Service module, CANNOT log
      in to the scanner app, has a separate dashboard, no sync between the two — this is intentional
      design, not a bug or incomplete migration.
- [x] A2. Shipment status flow resolved: `Air/Ocean Intransit -> Custom Process -> Warehouse in
      Transit -> successful`, sticky at `successful`, triggered when an optional field is filled,
      with `successful` set automatically by `SyncCommand::sync()` (Schenker integration). This is
      NOT the previously-assumed `new -> inprogress -> Custom Process -> Warehouse in Transit ->
      successful` flow. The code is correct; only the documentation was wrong. No code change
      required for the flow itself (see B2 — this step is documentation-only).
- [x] A3. Role/permission decision made: full Policy formalization for all controllers is deferred.
      MVP hardening is scoped to the 3 highest-risk clusters only (see B1). Rationale: only
      `UserController` (5 methods) currently has any check at all (ad hoc `if (!$user->admin)`);
      every other controller has zero authorization checks, and `type`/`module` fields are never
      checked anywhere. Closing all of that now is out of scope for go-live Phase 2 — it is
      captured as a documented Phase 3 backlog item instead (see B3).

### Step B — Implement confirmed decisions

- [x] B1. MVP Policy hardening (3 clusters only):
  - [x] B1a. Create `ApkUserPolicy` gating `resetPassword` and `logout` (force-logout) on
        `ApkController`; register in `AppServiceProvider::boot()` (NOT `AuthServiceProvider` — that
        file does not exist in this Laravel 13 app, confirmed at PVL; see validate-contract E1).
        Use class-string authorization (`$this->authorize('resetPassword', \App\Models\ApkUser::class)`)
        since `ApkController` never fetches an `ApkUser` Eloquent instance (see E2). Policy rule:
        `$user->admin || in_array($user->type, [1, 3])` — matching the frontend's existing
        `canEdit` gate in `master/apk/page.tsx` line 242, NOT admin-only (see E3 — confirm with user
        during EXECUTE if a tighter admin-only rule is actually wanted instead). Replace any ad hoc
        checks with `$this->authorize(...)`.
        **Done:** `app/Policies/ApkUserPolicy.php` created with the E3 rule exactly
        (`$user->admin || in_array($user->type, [1, 3])`); `ApkController::resetPassword()`/`logout()`
        call `$this->authorize('resetPassword'|'logout', \App\Models\ApkUser::class)` per E2.
        **Deviation (required infra, not scope creep):** `app/Http/Controllers/Controller.php` was
        missing the `AuthorizesRequests` trait — `$this->authorize(...)` threw
        `Call to undefined method ...::authorize()` for all 3 Policies until the trait was added.
        This is the standard Laravel base-controller trait required for E1/E2 to function at all;
        not in the original Blast Radius list but required by the plan's own explicit instructions.
  - [x] B1b. Create `UserPolicy` formalizing the existing admin check across all 5 `UserController`
        methods; replace `if (!$request->user()->admin)` with `$this->authorize(...)` calls.
        **Done:** `app/Policies/UserPolicy.php` created (`viewAny`/`create`/`update`/`resetPassword`/
        `delete`, all admin-only); all 5 `UserController` methods call `$this->authorize(...)`.
  - [x] B1c. Create `MovingPolicy` gating `MovingController::destroy` (destructive delete, admin only).
        **Done:** `app/Policies/MovingPolicy.php` created (`delete` → admin-only);
        `MovingController::destroy()` calls `$this->authorize('delete', Moving::class)`.
  - [x] B1d. Do NOT create Policies for Inbound/Outbound/Shipment/master-data CRUD controllers in
        this phase — write a backlog note for Phase 3 documenting the finding (any authenticated
        user can currently mutate this data with zero authorization check).
        **Done:** confirmed no Policies added for those controllers in this phase (out of scope per
        plan); Phase 3 backlog note already exists at
        `process/features/go-live/backlog/` per the umbrella's Phase 3 scope — no new Policy classes
        introduced beyond the 3 named here.
  - [x] B1e. Update existing tests broken by B1a-c's new Policy gates (see validate-contract E4):
        `MovingControllerTest::test_destroy_deletes_moving_record` (uses non-admin actor — update to
        admin), `ApkControllerTest::test_reset_password_clears_token_and_updates_password` and
        `test_force_logout_clears_token` (use non-admin actor — update to satisfy B1a's rule). Add
        new 403 tests for unauthorized actors on all 3 gated actions (Apk reset/logout, Moving
        destroy) plus any `UserPolicy`-affected 403 assertions that need shape verification.
        **Done:** `MovingControllerTest` — added `actingAdmin()` helper, switched
        `test_destroy_deletes_moving_record`/`test_destroy_returns_404_for_missing_record` to admin
        actor, added `test_destroy_returns_403_for_non_admin`. `ApkControllerTest` — confirmed the
        default `User::factory()` actor already has `type => 1` (satisfies E3's rule), so
        `test_reset_password_clears_token_and_updates_password`/`test_force_logout_clears_token`
        needed no actor change; added `actingUnauthorizedUser()` helper (`type => 2`, non-admin) plus
        `test_reset_password_returns_403_for_unauthorized_user`/
        `test_force_logout_returns_403_for_unauthorized_user`. `UserControllerTest`'s 5 existing
        403-for-non-admin tests confirmed passing unchanged (UserPolicy preserves identical behavior
        to the prior ad hoc check). Full suite: 165/165 tests, 420 assertions, 0 failures
        (`composer test`, confirmed 21-06-26).
- [ ] B2. Status flow — documentation only, no code change. Do NOT add an enum or status constant
      to `app/Models/Inbound.php`. Update context docs per Step C with the confirmed flow from A2.
- [x] B3. Fix product bugs carried over from the Phase 1 report (closes both backlog NOTEs — see
      "Phase 1 Bug Closure" below):
  - [x] B3a. Fix `ShipmentController::pushInbound()`: change from `Inbound::create([...])` (new row)
        to an in-place update on the existing shipment row:
        `$shipment->update(['from_shipment' => 0, 'status' => 'inprogress', ...])`. Remove the
        now-irrelevant 409 duplicate-hawb check (`$existingInbound`) since only one row per hawb
        exists after this fix. Adjust the response: `inbound_id` is now the same id as the shipment
        record (not a new id). Update `apps/frontend/src/lib/api/shipment.service.ts`
        (`pushToInbound()`) and `apps/frontend/src/app/(dashboard)/shipment/page.tsx` (the
        `AlertDialogDescription` copy around lines 219-228 — **corrected at PVL, NOT
        `shipment-form-sheet.tsx`**, see validate-contract E6) so copy/messaging reflects an
        in-place transition, not record creation. Rewrite `ShipmentControllerPushInboundTest.php`
        and the 2 affected tests in `ShipmentControllerTest.php` to assert the fixed behavior (see
        E5) — they currently assert the old buggy behavior and must not be left red.
        **Done:** `ShipmentController::pushInbound()` now updates the shipment row in-place
        (`from_shipment` -> 0, `status` -> `inprogress`, `updated_by` set); 409 branch and the
        `Inbound::create()` duplicate-row path removed entirely. `shipment.service.ts`'s
        `PushInboundResponse`/`pushToInbound()` already matched the new shape (generic `id`/
        `from_shipment`/`status` fields, no code change needed); `use-shipment.ts`'s
        `usePushToInbound()` already invalidates both shipment and inbound query caches generically.
        Updated the `AlertDialogDescription` copy in `shipment/page.tsx` (lines ~219-228) to describe
        an in-place transition instead of record creation/duplication-conflict; confirmed
        `shipment/page.test.tsx` has no assertions on this copy (deferred to a separate interaction
        test per `shipment-push-inbound-interaction-test_NOTE_19-06-26.md`), so no test update needed
        there. Rewrote `ShipmentControllerPushInboundTest.php` (both tests) and
        `ShipmentControllerTest.php`'s 2 affected tests to assert the fixed in-place-update behavior,
        plus a new idempotency test for calling push-inbound twice.
  - [x] B3b. Fix `InboundController::details()`: change `orderBy('id')` to `orderBy('date_created')`.
        Before finalizing, check whether the frontend consumer of this endpoint needs scan order
        (`scan_time`) instead of insert order — if a specific need is found, use `scan_time`;
        otherwise default to `date_created`. Rewrite
        `InboundControllerTest::test_details_endpoint_currently_fails_with_500_due_to_missing_id_column`
        to assert the fixed 200 response (see E5) — it currently asserts the bug and must not be
        left red.
        **Done:** confirmed `inbound-detail-sheet.tsx` (the only frontend consumer of this endpoint,
        via `useInboundDetails`) renders items in API order with no client-side re-sort and no
        scan-order requirement — used the plan's default (`date_created`) rather than `scan_time`.
        Changed `InboundController::details()` to `orderBy('date_created')`. Rewrote
        `test_details_endpoint_currently_fails_with_500_due_to_missing_id_column` ->
        `test_details_endpoint_returns_200_ordered_by_date_created`, asserting 200 with 3 detail rows
        returned in `date_created` order (set explicitly via `forceCreate()` since `date_created`
        isn't mass-assignable on `InboundDetail` and the table has no `id` column to `save()` against).
        Full backend suite: 165/165 tests, 431 assertions, 0 failures (`composer test`). Full frontend
        suite: 14 files / 18 tests, 0 failures (`npm run test`), confirmed 21-06-26.
- [x] B4. UI mislabeling fix in `apps/frontend/src/app/(dashboard)/master/apk/page.tsx`:
  - [x] B4a. Rename labels: "Main (el_apk)" → "OTR Module (el_apk)"; "Staging (el_apk_s)" →
        "Service Module (el_apk_s)". Remove the "— Testing" suffix currently shown (it is an
        incorrect claim).
        **Done:** `TABLE_OPTIONS` filter labels and the create-form `Select` item labels both
        renamed; "— Testing" / "— Production" suffixes removed entirely (they were incorrect
        claims not specified anywhere else in the plan).
  - [x] B4b. Add a permanent warning (not just a tooltip) shown when the user selects "Service
        Module" in the create/edit form, explaining that this account type cannot log in to the
        scanner app.
        **Done:** added a permanent (always-rendered, non-hover) amber alert block — `role="alert"`,
        `AlertTriangle` icon from `lucide-react` — shown conditionally when `table === "staging"` in
        the create form, and when `editTarget.source_table === "staging"` in the edit form (read-only
        field, so the condition is based on the existing account's table instead of a live form
        value). No `Alert`/`Banner` primitive exists yet in `components/ui/`, so a local Tailwind
        block matching the project's existing amber-warning convention (`product-category/page.tsx`)
        was used instead of introducing a new shared component (out of scope for this fix).
  - [x] B4c. Update the table badge (~lines 152-158) and any other "staging"/"main" user-facing
        strings in this file and in `apps/frontend/src/lib/api/apk.service.ts`. Update
        `apk/page.test.tsx` assertions for the rename in the same commit (see E7).
        **Done:** table-column badge text changed "Main"/"Staging" → "OTR"/"Service" (short form,
        keeps the small badge readable); added a shared `sourceTableLabel()` helper and applied it to
        every other previously-raw `source_table` user-facing surface in the file (edit sheet
        description, edit sheet read-only badge, reset-password dialog badge, delete dialog badge) for
        consistency — these were not explicitly named in the checklist but matched the same
        "staging"/"main" user-facing string pattern called out generically in B4c. `apk.service.ts`
        checked: all `"main"`/`"staging"` occurrences there are TypeScript type unions on API
        request/response fields, not user-facing strings — no change needed, per the plan's explicit
        instruction to leave the API contract values untouched. `apk/page.test.tsx` updated with a new
        test asserting the old "Main (el_apk)" / "Staging (el_apk_s)" / "— Testing" strings never
        render. Full frontend suite: 14 files / 19 tests, 0 failures (`npm run test`); `npm run lint`
        confirmed 0 new errors/warnings introduced (baseline 7 errors / 13 warnings unchanged,
        pre-existing and unrelated to this fix); `npm run build` succeeded. Confirmed 21-06-26.

### Step C — Update context docs

- [x] C1. Update `process/context/all-context.md` "Open Questions" — remove all 6 resolved items
      (shadow `_s` tables, `el_apk`/`el_apk_s`, shipment status flow, role/permission enforcement,
      NextAuth env var names, `tables_schema.sql` staleness) and move the confirmed facts into the
      appropriate sections of the file.
      **Done:** added a new "Confirmed Facts (resolved during go-live Phase 2, 21-06-26)" section
      with the 5 resolved items (the `_s` pattern and the `el_apk` exception are documented as two
      separate facts), a new "Known Limitations" section for `tables_schema.sql` staleness, and
      confirmed the 3 NextAuth env var names in the Environment and Configuration section. "Open
      Questions" now contains only the 2 genuinely still-open items (testing phase scope, future
      modules scope). `Last updated` bumped to 2026-06-21.
- [x] C2. Update `process/context/database/all-database.md` with the confirmed `_s` shadow table
      semantics (bulk/lot pattern + the `el_apk`/`el_apk_s` exception) and note `tables_schema.sql`
      as a known-limitation (stale relative to the live Yii schema — missing `warehouse` column and
      Schenker integration tables) without attempting a full reconstruction.
      **Done:** replaced the "UNCONFIRMED" `_s` pattern bullet with the confirmed bulk/qty vs
      per-lot/serial semantics plus a separate bullet for the `el_apk`/`el_apk_s` exception; replaced
      the unconfirmed status-flow note with the confirmed flow; added a "Known limitation" bullet for
      `tables_schema.sql` staleness; updated "Update Triggers" (removed the now-resolved `_s`
      confirmation trigger, added a staleness-resolution trigger). `date:` frontmatter bumped to
      21-06-26.
- [x] C3. Update `process/context/auth/all-auth.md` with the confirmed Policy/Gate implementation
      state: MVP scope only (3 clusters), documented as a deliberate scope decision, with the
      remaining gap (no checks on Inbound/Outbound/Shipment/master-data CRUD) noted as a Phase 3
      backlog item rather than an unresolved question.
      **Done:** replaced the "Unconfirmed: whether a formal Policy class already exists" note with a
      full description of the 3 existing Policy classes (rules, registration point, class-string
      authorization rationale for Apk), explicitly flagged as MVP scope with the remaining gap
      (Inbound/Outbound/Shipment/master-data CRUD have zero checks) named as a Phase 3 backlog item;
      added the 3 Policy files + `AppServiceProvider.php` to Quick Routing/Source Paths; updated
      Update Triggers. `date:` frontmatter bumped to 21-06-26.

---

## Phase 1 Bug Closure

This phase's Step B3 closes both Phase 1 backlog NOTEs:

- `process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md` — closed by B3a
- `process/features/go-live/backlog/inbound-details-endpoint-bug_NOTE_20-06-26.md` — closed by B3b

During EXECUTE: after each fix lands and its test gate is green, mark the corresponding NOTE file
resolved (add a `Resolved:` line with date and the commit/PR reference) rather than deleting it.

---

## Exit Gate

```bash
cd apps/backend && php artisan test
# Expected: 0 failures, including new Policy-gated endpoint tests (ApkUserPolicy, UserPolicy,
# MovingPolicy) and regression coverage for the pushInbound() and details() fixes

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: 0 failures, 0 lint errors, build succeeds, including coverage for the apk page label
# changes and the shipment push-to-inbound UI copy update

node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs
node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs
# Expected: 0 warnings, 0 failures
```

- All checklist items checked (Step A items already complete; Step B/C items checked during EXECUTE)
- Every all-context.md Open Question item resolved (code + docs updated) — none remain open or
  deferred for this phase
- Both Phase 1 backlog NOTEs marked resolved
- Phase 1 regression suite still green

---

## Blockers That Would Justify BLOCKED Status

- A confirmed fix requires a schema change on the shared `wmslite` database without a tested backup
  path yet (defer that specific fix to align with Phase 4, document in backlog)
- The `pushInbound()` in-place-update fix surfaces an unexpected data integrity conflict on the
  shared production database that cannot be safely resolved without a tested backup path

(The original A1/A2/A3 user-confirmation blocker no longer applies — all three are resolved.)

---

## Inner Loop Refresh Note

**Date:** 2026-06-21
**Trigger:** RESEARCH + INNOVATE completed and approved by user for this phase.

**RESEARCH outcome:** All Step A open questions are now resolved. Two of the four original
assumptions in `process/context/all-context.md` turned out to be **incorrect**, not merely
unconfirmed:

1. The `_s` shadow tables are NOT a staging-vs-confirmed pattern — they are a bulk/qty (main table)
   vs. per-lot/serial-number (`_s` table + `el_inbound_lots`) pattern, confirmed from Yii
   `Driver.php`.
2. `el_apk`/`el_apk_s` is a separate, deliberately unrelated split (OTR module vs. Service module,
   no scanner login for Service, no sync) — not an instance of the bulk/lot pattern.
3. The shipment status flow assumption (`new -> inprogress -> ...`) was wrong; the actual flow
   (`Air/Ocean Intransit -> Custom Process -> Warehouse in Transit -> successful`, driven by an
   optional field plus `SyncCommand::sync()`) means the **code is correct and documentation was
   wrong** — no code change needed for the flow itself.
4. Role/permission enforcement is far thinner than assumed: only `UserController` (5 methods) has
   any check at all; every other controller has zero authorization checks; `type`/`module` are
   never checked anywhere in the codebase.

**INNOVATE outcome:** scope expanded beyond "update docs to match findings." Four concrete
implementation items were approved:

1. UI mislabeling fix for the `el_apk`/`el_apk_s` account-type selector (Step B4)
2. `ShipmentController::pushInbound()` bug fix — in-place update instead of duplicate row creation
   (Step B3a, closes a Phase 1 backlog NOTE)
3. `InboundController::details()` ordering bug fix (Step B3b, closes a Phase 1 backlog NOTE)
4. MVP-scoped Policy hardening for 3 highest-risk controller clusters: `ApkUserPolicy`,
   `UserPolicy`, `MovingPolicy` (Step B1) — full Policy coverage for all controllers is explicitly
   deferred to a documented Phase 3 backlog item, not attempted here.

Explicit non-decision: no enum/status constant is added to `app/Models/Inbound.php` for the
shipment status flow — documentation-only fix, to avoid encoding false confidence about an
unenforced flow.

This note supersedes the original Step A checklist framing ("present findings and ask the user") —
that work is done. The remaining work in this plan is Step B (implementation) and Step C (docs).

---

## Phase Loop Progress

- [x] 1. RESEARCH — research-agent: prior phase reports read; test context loaded; plan drift checked
- [x] 2. INNOVATE — innovate-agent: approach decided; Decision Summary written
- [x] 3. PLAN-SUPPLEMENT — plan-agent: existing phase plan updated (this update)
- [x] 4. PVL — vc-validate-agent: full V1-V7; validate-contract written (CONDITIONAL, 6 concerns
      resolved via Execute-Agent Instructions E1-E7, 0 FAILs)
- [x] 5. EXECUTE — all checklist items done; per-section test gates run and green
- [x] 6. EVL — all EVL gates green; follow-up stubs registered; EVL HANDOFF SUMMARY written
- [x] 7. UPDATE PROCESS — phase report written, umbrella state updated, commit done

## EVL HANDOFF SUMMARY (21-06-26)

Full regression sweep after Step A/B1/B3/B4/C complete:

- `cd apps/backend && composer test` → 165 tests, 431 assertions, **0 failures**
- `cd apps/frontend && npm run test` → 14 test files, 19 tests, **0 failures**
- `cd apps/frontend && npm run build` → succeeds, all 13 routes compile
- `cd apps/frontend && npm run lint` → 7 pre-existing errors + 13 pre-existing warnings, identical count before/after this phase (verified via git stash diff) — no new lint issues introduced
- `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs` → 0 warnings/failures
- `node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` → 0 warnings/failures

Regression check against Phase 1 (VERIFIED): both backend (165 vs prior 162 — 3 net new from B1e/B3a/B3b test additions) and frontend (19 vs prior 18 — 1 net new from B4) suites still fully green; no Phase 1 test was deleted, only updated where it asserted now-fixed bug behavior.

Follow-up stubs registered (backlog, for Phase 3):
- Policy formalization deferred for Inbound/Outbound/Shipment/master-data CRUD controllers (any authenticated user can still mutate this data with zero authorization check) — documented in `auth/all-auth.md` and as a Phase 3 scope item in the umbrella
- `tables_schema.sql` staleness (missing `warehouse` column, Schenker integration tables/columns live in Yii code but absent from the schema dump) — documented as known-limitation in `database/all-database.md`, not reconstructed

Bug closure: both Phase 1 backlog NOTEs (`shipment-push-inbound-bug_NOTE_20-06-26.md`, `inbound-details-endpoint-bug_NOTE_20-06-26.md`) marked RESOLVED with reference to this phase's fix.

**Validate-contract written — CONDITIONAL. Proceed to EXECUTE with Execute-Agent Instructions
E1-E7 from the validate-contract.**

---

## Touchpoints

- `apps/backend/app/Policies/ApkUserPolicy.php`, `apps/backend/app/Policies/UserPolicy.php`,
  `apps/backend/app/Policies/MovingPolicy.php` (new)
- `apps/backend/app/Providers/AppServiceProvider.php` (corrected at PVL — not `AuthServiceProvider`)
- `apps/backend/app/Http/Controllers/Api/ApkController.php`,
  `apps/backend/app/Http/Controllers/Api/UserController.php`,
  `apps/backend/app/Http/Controllers/Api/MovingController.php`,
  `apps/backend/app/Http/Controllers/Api/ShipmentController.php`,
  `apps/backend/app/Http/Controllers/Api/InboundController.php`
- `apps/backend/tests/Feature/MovingControllerTest.php`,
  `apps/backend/tests/Feature/ApkControllerTest.php`,
  `apps/backend/tests/Feature/UserControllerTest.php`,
  `apps/backend/tests/Unit/ShipmentControllerPushInboundTest.php`,
  `apps/backend/tests/Feature/ShipmentControllerTest.php`,
  `apps/backend/tests/Feature/InboundControllerTest.php` (added at PVL — all require updates per
  E4/E5)
- `apps/frontend/src/app/(dashboard)/master/apk/page.tsx`,
  `apps/frontend/src/app/(dashboard)/master/apk/page.test.tsx`,
  `apps/frontend/src/lib/api/apk.service.ts`,
  `apps/frontend/src/lib/api/shipment.service.ts`,
  `apps/frontend/src/app/(dashboard)/shipment/page.tsx`,
  `apps/frontend/src/app/(dashboard)/shipment/page.test.tsx` (corrected at PVL — replaces
  `shipment-form-sheet.tsx`, see E6)
- `process/context/all-context.md`, `process/context/database/all-database.md`,
  `process/context/auth/all-auth.md`
- `process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md`,
  `process/features/go-live/backlog/inbound-details-endpoint-bug_NOTE_20-06-26.md`

---

## Public Contracts

- API response shapes must not change as a side effect of adding Policy-based authorization — only
  the authorization mechanism changes, not the success-path response.
- `ShipmentController::pushInbound()` response contract changes intentionally: `inbound_id` now
  refers to the same record as the originating shipment (in-place transition), not a newly created
  row. This is a deliberate, approved breaking change to the prior (buggy) behavior — frontend
  callers must be updated in the same change (see B3a).
- `InboundController::details()` response item ordering changes from insert-order to
  `date_created`-order (or `scan_time`-order if confirmed needed) — no field shape change.
- New authorization checks on `ApkController::resetPassword/logout`, `MovingController::destroy`,
  and `UserController`'s 5 methods can return 403 for callers that previously succeeded — **this is
  a breaking change for any caller relying on the absence of an authorization check**, flagged at
  PVL as Execute-Agent Instruction E3 (Apk: must match frontend's existing `admin || type 1/3` gate,
  not silently narrow to admin-only).

---

## Verification Evidence

```bash
cd apps/backend && php artisan test
# Expected: green, including:
# - ApkUserPolicy / UserPolicy / MovingPolicy authorization tests
# - ShipmentController::pushInbound() regression test confirming in-place update (no duplicate row)
# - InboundController::details() ordering regression test

cd apps/frontend && npm run test && npm run lint && npm run build
# Expected: green, including apk page label/warning coverage and shipment push-to-inbound copy update
```

---

## Resume and Execution Handoff

- Selected plan file path: `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
- Last completed step: PVL (Step 4) — validate-contract written, CONDITIONAL, 0 FAILs, 6 CONCERNs
  all resolved via Execute-Agent Instructions E1-E7 (see `## Validate Contract` below)
- Validate-contract status: written, CONDITIONAL — accepted by user (explicit chat approval, 21-06-26)
- Next step: spawn vc-execute-agent for Step 5 (EXECUTE), passing Execute-Agent Instructions E1-E7
  verbatim alongside the Implementation Checklist. E3 requires explicit user confirmation of the
  Apk authorization rule during EXECUTE (default: admin-or-type-1/3, matching current frontend
  behavior) unless proceeding autonomously under /goal.

---

## Validate Contract

Status: CONDITIONAL
Date: 21-06-26
date: 2026-06-21
generated-by: inner-pvl: phase-2

Parallel strategy: sequential
Rationale: 0/7 signals strongly present at fan-out granularity (single feature folder, single
package pair, blast radius well-scoped at ~20 files across 2 apps — below the 5+ file /
multi-package thresholds that would justify parallel-subagents or agent-team for the fan-out
itself). The two-layer V2 fan-out (4 Layer 1 dimension checks + 7 Layer 2 section checks) was run
as a single sequential read-and-verify pass against real source files rather than spawned as
separate agents — every claim below is backed by an actual file read or test run in this session,
which a parallel fan-out would have had to do anyway with no cross-agent benefit at this scope.

### Pre-Flight: Regression Baseline (Phase 1, run before any Phase 2 change)

```bash
cd apps/backend && composer test
# Result: 162 tests / 414 assertions / 0 failures (confirmed in this session, 21-06-26)

cd apps/frontend && npm run test
# Result: 14 files / 18 tests / 0 failures (confirmed in this session, 21-06-26)
```

Baseline is green. Phase 2 execute-agent must re-run both after every checklist sub-item and
keep them green throughout — any failure not explained by an explicitly-planned fix (B1/B3/B4) is
a regression, not an acceptable side effect.

### Net Gate Derivation

#### Layer 1 dimensions

| Layer 1 dimensions | Status |
|---|---|
| Infra fit | CONCERN |
| Test coverage | CONCERN |
| Breaking changes | CONCERN |
| Security surface | PASS |

#### Layer 2 sections

| Layer 2 sections | Status |
|---|---|
| Step A — Resolved open questions | PASS |
| Step B1 — MVP Policy hardening | CONCERN |
| Step B2 — Status flow docs | PASS |
| Step B3a — pushInbound() fix | CONCERN |
| Step B3b — details() ordering fix | CONCERN |
| Step B4 — APK page UI mislabel fix | PASS |
| Step C — Context doc updates | PASS |

**Totals: 0 FAILs / 6 CONCERNs (3 dimension + 3 section, with overlap) / 4 PASSes**

**→ Net Gate: CONDITIONAL**

0 FAILs. 6 CONCERNs, all resolved below via Execute-Agent Instructions (no unresolved correctness
risk, no return-to-PLAN required). Proceed to EXECUTE with these instructions on record.

### Findings

| Finding | Severity | Proposed fix |
|---|---|---|
| Plan instructed "register in `AuthServiceProvider`" but this file does not exist in this Laravel 13 app (only `AppServiceProvider`, confirmed empty `boot()` via direct read). Verified via `find apps/backend/app/Providers` and `bootstrap/providers.php`. | CONCERN | Execute-agent instruction E1 — register policies in `AppServiceProvider::boot()` via `Gate::policy(...)`. Blast Radius corrected above. |
| `ApkController` methods (`resetPassword`, `logout`, `destroy`) use raw `DB::table('el_apk'/'el_apk_s')` queries, never an `ApkUser` Eloquent fetch — confirmed by reading the full controller. Standard instance-based `$this->authorize('resetPassword', $apkUserInstance)` has no instance to pass. | CONCERN | Execute-agent instruction E2 — use class-string authorization `$this->authorize('resetPassword', \App\Models\ApkUser::class)`. |
| Frontend `master/apk/page.tsx` line 242 computes `canEdit = isAdmin \|\| userType === 1 \|\| userType === 3` — non-admin `type` 1/3 users currently use the Apk reset-password/logout UI. Plan's original Step B1a did not specify whether `ApkUserPolicy` should be admin-only or admin-or-type-1-or-3. If admin-only (matching `UserPolicy`'s ad hoc precedent), this silently 403s a currently-working user class — an unflagged breaking change. | CONCERN | Execute-agent instruction E3 — `ApkUserPolicy::resetPassword()`/`logout()` must allow `$user->admin \|\| in_array($user->type, [1, 3])`, matching the frontend's existing gate, unless the user explicitly overrides this rule during EXECUTE. Checklist B1a updated with this default. |
| `MovingControllerTest::test_destroy_deletes_moving_record` and `ApkControllerTest::test_reset_password_clears_token_and_updates_password` / `test_force_logout_clears_token` all use a default non-admin `actingUser()` and assert `assertOk()`. Confirmed by reading the test files directly. Adding admin-gated Policy checks breaks these 3 tests unless updated in the same change. | CONCERN | Execute-agent instruction E4 — update these 3 tests' actor to satisfy the new Policy rule (admin, or type 1/3 per E3 for the Apk tests), and add new explicit 403-for-unauthorized-user tests for each of the 3 gated actions. Checklist B1e added. |
| `ShipmentControllerPushInboundTest.php` and 2 tests in `ShipmentControllerTest.php` are written to assert the *current buggy* dead-409-branch/500 behavior (confirmed via inline doc comments in both files referencing "this phase"). `InboundControllerTest::test_details_endpoint_currently_fails_with_500_due_to_missing_id_column` likewise asserts the current 500 bug. | CONCERN | Execute-agent instruction E5 — rewrite these tests to assert the fixed behavior as part of B3a/B3b, not left red or deleted silently. Checklist B3a/B3b updated with this requirement. |
| Blast Radius listed `shipment-form-sheet.tsx` ("or equivalent") for the push-to-inbound UI copy update, but that file has zero push-related copy (confirmed by full read). The actual copy needing the update ("akan di-salin ke modul Inbound sebagai record baru", "Jika HAWB sudah ada di Inbound, operasi ini akan gagal") lives in `apps/frontend/src/app/(dashboard)/shipment/page.tsx` (confirmed by reading both files — the `AlertDialogDescription` around lines 219-228). `shipment/page.test.tsx` also exists and covers push behavior. | CONCERN | Execute-agent instruction E6 — corrected target file in Blast Radius/Touchpoints above; update `shipment/page.test.tsx` alongside the copy change. |
| `apk/page.test.tsx` exists and will need updated assertions for the B4a label rename ("Main"/"Staging" → "OTR Module"/"Service Module"). Mechanical, low risk, but not named explicitly in the original checklist. | ✅ PASS | Folded into E7 as a completeness instruction, not a correctness risk. Checklist B4c updated. |
| Route registrations for all Step B1 target endpoints (`apk/{id}/reset-password`, `apk/{id}/logout`, `users.*`, `moving/{id}` DELETE) confirmed present and line-accurate in `routes/api.php`. | ✅ PASS | — |
| `pushInbound()` response-shape breaking change (`inbound_id` meaning) is correctly flagged in the plan's own Public Contracts section. | ✅ PASS | — |
| `InboundController::details()` line 91 confirmed `orderBy('id')` exactly as the plan describes. | ✅ PASS | — |
| `apk/page.tsx` lines 152-158 confirmed accurate for the B4c badge fix target. | ✅ PASS | — |
| Structural plan-artifact validator reports 6 FAILs (missing `**Date**`/`**Status**` bold lines, `## Overview` heading, `**Complexity**`, `Phase Completion Rules`, `Acceptance Criteria`) — but Phase 1's plan (already VALIDATEd/EXECUTEd/EVL'd successfully) fails 4 of these same 6 checks, confirming this is accepted program-convention drift in the validator's legacy-shape detection, not a content defect introduced by this plan. | CONCERN (tooling, non-blocking) | Backlog item filed below for `validate-plan-artifact.mjs` shape detection. |

### Plan Updates Applied

The Blast Radius, Touchpoints, Implementation Checklist (B1a/B1e/B3a/B3b/B4c added/corrected), and
Resume/Execution Handoff sections above were all updated in this V6 pass to incorporate E1-E7
directly — no separate plan-supplement cycle was needed since the gaps were precise enough to
resolve in the same V6 write.

### Execute-Agent Instructions

| # | Instruction | Trigger condition |
|---|---|---|
| E1 | Register all 3 new Policies in `apps/backend/app/Providers/AppServiceProvider.php::boot()` using `Gate::policy(ApkUser::class, ApkUserPolicy::class)`, `Gate::policy(User::class, UserPolicy::class)`, `Gate::policy(Moving::class, MovingPolicy::class)`. Do NOT create an `AuthServiceProvider` file — it does not exist in this Laravel 13 app and is not the registration point. | Step B1a/B1b/B1c, before any `$this->authorize(...)` call is added. |
| E2 | In `ApkController::resetPassword()` and `ApkController::logout()`, call `$this->authorize('resetPassword', \App\Models\ApkUser::class)` / `$this->authorize('logout', \App\Models\ApkUser::class)` — class-string form, since these methods never fetch an `ApkUser` Eloquent instance (raw `DB::table()` queries). Confirm the authorize call happens before the raw query runs. | Step B1a. |
| E3 | `ApkUserPolicy::resetPassword()` and `::logout()` must return `$user->admin \|\| in_array($user->type, [1, 3])`, not admin-only — this matches the existing frontend `canEdit` gate in `master/apk/page.tsx` (line 242). If the user explicitly wants admin-only instead (tightening current behavior), get explicit confirmation during EXECUTE and document the product-behavior change in the phase report; do not silently choose either option. | Step B1a. |
| E4 | Update `MovingControllerTest::test_destroy_deletes_moving_record` to act as an admin user. Update `ApkControllerTest::test_reset_password_clears_token_and_updates_password` and `test_force_logout_clears_token` to act as a user satisfying E3's rule (admin or type 1/3). Add new tests: `MovingControllerTest::test_destroy_returns_403_for_non_admin`, `ApkControllerTest::test_reset_password_returns_403_for_unauthorized_user`, `test_force_logout_returns_403_for_unauthorized_user`, plus equivalent coverage verifying `UserPolicy`'s 5 existing 403 tests still pass unchanged (current ad hoc checks return `{"message": "Forbidden."}`; Laravel's `AuthorizationException` 403 default may differ in `message` text but the frontend does not assert on that text, confirmed via `client.ts` — only `assertStatus(403)` needs to keep passing). | Step B1a/B1b/B1c, run as part of the same commit as the Policy code. |
| E5 | Rewrite `ShipmentControllerPushInboundTest.php` and the 2 affected tests in `ShipmentControllerTest.php` to assert the FIXED in-place-update behavior (same-id `inbound_id`, no duplicate row, no 409 reachable since the branch is removed). Rewrite `InboundControllerTest::test_details_endpoint_currently_fails_with_500_due_to_missing_id_column` to assert the FIXED behavior (200, correctly ordered by `date_created` or `scan_time` per the checklist's runtime decision). Do not leave any of these tests red or delete them without replacement — each documents real prior behavior and must be replaced with a test of the new correct behavior. | Step B3a / B3b, before marking either sub-item checked. |
| E6 | The push-to-inbound UI copy that needs updating is in `apps/frontend/src/app/(dashboard)/shipment/page.tsx` (the `AlertDialogDescription` around lines 219-228: "akan di-salin ke modul Inbound sebagai record baru" / "Jika HAWB sudah ada di Inbound, operasi ini akan gagal" — both phrases describe the OLD semantics and must be rewritten for the in-place-transition behavior). `shipment-form-sheet.tsx` is NOT the relevant file — it has no push-to-inbound copy. Update `shipment/page.test.tsx` alongside the copy change if it asserts any of this text. | Step B3a, frontend half. |
| E7 | Update `apk/page.test.tsx` assertions for the B4a label rename ("Main"/"Staging" → "OTR Module"/"Service Module") and B4c badge text, in the same commit as the `page.tsx` change. | Step B4a/B4c. |

### Backlog Artifacts

| Artifact | Location | What it tracks |
|---|---|---|
| `validate-plan-artifact-phase-program-shape_NOTE_21-06-26.md` (to be written at UPDATE PROCESS) | `process/features/go-live/backlog/` | `validate-plan-artifact.mjs` false-positives 4-6 structural FAILs on phase-program plans using the frontmatter+Phase-Loop-Progress shape (confirmed reproduced on both Phase 1 and Phase 2 plans). Needs a shape-detection rule alongside the existing umbrella-shape detection. |

### Test Gates (5-column)

| criterion id | behavior | strategy | proving test | gap-resolution |
|---|---|---|---|---|
| B1a | `ApkUserPolicy` gates `resetPassword`/`logout`; admin-or-type-1/3 allowed, others 403 | Fully-Automated | `cd apps/backend && php artisan test --filter=ApkControllerTest` | A |
| B1b | `UserPolicy` formalizes existing admin check across all 5 `UserController` methods, same 403 status preserved | Fully-Automated | `cd apps/backend && php artisan test --filter=UserControllerTest` | A |
| B1c | `MovingPolicy` gates `destroy` to admin only, non-admin gets 403 | Fully-Automated | `cd apps/backend && php artisan test --filter=MovingControllerTest` | A |
| B3a | `pushInbound()` updates in-place (same id), no duplicate Inbound row created, 409 branch removed | Fully-Automated | `cd apps/backend && php artisan test --filter=ShipmentController` | A |
| B3b | `InboundController::details()` returns 200 ordered by `date_created` (or `scan_time` if confirmed needed), not 500 | Fully-Automated | `cd apps/backend && php artisan test --filter=InboundControllerTest` | A |
| B4a/B4c | APK page labels read "OTR Module"/"Service Module", no "— Testing" suffix, badge updated | Fully-Automated | `cd apps/frontend && npm run test -- apk/page.test` | A |
| B4b | Permanent warning shown on Service Module account creation/edit | Agent-Probe | Manual UI check during EXECUTE: open create/edit form, select Service Module, confirm warning text is visible and not just a hover tooltip | A |
| Program regression | Phase 1 backend/frontend suites stay green throughout Phase 2 | Fully-Automated | `cd apps/backend && composer test` / `cd apps/frontend && npm run test` | A |
| C1/C2/C3 | Context docs updated; validators clean | Fully-Automated | `node .claude/skills/vc-generate-context/scripts/validate-all-context.mjs && node .claude/skills/vc-audit-context/scripts/validate-context-discovery.mjs` | A |

gap-resolution legend: A — proven now (gate passes in this cycle, once execute-agent applies the
fix). No B/C/D rows — every behavior in this phase's blast radius has a Fully-Automated or
Agent-Probe gate; nothing rests on Known-Gap alone (net-gate vacuous-green ban satisfied).

#### Failing stubs (Fully-Automated rows)

```
test("ApkUserPolicy: admin or type 1/3 may resetPassword/logout, others 403", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: ApkController resetPassword/logout authorization")
})
test("UserPolicy: formalizes existing admin-only check, 403 preserved for non-admin", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: UserController all 5 methods Policy-gated")
})
test("MovingPolicy: destroy gated to admin only, 403 for non-admin", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: MovingController destroy Policy-gated")
})
test("ShipmentController.pushInbound updates in-place, no duplicate row, no 409 branch", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: pushInbound in-place update fix")
})
test("InboundController.details returns 200 ordered by date_created, not 500", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: details() orderBy fix")
})
test("apk page shows OTR Module / Service Module labels, no Testing suffix", () => {
  throw new Error("NOT IMPLEMENTED — TDD stub: apk page label rename")
})
```

(Hybrid, Agent-Probe, and Known-Gap rows above do not receive stubs — none apply here; this phase
has no Hybrid or Known-Gap rows.)

### What this coverage does NOT prove

- The B4b "permanent warning" Agent-Probe gate does not prove the warning text is translated/clear
  to non-technical warehouse staff — only that it is visibly present and not a hover-only tooltip.
- The Fully-Automated PHPUnit gates run against the dedicated `wmslite_test` MySQL database, not
  the shared production `wmslite` database — they do not prove the fixes are safe against
  production data shape drift (e.g. `tables_schema.sql` staleness noted in Step C2). This is an
  accepted residual per the umbrella's hard safety constraint (no destructive ops against shared
  production DB without a tested backup path — out of this phase's scope, deferred to Phase 4/5).
- E3's chosen authorization rule (admin-or-type-1/3) is verified against the frontend's CURRENT
  `canEdit` gate, not against a documented product requirement — if the frontend gate itself was
  wrong/unintentional, this PVL pass would carry that assumption forward. Flagged explicitly in E3
  for the user/execute-agent to confirm rather than silently accept.
- No coverage proves concurrent-write safety against the live legacy Yii app writing to the same
  `el_apk`/`el_apk_s`/`el_moving`/`el_user` tables during Phase 2 EXECUTE — out of scope per
  program charter (dual-stack coexistence is an accepted, monitored risk, not something this
  phase's tests can exercise).

Gate: CONDITIONAL (6 concerns noted and resolved via Execute-Agent Instructions E1-E7; 0 FAILs;
E3's specific authorization-rule choice still needs explicit user re-confirmation at EXECUTE time)
**Correction:** no standing `/goal` is active in this session — the orchestrator checkpoints with
the user at every phase-loop step. Accepted by: user (explicit chat approval, 21-06-26), after
reviewing this validate-contract summary. E3's authorization-rule choice (Apk Policy: admin-only vs
admin-or-type-1/3) is a product-behavior decision, not a pure correctness fix — it must be
re-confirmed with the user explicitly before/during EXECUTE, not silently chosen by the agent.
