# go-live

<!-- Part of WMS Lite -->

## Scope

Cross-cutting program that takes WMS Lite from "all 9 modules functionally verified locally" to
production-ready ("siap pakai"): real test coverage, data-model/auth hardening, security audit,
deployment readiness, and a Legacy Yii cutover plan. Tracked as a 5-phase program — see the
umbrella plan in `active/go-live_19-06-26/`.

Explicitly out of scope: building the actual APK Scanner mobile/scanner app (only its
account-management surface exists today), and any new product modules beyond the current 9.

## Key Source Files

- `process/features/go-live/active/go-live_19-06-26/go-live-umbrella_PLAN_19-06-26.md` — umbrella plan + Program Goal Charter
- `process/features/go-live/active/go-live_19-06-26/phase-01-test-infrastructure_PLAN_19-06-26.md`
- `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`
- `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
- `process/features/go-live/active/go-live_19-06-26/phase-05-yii-cutover_PLAN_19-06-26.md`

## Related Context

- `process/context/all-context.md` — Open Questions section feeds Phase 2
- `process/context/database/all-database.md`
- `process/context/auth/all-auth.md`
- `process/context/tests/all-tests.md`
- `API_INVENTORY.md` (repo root) — feeds Phase 5

## Current Status

Status: in-progress (Phase 1 of 5 — not yet started, plan freshly created)

## Folder Contents

```
process/features/go-live/
  active/       -- in-progress plans for this feature (task-folder convention)
  completed/    -- archived completed plans
  backlog/      -- deferred/future plans (e.g. APK Scanner app integration)
```
