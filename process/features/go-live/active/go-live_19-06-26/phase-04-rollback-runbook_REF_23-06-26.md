---
name: ref:go-live-phase-04-rollback-runbook
description: "Code rollback runbook for WMS Lite — git tag + restart strategy, documentation only"
date: 23-06-26
metadata:
  node_type: memory
  type: reference
  feature: go-live
  phase: phase-04
---

# Code Rollback Runbook — WMS Lite

**Status:** Documentation only — no execution performed or required this phase. There is no live
deployment yet (no VPS provisioned), so there is nothing to roll back from. Execution proof is
deferred to whichever Tier 2 phase performs the first real deploy (see
`process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`).

---

## Strategy: Git Tag Per Release + Process Manager Restart

The core rollback strategy is: **every deploy is tagged in git, and rolling back means checking
out the previous tag and restarting the running process(es) against it.** This is intentionally
generic — the exact VPS, process manager, and topology are not decided yet (no VPS exists).

### 1. Tag every release

Before (or as part of) every deploy, create an annotated git tag marking exactly what was
deployed:

```bash
git tag -a v<version> -m "Deploy <version> — <short description>"
git push origin v<version>
```

Use a consistent, sortable version scheme (e.g. semantic versioning `v1.2.3`, or a date-based
scheme `v2026.06.23-1`) — the exact convention can be decided when the first real deploy happens
(Tier 2).

### 2. Keep the previous tag's build artifact reachable

Whatever the deploy mechanism turns out to be (Tier 2, undecided), the previous tag's deployable
artifact must remain reachable for a fast rollback, not just the git tag itself. Two viable
approaches, to be chosen once a real deploy mechanism exists:

- **Source-checkout-based:** the previous tag's commit is checked out directly on the host
  (`git checkout v<previous-version>`), dependencies reinstalled (`composer install` /
  `npm ci && npm run build`), and the process manager restarted against the rebuilt artifact. This
  is simplest with no extra infrastructure, but rebuild time adds to rollback time.
- **Pre-built-artifact-based:** each deploy keeps the previous build's compiled artifact (Laravel:
  vendor + cached config; Next.js: `.next` build output) archived on the host or in object storage,
  so rollback is a swap-and-restart rather than a rebuild. Faster rollback, but requires deciding a
  storage/retention policy for old artifacts (Tier 2).

Until a VPS exists, neither approach is implemented — this section documents the decision space
for whichever Tier 2 phase implements the first real deploy.

### 3. Restart/redeploy steps to revert to the previous tag

Generic steps (exact commands depend on the eventual process manager — PM2, systemd, or a
container orchestrator — none chosen yet):

**Backend (`apps/backend`, Laravel):**

```bash
# 1. Stop the running backend process
#    (PM2 example: pm2 stop wmslite-backend)
#    (systemd example: systemctl stop wmslite-backend)

# 2. Checkout the previous tag
git checkout v<previous-version>

# 3. Reinstall dependencies matching that tag (if source-checkout-based rollback)
composer install --no-dev --optimize-autoloader

# 4. Re-run any required Laravel cache/config commands
php artisan config:cache
php artisan route:cache

# 5. Restart the process
#    (PM2 example: pm2 start wmslite-backend)
#    (systemd example: systemctl start wmslite-backend)
```

**Frontend (`apps/frontend`, Next.js):**

```bash
# 1. Stop the running frontend process

# 2. Checkout the previous tag
git checkout v<previous-version>

# 3. Reinstall dependencies and rebuild (if source-checkout-based rollback)
npm ci
npm run build

# 4. Restart the process
```

### 4. Process manager options (undecided, Tier 2)

The exact process manager is not chosen yet because no VPS exists. Documented options for when
that decision happens:

- **PM2** — Node.js-native process manager, commonly used for Next.js apps; can also manage
  non-Node processes via custom scripts. Supports `pm2 reload` for zero-downtime restarts and
  `pm2 save`/`pm2 resurrect` for persistence across host reboots.
- **systemd** — OS-native service manager (Linux), works for both the Laravel (PHP-FPM or a PHP
  built-in server process) and Next.js processes via unit files. No extra dependency to install,
  but less ergonomic for Node-specific process management (log rotation, cluster mode) than PM2.

### 5. Container image versioning (future option, if Docker is adopted)

If the eventual VPS deployment adopts Docker (not decided — there is no Docker setup in this repo
today per `process/context/all-context.md`), the rollback strategy would shift to **image tag
versioning** instead of git tag + rebuild:

- Each deploy builds and pushes a tagged image (`wmslite-backend:v1.2.3`,
  `wmslite-frontend:v1.2.3`) to a registry.
- Rollback becomes: `docker stop <container>`, then re-run with the previous image tag
  (`docker run wmslite-backend:v1.2.2 ...` or the equivalent `docker compose` /orchestrator
  command), instead of a git checkout + rebuild.
- This is generally faster and more reproducible than source-checkout rollback (no dependency
  reinstall step, no build-tooling-version drift risk), at the cost of needing a container
  registry and Docker tooling, which this repo does not have yet.
- This option is documented here for completeness; adopting Docker is itself an undecided,
  separate decision tracked in the Tier 2 backlog, not something this phase commits to.

---

## Explicit Statement: No Rollback Executed This Phase

**No rollback was executed during Phase 4.** This is a documentation-only deliverable. There is no
live deployment of the new Next.js/Laravel stack anywhere (no VPS provisioned yet — see the Phase
4 2-tier scope decision), so there is nothing to roll back from, and no rollback execution is
possible or required to satisfy this phase's exit gate.

**Execution proof is deferred** to whichever Tier 2 phase performs the first real deploy to a
provisioned VPS — at that point, an actual rollback (reverting from one real deployed version to
the previous one) should be exercised at least once to validate this documented strategy in
practice, not just on paper. This is tracked in
`process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`.

---

## References

- Phase 4 plan: `process/features/go-live/active/go-live_19-06-26/phase-04-deployment-readiness_PLAN_19-06-26.md`
- Tier 2 backlog (real deploy, CD pipeline, executed rollback):
  `process/features/go-live/backlog/vps-deploy-cd-pipeline_NOTE_23-06-26.md`
