---
name: note:composer-php-constraint-stale
description: "apps/backend/composer.json declares php ^8.3 but composer.lock resolves to >=8.4 — constraint string is stale"
date: 23-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: `composer.json` PHP Constraint Stale

**Found during:** go-live Phase 4 (Deployment Readiness), Step 1e EVL — real GitHub Actions CI run
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`

---

## Problem

`apps/backend/composer.json` declares `"php": "^8.3"`, but `apps/backend/composer.lock` has already
resolved dependencies that actually require PHP >=8.4. This was invisible locally (the dev
environment already runs PHP 8.5) and was only surfaced when CI pinned its runner to the
`composer.json`-declared 8.3 and the first real GitHub Actions run (`28008703575`) failed on it.

The immediate symptom was fixed by bumping the CI runner's PHP version to 8.5 (matching the actual
dev environment) — that unblocked Phase 4's CI exit gate. The underlying stale constraint string in
`composer.json` itself was not corrected, since doing so is outside Phase 4's docs/CI-only blast
radius.

## Recommendation

In a future small cleanup pass, update `apps/backend/composer.json`'s `"php"` constraint to
accurately reflect the actual minimum PHP version required by the resolved dependency tree (likely
`^8.4` or `^8.5`), then run `composer update --lock` (or equivalent) to confirm `composer.lock`
stays consistent. Low risk, no behavior change — pure metadata correctness.

## Related

- `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
  — Step 1e EVL HANDOFF SUMMARY, "New finding for backlog"
- `.github/workflows/ci.yml` — CI currently pinned to PHP 8.5 to work around this
