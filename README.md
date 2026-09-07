# Marixion Revenue Operating Agent

Internal revenue-operations platform for Marixion Global Technologies. Laravel/PHP
is the primary application and commercial-control plane; AI assists with research,
classification, extraction and drafting, but every commercial rule (pricing,
approvals, workflow, payment) is enforced by deterministic Laravel code. See the
execution plan ("Marixion Revenue Operating Agent — Execution & Technical
Architecture Plan, Laravel/PHP Edition, v2.0") for the full business design.

This repository currently implements **Phase 1 (Bootstrap)** and **Phase 2
(Control Plane)** only. Prospect Intelligence, Outreach, Document Intelligence,
Pricing/Proposal, and Payment/Handoff are not built yet — see
[Remaining Work](#remaining-work).

## Stack

| Layer | Choice |
|---|---|
| Application | Laravel 13 / PHP 8.3 |
| Internal UI | Blade + Livewire 4 |
| Database | PostgreSQL (source of truth) |
| Queues | Laravel Queues + Redis (via `predis`) |
| Queue monitoring | Laravel Horizon |
| RBAC | `spatie/laravel-permission` (roles) + Laravel Policies (enforcement) |
| Auth scaffolding | Laravel Breeze (Blade stack) |
| AI | Behind `App\AI\AIProviderInterface`; unconfigured (`NullAIProvider`) until Phase 3 |
| CRM | HubSpot remains system of record; behind `App\Integrations\Hubspot\HubspotClientInterface`, unconfigured until Phase 3+ |

## Local setup

### Option A — Docker (matches the target architecture: Postgres + Redis)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

App: http://localhost:8000 · Horizon: http://localhost:8000/horizon (Admin /
Founder-Management only).

### Option B — Native PHP, no Docker

Requires PHP 8.3+, Composer, Node 18+. No local PostgreSQL/Redis server is assumed;
`.env` defaults to SQLite + the `database` queue/session/cache drivers so the app
runs standalone. Swap `.env` to match `.env.example` (pgsql + redis) once those
services are available.

```bash
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Demo accounts (seeded by `RolesAndPermissionsSeeder` / `DatabaseSeeder`,
password `password` for all): `admin@marixion.test` (Admin),
`founder@marixion.test` (Founder/Management), `sales.manager@marixion.test`
(Sales Manager), `sales@marixion.test` (Sales), `technical@marixion.test`
(Technical/Delivery), `finance@marixion.test` (Finance).

### Granting access to a new self-registered user

Breeze's `/register` is open, but registering does **not** grant a role —
an account with no role can sign in but sees a friendly "Access pending"
page instead of the Control Plane (see `resources/views/errors/403.blade.php`).
An administrator grants access with:

```bash
php artisan users:assign-role user@example.com "Sales"
```

Valid roles are the six in `App\Enums\RoleName`. This is a deliberate,
minimal stand-in for the "Administration" module the execution plan defers
to a later phase (section 3) — auto-granting a role on public self-registration
was rejected as a silent security regression for what is meant to be an
internal tool.

### Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (`phpunit.xml`), independent of
whichever driver `.env` points at.

## Architecture notes specific to this codebase

- **`app/Workflows/OpportunityWorkflow.php`** is the only code path allowed to
  change `opportunities.stage`. `Opportunity::$fillable` deliberately excludes
  `stage`, so it cannot be set through mass assignment in a controller.
- **`app/Services/AuditLogger.php`** is the single write path for
  `audit_events`. It's an append-only table (`AuditEvent::$UPDATED_AT = null`).
- **`app/AI`, `app/Integrations/Hubspot`, `app/Integrations/Google`** contain
  interfaces plus `Null*` no-op implementations, bound in
  `IntegrationServiceProvider`. Nothing in the control plane depends on a
  concrete provider — swapping in a real OpenAI/HubSpot/Google client later
  should not require touching calling code.
- Every Company/Contact/Opportunity mutation goes through an `app/Actions/*`
  class wrapped in a DB transaction, which also writes the audit event.
- **`app/Livewire/OpportunityPipelineBoard.php`** is the pipeline board UI,
  shared by `/dashboard` and `/opportunities?view=pipeline` — one component,
  not two copies, per the plan's "Pipeline View should resemble the Dashboard
  pipeline" (section 11B).
- **`app/Services/PipelineMetrics.php`** holds every dashboard KPI
  calculation (Open Opportunities, Open Pipeline Value, Expected Revenue,
  Closed Won/Lost counts) so the math lives in one tested place, not in Blade.

## Control Plane feature coverage

- **Companies/Contacts/Opportunities**: full CRUD, search, filters,
  pagination (20/page), owner assignment, notes.
- **Duplicate prevention**: a company create/update is blocked (with a
  message naming the existing record) if the domain already exists
  (case-insensitive); a contact create/update is blocked if the same
  company already has a contact with that email.
- **Opportunities**: List View (table, filterable by stage/company/owner/
  value) and Pipeline View (the same board component as the Dashboard) via
  `/opportunities?view=pipeline`.
- **Contact detail page** (`/contacts/{contact}`): company, opportunities at
  that company, communication history/activities placeholders, audit
  history, and a disabled "Start Outreach" action (Outreach isn't built).
- Every list page has an empty state with a call to action, and every
  create/edit form disables its submit button while the request is in
  flight to prevent duplicate submissions.

## Opportunity pipeline

```
Target Account -> Prospect Identified -> Engaged Prospect -> Qualified Discovery
  -> Sales Qualified Opportunity -> Technical/Solution Discovery -> Proposal
  -> Negotiation -> Closed Won | Closed Lost
```

Rules enforced by `OpportunityWorkflow`:

- Stages only advance one step at a time — skipping is rejected.
- Any open stage can move to **Closed Lost** (a reason is required).
- **Closed Won** is only reachable from **Negotiation**.
- Only the role that owns a stage (per the execution plan's "Controlled Revenue
  Journey" table) — or Admin / Founder-Management as an override — may move an
  opportunity out of it.
- Closed stages are terminal.

## Architectural decisions made without an explicit rule in the source documents

These were necessary to ship a working control plane; they are implementation
choices, not business rules from the execution plan, and should be confirmed
or corrected by a human before Phase 3:

1. **Closed Won/Lost split into two stages** (`closed_won`, `closed_lost`).
   The plan's pipeline diagram shows one "Closed Won/Lost" node; two distinct
   terminal states were used so win/loss is unambiguous in data and reporting.
2. **Transition rules beyond strict forward progression** — "Closed Lost from
   any open stage" and "Closed Won only from Negotiation" are reasonable
   sales-process defaults, not stated verbatim in the plan.
3. **Row-level visibility on Opportunities** — the MVP shows all open
   opportunities to every internal role (matches the "one dashboard" vision in
   section 18), rather than scoping Sales users to their own deals. No
   visibility-scoping rule was specified.
4. **Company.status** (`prospecting` / `active` / `inactive` / `disqualified`)
   and **Contact.communication_status** (`not_contacted` / `contacted` /
   `engaged` / `unresponsive` / `opted_out`) enum values — the plan names
   these fields but doesn't enumerate their values.
5. **Permission list and role→permission matrix** (`RolesAndPermissionsSeeder`)
   — the plan names the six roles and, per pipeline stage, which role *owns*
   it, but doesn't specify CRUD-level permissions for Companies/Contacts. The
   matrix used is the most conservative reading (Finance/Technical read-only,
   Sales/Sales Manager can create and edit, only Founder-Management/Admin can
   delete).
6. **RBAC implementation**: `spatie/laravel-permission` for role storage,
   enforced through hand-written Laravel Policies that check role names
   directly (not permission-based Gate checks), since the plan defines access
   in terms of named roles rather than abstract permissions.
7. **`OpportunityPolicy::transition()` conflates "is this move legal" with
   "is this user authorized"** — an out-of-sequence stage request returns
   `403 Forbidden` even for Admin, rather than a friendlier validation error.
   Functionally safe, but worth revisiting for UX in a later phase.
8. **Auth scaffolding**: Laravel Breeze (Blade stack), chosen because the
   plan specifies Blade + Livewire for the internal UI without naming an auth
   package.
9. **No auto-assigned role on registration.** A self-registered user can log
   in but sees an "Access pending" page until an admin runs
   `php artisan users:assign-role`. This is the root-cause fix for the
   Companies-page 403 (see below) — the alternative (auto-granting a default
   role to anyone who registers) was rejected as an unreviewed security
   decision for what is meant to be an internal, access-controlled tool.
10. **"Activity" and "Audit History" are one section** (`audit_events`) on
    the Opportunity/Company detail pages, not two, because there is no
    separate Activity entity yet (no calls/emails/meetings module exists —
    that's Outreach/Conversation, explicitly out of scope here). The combined
    section is labelled "Activity & Audit Timeline" rather than presenting a
    second, visually distinct section with identical data.
11. **"Last Activity" on the Companies list = `updated_at`.** There's no
    dedicated Activity entity to aggregate from yet (see #10), so this is the
    closest honest proxy rather than a fabricated metric.
12. **Company "Research Summary" and Opportunity "Discovery" are explicit
    placeholders** ("not generated yet" / "not built yet"), not fields backed
    by fake data — per the plan's own instruction not to prematurely build
    Prospect Intelligence or the Discovery module.
13. **Opportunity "name" is optional** (`Opportunity::displayName()` falls
    back to the company name). Making it required would have broken every
    already-seeded/demo opportunity that predates the field.
14. **Business Problem / Qualification fields** (`problem_category`,
    `evidence`, `impact`, `urgency`, `stakeholder`, `budget_signal`,
    `business_impact`, `decision_process`, `fit`) are plain nullable columns,
    manually entered — the plan is explicit that AI-based Problem
    Intelligence auto-extraction is a later phase.

## Fixing the Companies-page 403 (this session)

**Root cause:** a real user had registered via `/register` and received no
role — `CompanyPolicy::viewAny()` (and every other policy) correctly denies
access to a user with zero roles. The RBAC design itself was not broken;
there was simply no supported path from "just registered" to "has a role."

**Fix:** `php artisan users:assign-role` (see above) plus a friendlier
`resources/views/errors/403.blade.php` that explains *why* access is denied
instead of showing a bare "This action is unauthorized." (only visible when
`APP_DEBUG=false`; local dev shows the full Ignition trace either way).

**Data-loss note:** diagnosing this required running `php artisan
migrate:fresh --seed` against the local SQLite dev database, which — because
this is a real login, not only seeded fixtures — deleted the actual
registered account along with the demo data. The account was recreated with
the same email and role, password reset to `password`. This should have been
flagged before running `migrate:fresh` against a database known to hold a
real login; noting it here so it isn't silently missed. No other environment
was touched.

## Known environment limitations (this development sandbox)

- **Laravel Horizon requires the `pcntl`/`posix` PHP extensions, which do not
  exist on native Windows PHP builds.** `composer.json` has
  `config.platform.ext-pcntl` / `ext-posix` overrides so Composer can resolve
  the dependency graph on Windows; Horizon itself only actually runs inside
  the Linux app container (`docker-compose.yml`) or under WSL2. This is a
  platform fact, not a workaround to silently paper over — flagging it here so
  it isn't mistaken for Horizon working natively on Windows.
- **This sandbox has no local PostgreSQL server or running Docker daemon**, so
  `php artisan test` and local `php artisan migrate` in this environment ran
  against SQLite (`phpunit.xml` already defaults tests to SQLite in-memory,
  independent of this limitation). `.env.example` and `docker-compose.yml`
  both default to PostgreSQL + Redis, matching the architecture plan, and
  should be used for real development/staging/production.
- **`predis`** (pure-PHP Redis client) is configured instead of the `phpredis`
  extension, since `phpredis` isn't available in this environment. The Docker
  image installs the real `phpredis` extension via PECL; `REDIS_CLIENT` can be
  switched to `phpredis` there if preferred.
- A local Redis is now running via Laragon's bundled
  `redis-server.exe` (started manually — it is **not** a Windows service, so
  it will not survive a reboot). For anything beyond a quick local check, use
  Laragon's Redis toggle (auto-starts it) or `docker compose up redis`.

## Queue infrastructure verification

`.env` had drifted from `.env.example`: it still said `REDIS_CLIENT=phpredis`
(an extension that isn't installed) and `QUEUE_CONNECTION=database`, so
Horizon — which is Redis-only — would have hard-crashed
(`Class "Redis" not found`) the moment anyone opened `/horizon`. Fixed to
`REDIS_CLIENT=predis` / `QUEUE_CONNECTION=redis` to match `.env.example`.

Verified end-to-end against a real local Redis instance:

| Check | Result |
|---|---|
| Laravel reaches Redis (`Redis::connection()->ping()`) | ✅ `PONG` via predis |
| `/horizon` dashboard and `/horizon/api/stats` render for Admin | ✅ 200, valid JSON |
| `/companies` (or any Horizon route) for a non-Admin/Founder role | ✅ 403 (gate unchanged) |
| A real job dispatched (`dispatch(fn () => ...)`) lands in the `redis` queue | ✅ confirmed via `redis-cli keys` |
| `php artisan queue:work` picks it up and executes it | ✅ side effect observed |
| `php artisan horizon` (the master **supervisor** process) | ❌ `Call to undefined function pcntl_async_signals()` |

That last row is the one real, unavoidable gap on native Windows: Horizon's
supervisor forks and signal-manages worker processes via `pcntl`, which does
not exist on Windows PHP builds at all (not a missing extension you can
enable — the functions themselves aren't compiled in). The dashboard UI and
`queue:work` both function correctly; only the `horizon` command's own
process-supervision needs the Linux container (`docker-compose.yml`) or
WSL2. `tests/Feature/QueueInfrastructureTest.php` covers what's testable
without live infrastructure (queue config, Horizon registration/gate,
dispatch-and-run under the `sync` driver tests use).

## Remaining work (not started)

Everything after Phase 2 in the execution plan's roadmap (section 14):
Prospect Intelligence (research, ICP scoring), Outreach Engine (Gmail sending,
suppression, follow-ups), Conversation Agent, Meeting & Calendar (Google
Calendar), Document Intelligence, Requirements Validator, Pricing &
Profitability engine, Proposal Engine, Commercial Control (invoices, payment
verification), and the eventual Python specialized-intelligence layer. The
interfaces in `app/AI` and `app/Integrations` exist so these phases can be
built against a stable contract, but no real provider is wired up.
