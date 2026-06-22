# WMS Lite - All Context

Last updated: 2026-06-21

This file is the root context entrypoint for the repo.

Use it for two things:

1. quick routing to the right context pack or root file
2. broad architecture and repository understanding

Start here before loading deeper context files.

---

## About This Project

WMS Lite is a Warehouse Management System for tracking inbound, outbound, shipment, and inventory operations. It is used internally by warehouse staff, runs at medium scale, and is built solo by Isnaeni Hidayat.

The project is mid-migration from a legacy Yii 1.x monolith to a modern Next.js (frontend) + Laravel (backend) stack. The legacy Yii app (`protected/`, `yiiframework/`, root-level `*.php` entrypoints) still runs in parallel against the same MySQL database while the new stack is built out module by module.

Modules considered final for this phase: Inbound, Outbound, Shipment, Moving, Monitoring, Master Data, Admin/User Management, Reports, Dashboard, and APK Scanner Account Management. Additional modules will be scoped later.

APK Scanner is a **separate** physical/mobile scanner application that will connect to the backend via API. Only its account-management surface (CRUD, password reset, force logout) has been built so far on the backend/frontend side — the scanner app itself and its operational API surface do not exist yet.

Testing has not started yet across either app. The plan is to begin testing before continuing to additional modules. Deployment is local-only today: there is no staging or production environment, and no CI/CD or Docker setup.

---

## How This File Works (the `all-*.md` Convention)

Every `process/context/` directory has one `all-*.md` entrypoint that acts as an attachable quick router for that domain. This root file (`all-context.md`) is the top-level router. Context groups each have their own `all-{group}.md` entrypoint.

**The pattern:**

```
process/context/
  all-context.md                      <-- THIS FILE: root router
  planning/
    all-planning.md                   <-- group router for planning
    example-simple-prd.md             <-- deep doc within the group
    example-complex-prd.md            <-- deep doc within the group
  tests/
    all-tests.md                      <-- group router for tests
    debugging-and-pitfalls.md         <-- deep doc within the group
    e2e-tests.md                      <-- deep doc within the group
  database/
    all-database.md                   <-- group router for database
    schema-guide.md                   <-- deep doc within the group
    migration-procedures.md           <-- deep doc within the group
```

**How agents use it:**

1. Agent reads `all-context.md` first (this file)
2. Finds the relevant context group from the routing tables below
3. Reads that group's `all-{group}.md` entrypoint
4. Only then loads the specific deep doc needed

This layered routing keeps context windows small. Never load the whole `process/context/` tree.

**What each `all-{group}.md` must contain:**

- Scope (what the group covers and does NOT cover)
- Read-when rules (when an agent should load this group)
- Quick procedures or decision rules
- Source paths (list of deeper docs in the group)
- Update triggers (when to refresh this group's content)
- Routing to deeper docs within the group

---

## Quick Start

For most substantial tasks:

1. read this file first
2. choose the smallest relevant root file or context group from the tables below
3. only then load deeper files

---

## Current Root Entry Points

<!-- The two tables below (Root Entry Points + Context Groups) are GENERATED from each
     context doc's frontmatter by `discover-context.mjs --emit-routing`. Do NOT hand-edit
     between the GENERATED markers — your edits will be overwritten on the next rebuild.
     To change a row, edit the owning doc's frontmatter (description / keywords) and re-emit.
     `--check-routing` fails lint if this block drifts from the frontmatter on disk. -->

<!-- GENERATED:routing -->
| File | Read when |
|---|---|
| `process/context/all-context.md` | any substantial planning, research, review, or implementation task |
| `process/context/auth/all-auth.md` | Dual-stack auth: NextAuth.js (frontend) + Laravel Sanctum (backend) — the Auth group entrypoint/router |
| `process/context/database/all-database.md` | MySQL legacy schema shared by Yii and Laravel during transition — the Database group entrypoint/router |
| `process/context/planning/all-planning.md` | Planning entrypoint — plan-shape calibration and SIMPLE vs COMPLEX examples for WMS Lite |
| `process/context/tests/all-tests.md` | Testing entrypoint — PHPUnit for Laravel backend against a dedicated MySQL wmslite_test database, Vitest for the Next.js frontend |

## Current Context Groups

| Group | Entry point | Scope |
|---|---|---|
| `auth/` | `process/context/auth/all-auth.md` | Dual-stack auth: NextAuth.js (frontend) + Laravel Sanctum (backend) — the Auth group entrypoint/router |
| `database/` | `process/context/database/all-database.md` | MySQL legacy schema shared by Yii and Laravel during transition — the Database group entrypoint/router |
| `planning/` | `process/context/planning/all-planning.md` | Planning entrypoint — plan-shape calibration and SIMPLE vs COMPLEX examples for WMS Lite |
| `tests/` | `process/context/tests/all-tests.md` | Testing entrypoint — PHPUnit for Laravel backend against a dedicated MySQL wmslite_test database, Vitest for the Next.js frontend |
<!-- /GENERATED:routing -->

## Task Routing Table

| Task type | Load first | Then load |
|---|---|---|
| general repo research | `all-context.md` | the relevant domain file or feature folder named by task |
| database / schema work (MySQL, shared Yii+Laravel tables) | `all-context.md`, `database/all-database.md` | the specific schema or migration doc inside the group |
| auth work (NextAuth v5 frontend, Sanctum backend, dual-auth model) | `all-context.md`, `auth/all-auth.md` | the specific provider/middleware doc inside the group |
| inbound module work | `all-context.md` | `process/features/inbound/_GUIDE.md`, then `active/` task folders |
| outbound module work | `all-context.md` | `process/features/outbound/_GUIDE.md`, then `active/` task folders |
| shipment module work | `all-context.md` | `process/features/shipment/_GUIDE.md`, then `active/` task folders |
| moving module work | `all-context.md` | `process/features/moving/_GUIDE.md`, then `active/` task folders |
| monitoring / activity log work | `all-context.md` | `process/features/monitoring/_GUIDE.md`, then `active/` task folders |
| master data work (locations, categories, recipients, apk accounts) | `all-context.md` | `process/features/master-data/_GUIDE.md`, then `active/` task folders |
| admin / user management work | `all-context.md` | `process/features/admin/_GUIDE.md`, then `active/` task folders |
| reports module work | `all-context.md` | `process/features/reports/_GUIDE.md`, then `active/` task folders |
| dashboard module work | `all-context.md` | `process/features/dashboard/_GUIDE.md`, then `active/` task folders |
| go-live readiness program (testing, hardening, deployment, Yii cutover) | `all-context.md` | `process/features/go-live/_GUIDE.md`, then the umbrella plan in `active/go-live_19-06-26/` |
| Yii-to-Laravel endpoint parity / API migration | `all-context.md` | `API_INVENTORY.md` (repo root) |
| testing or verification | `all-context.md` | `process/context/tests/all-tests.md` |
| creating a new plan | `all-context.md` | `process/context/planning/all-planning.md` |
| frontend Next.js 16 / React 19 API specifics | `all-context.md` | `apps/frontend/AGENTS.md`, then `node_modules/next/dist/docs/` before writing framework-specific code |

## Context Group Lifecycle

Context groups are durable knowledge domains, not feature folders.

Create a group when:

- a topic has 3+ durable docs
- a single doc exceeds roughly 800 lines with separable subtopics
- multiple agents repeatedly need only one slice of a large context file
- the topic maps to a stable operational domain (tests, infra, database, auth, UI, workflows, etc.)

Do not create a group when:

- the content is a temporary report
- the content is a plan or execution artifact
- the topic is feature-specific and belongs in `process/features/...`

Move or split one group at a time. Use `all-{group}.md` entrypoints. Run the `audit-context` skill after every context organization change.

## Naming Convention

There are no `README.md` files inside `process/context/`.

Canonical entrypoints use `all-*.md`:

- root: `process/context/all-context.md`
- group: `process/context/{group}/all-{group}.md`

Each `all-{group}.md` file should act as the attachable quick router for that domain:

- tell the agent what the group covers
- give quick procedures and decision rules
- route to smaller deeper files

## Context Update Protocol

When durable project knowledge changes:

1. update the smallest relevant context file
2. update this file if routing, ownership, naming, or groups changed
3. update the owning `all-{group}.md` entrypoint when a group exists
4. run `audit-context`

---

## Repository Structure

```
wmslite/
  apps/
    frontend/                -- Next.js 16.2.7 App Router (Sprint 1.x)
      src/
        app/
          (auth)/login/      -- login page (route group, no shared chrome)
          (dashboard)/        -- authenticated route group with shared layout
            admin/ dashboard/ inbound/ master/ monitoring/
            moving/ outbound/ reports/ shipment/
          api/auth/            -- NextAuth route handler
        components/
          auth/ data-table/ layout/ shared/ ui/
        hooks/
        lib/
          api/                -- one *.service.ts per module, axios-based
          auth/                -- NextAuth v5 config
          utils.ts
        stores/                -- zustand stores
        types/
        middleware.ts
      AGENTS.md                -- warns Next.js 16 / React 19 may diverge from training data
      CLAUDE.md                -- references @AGENTS.md only
    backend/                  -- Laravel 13 REST API (Sprint 2.x+)
      app/
        Http/Controllers/Api/  -- Apk, Auth, Dashboard, Inbound, Master,
                                   Monitoring, Moving, Outbound, Report,
                                   Shipment, User
        Models/                 -- ActivityLog, ApkUser, Inbound, InboundDetail,
                                   Location, Moving, Outbound, OutboundDetail,
                                   ProductCategory, Recipient, User
        Providers/
      routes/api.php           -- all routes under /api, auth:sanctum protected
      tests/
        Unit/ Feature/          -- PHPUnit, sqlite :memory:, only example tests so far
  protected/                  -- Yii 1.x legacy MVC (controllers/models/views), still live
  yiiframework/                -- Yii 1.x framework core
  phpqrcode/                   -- legacy QR code library dependency
  assets/                      -- legacy Yii frontend assets (css/js/fonts/datatables/etc)
  scripts/                     -- sprint-done.sh, auto-push.sh, generate-changelog.mjs
  *.php (root)                  -- legacy entrypoints: inbound.php, outboundx.php, sync.php, index.php, etc.
  API_INVENTORY.md              -- ~134-endpoint audit of legacy AjaxController.php mapped to target Laravel REST endpoints
  tables_schema.sql, adminer.sql -- MySQL schema/dump shared by Yii and Laravel
  process/                     -- RIPER-5 agent harness (this folder)
```

## Technology Stack

**Frontend (`apps/frontend`)**
- **Framework:** Next.js 16.2.7 (App Router)
- **UI library:** React 19.2.4 + react-dom 19.2.4
- **Language:** TypeScript (strict)
- **Styling:** Tailwind CSS v4, via shadcn/ui 4.10 + tw-animate-css
- **Auth:** next-auth 5.0.0-beta.31 (Credentials provider against Laravel/Sanctum)
- **Data fetching/cache:** @tanstack/react-query 5.101
- **Tables:** @tanstack/react-table 8.21
- **Client state:** zustand 5.0.14
- **Forms/validation:** react-hook-form 7.78 + @hookform/resolvers + zod 4.4.3
- **HTTP client:** axios 1.17
- **UI primitives:** radix-ui (avatar, checkbox, dialog, dropdown-menu, label, popover, scroll-area, select, separator, slot, switch, tabs, toast, tooltip)
- **Charts:** recharts 3.8.1
- **Misc:** lucide-react (icons), sonner (toasts), cmdk, date-fns
- **Package manager:** npm (package-lock.json, no `packageManager` field)
- **Import alias:** `@/*` -> `./src/*`
- **Test runner:** none installed yet (no vitest/jest/playwright in devDependencies, no test script in package.json)

**Backend (`apps/backend`)**
- **Framework:** Laravel ^13.8
- **Auth:** laravel/sanctum ^4.0 (token-based API auth)
- **Language/runtime:** PHP 8.3
- **Code style:** laravel/pint
- **Testing:** phpunit/phpunit ^12.5, sqlite `:memory:` for test DB, `.env.example` defaults to sqlite for local dev even though production target is MySQL shared with Yii

**Legacy (still running in parallel)**
- Yii 1.x MVC framework (`yiiframework/`, `protected/`)
- Root-level procedural PHP entrypoints (`inbound.php`, `outboundx.php`, `sync.php`, `index.php`, etc.)
- `phpqrcode` library, legacy `assets/` (css/js/datatables/fonts)

**Database**
- MySQL, shared between legacy Yii and new Laravel backend (single source of truth during migration)
- Schema files at repo root: `tables_schema.sql`, `adminer.sql`

**Tooling / repo-level**
- No root package manager / workspace tool — `apps/frontend` and `apps/backend` are managed independently (frontend via npm, backend via composer)
- No CI/CD, no Docker, no staging/production deploy target yet — local development only
- `scripts/sprint-done.sh`, `scripts/auto-push.sh`, `scripts/generate-changelog.mjs` — sprint/git helper scripts

## Key Patterns and Conventions

**Module boundary pattern (frontend):** each business module (inbound, outbound, shipment, moving, monitoring, master, admin, reports, dashboard) gets its own route segment under `src/app/(dashboard)/{module}/` and its own `*.service.ts` file under `src/lib/api/` (e.g. `inbound.service.ts`, `shipment.service.ts`), all built on top of the shared axios instance in `src/lib/api/client.ts`.

**Route groups:** `(auth)` holds the login page with no shared dashboard chrome; `(dashboard)` wraps all authenticated modules with a shared `layout.tsx`.

**Auth flow (frontend):** NextAuth v5 `CredentialsProvider` POSTs to `${NEXT_PUBLIC_LARAVEL_API_URL}/auth/login`, expects `{token, user}`, and stores the Sanctum token as `accessToken` inside the JWT. Session strategy is `"jwt"` with `maxAge` of 8 hours. Custom session/user fields: `user_id`, `type`, `admin`, `module`, `status` — used for role/permission gating.

**API response shape (target, Laravel):** `{data, message, status}`.

**API response shape (legacy, Yii `AjaxController.php`, ~6951 lines):** `{code, msg, details, on_update}`. Full endpoint-by-endpoint mapping from legacy shape/endpoints to target Laravel REST endpoints lives in `API_INVENTORY.md` (~134 endpoints audited).

**Backend route conventions (Laravel):** all routes under `/api` prefix; protected routes wrapped in `auth:sanctum` middleware group; CRUD-heavy resources use `Route::apiResource` (e.g. `shipments`, `inbound`, `outbound`); one-off actions use explicit verbs nested under the resource (e.g. `shipments/{id}/push-inbound`, `inbound/{id}/details`); read-only domains expose only `index` (e.g. `monitoring`, an append-only activity log); admin/master CRUD lives under `admin/users/*` and `master/*` (locations, categories, recipients, plus full apk-account CRUD + reset-password + logout).

**Controller/model naming:** one controller per module under `app/Http/Controllers/Api/` named `{Module}Controller.php`; matching Eloquent models under `app/Models/` (e.g. `Inbound`, `InboundDetail`, `Outbound`, `OutboundDetail`, `Moving`, `Location`, `ProductCategory`, `Recipient`, `ApkUser`, `User`, `ActivityLog`).

**Dual-stack coexistence:** the Yii legacy app and the new Next.js/Laravel stack run against the same MySQL database simultaneously during migration — schema changes must stay compatible with both until the legacy app is retired module by module.

**Frontend framework caution:** `apps/frontend/AGENTS.md` explicitly warns that Next.js 16 and React 19 introduce breaking changes relative to the model's training data; agents must check `node_modules/next/dist/docs/` before writing code against specific Next.js APIs. `apps/frontend/CLAUDE.md` only contains a reference to `@AGENTS.md` — no separate content.

## Environment and Configuration

**Config files:** `apps/frontend/.env*` (Next.js env files, git-ignored), `apps/backend/.env` / `.env.example` (Laravel), `tsconfig.json` (frontend path aliases), `phpunit.xml` (backend test config).

**Env var groups (names only, never values) — confirmed against `apps/frontend/src/lib/auth/auth.ts` and local `.env` during go-live Phase 2 RESEARCH (21-06-26):**
- Frontend → Backend API: `NEXT_PUBLIC_LARAVEL_API_URL`
- Frontend auth (NextAuth v5): `NEXTAUTH_SECRET`, `NEXTAUTH_URL` — confirmed
- Backend database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (defaults to sqlite in `.env.example`; production target is the shared MySQL instance)
- Backend app/framework: standard Laravel `APP_*` vars (`APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`)
- Backend auth: Sanctum-related config in `config/sanctum.php` (stateful domains, token expiration) rather than dedicated env vars beyond the standard Laravel set

## Confirmed Facts (resolved during go-live Phase 2, 21-06-26)

These were previously logged as "Open Questions" during the initial harness STUDY pass and have
since been resolved via RESEARCH + INNOVATE + EXECUTE in `process/features/go-live/active/go-live_19-06-26/phase-02-data-model-auth-hardening_PLAN_19-06-26.md`:

- **Shadow `_s` tables — bulk/qty vs per-lot/serial pattern, NOT staging-vs-confirmed.** Confirmed
  from Yii `Driver.php` (`actionaddIn` / `actionaddIns`): main tables (`el_inbound_header`,
  `el_outbound_header`, `el_moving`, `el_loc`) hold bulk/qty records; their `_s` counterparts plus
  `el_inbound_lots` hold per-lot/serial-number detail records. See `database/all-database.md` for
  the full pattern.
- **`el_apk`/`el_apk_s` — a separate, unrelated exception, not an instance of the `_s` pattern
  above.** `el_apk` = OTR module, accounts CAN log in to the scanner app
  (`Driver::driverAppLogin()`). `el_apk_s` = Service module, accounts CANNOT log in to the scanner
  app, has its own separate dashboard, and there is no sync between the two tables. This is
  intentional design, not a bug or an incomplete migration.
- **Shipment status flow — confirmed, and the prior assumption was wrong.** Actual flow:
  `Air/Ocean Intransit -> Custom Process -> Warehouse in Transit -> successful`, sticky at
  `successful` once reached, triggered when an optional field is filled, with `successful` set
  automatically by `SyncCommand::sync()` (Schenker integration). The previously-documented
  `new -> inprogress -> ...` flow was incorrect documentation, not a code bug — no code change was
  needed for the flow itself.
- **Role/permission enforcement — extended in go-live Phase 3; residual gap is narrower but still
  real.** As of Phase 3 (22-06-26), Laravel Policy classes also gate `update`/`destroy` on
  `InboundController`, `OutboundController`, `ShipmentController` (`InboundPolicy`/
  `OutboundPolicy`/`ShipmentPolicy`, admin-only) and `store` on `MovingController`
  (`MovingPolicy`), on top of Phase 2's `ApkUserPolicy`/`UserPolicy`/`MovingPolicy::destroy`
  baseline. `ApkUserPolicy` also gained `store`/`update`/`delete`. **Remaining gap:**
  `store`/`index`/`show` on Inbound/Outbound/Shipment, and ALL methods on every master-data CRUD
  controller (locations, categories, recipients), still have zero authorization checks — any
  authenticated user can currently create or read that data regardless of `type`/`admin`/`module`.
  See `process/features/go-live/backlog/crud-store-read-authz_NOTE_22-06-26.md` — deferred pending
  a product decision on who may create/read records per module, not something resolved or actively
  planned without checking the backlog first. See `auth/all-auth.md` for the full Policy inventory.
- **NextAuth env var names** — confirmed: `NEXTAUTH_URL`, `NEXTAUTH_SECRET`, and
  `NEXT_PUBLIC_LARAVEL_API_URL` are the real, verified env var names used by
  `apps/frontend/src/lib/auth/auth.ts`.

## Known Limitations

- **`tables_schema.sql` is stale relative to the live Yii codebase.** Confirmed during go-live
  Phase 2 RESEARCH: the live Yii code references at least one column (`warehouse`) and a set of
  Schenker integration tables that are not present in `tables_schema.sql`. Do not treat
  `tables_schema.sql`/`adminer.sql` as a complete or current schema reference — verify any
  assumption about a specific table or column against the live Yii code (`protected/`) or a live
  database inspection instead of the schema dump alone. No full reconstruction of the schema dump
  has been attempted; this is logged as an accepted limitation, not a task to complete.

## Open Questions / Outstanding Work

- **Testing phase**: user has stated intent to start testing (frontend + backend) before adding
  further modules — no concrete plan or scope decided yet.
- **Future modules**: scope beyond the current 9 modules is intentionally undecided ("akan
  ditentukan nanti").

## Scan Metadata

- Generated: 2026-06-18T00:00:00+07:00
- Repo HEAD (commit): 0afd74a8c2ee8a6dd33e91baeaf03f899a6697e0
- Mode: fresh
- Package manager: npm (frontend); composer (backend) — no root-level package manager or workspace tool; `apps/frontend` and `apps/backend` are managed independently
