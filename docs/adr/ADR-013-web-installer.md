# ADR-013: First-Run Web Installer Wizard

**Status:** Accepted
**Date:** 2026-11-08

## Context

The boilerplate is a product. Buyers clone it and expect to reach a working,
demo-populated app without touching a terminal beyond `composer install` and
`npm install && npm run build`. Setup previously required manual `.env`
editing, `migrate --seed`, and knowing the seeder/role order (Tyro roles must
exist before `StaffRolesSeeder`; the admin needs a `Registered` event to get
a workspace).

## Decision

A browser-based 5-step wizard at `/install` (requirements → database →
admin account → optional gateways → optional demo data + finish).

1. **Installed state is a file marker** — `storage/app/installed.lock` — not
   a DB row. The DB can be dropped while the app must stay locked; a file
   survives that and requires no DB query on every request.
2. **Global pre-install gate** — `RedirectIfNotInstalled` prepended to the
   global stack: every path except `/install*`, `/up`, and static assets
   redirects to the installer until the lock exists. `BlockIfInstalled`
   (alias `install.locked`) 404s the installer routes once installed.
3. **`.env` writing reuses the Tyro pattern** — regex key-upsert, same as
   `TyroLoginExtraSettingsController::writeEnv()`, generalized into
   `Services/Install/EnvWriter` with an injectable path (tests use a scratch
   file; the real `.env` is never touched by tests). Credentials are verified
   with a raw PDO connection *before* anything is written.
4. **Gateway keys go through `SettingsService`** (encrypted `settings`
   table), exactly like Admin → Gateway Settings — the installer never
   invents a second storage location for secrets.
5. **Admin creation reuses the registration path** — create user, ensure
   Tyro roles, run `StaffRolesSeeder`, attach `super-admin`, then fire
   `Illuminate\Auth\Events\Registered` so `WorkspaceServiceProvider`
   creates the personal workspace. No forked auth logic.
6. **Demo data is rich, idempotent, and removable** — `DemoDataSeeder`
   always runs `DefaultSettingsSeeder` + `PlansAndFeaturesSeeder`; the
   optional dataset adds workspaces in every subscription state, payments,
   invoices, coupons, tickets, and a member user. `php artisan
   saas:reset-demo` removes exactly the demo rows by known emails/slugs/
   codes. New factories (Workspace, Subscription, Payment, Invoice,
   SupportTicket, Plan) follow `CouponFactory`'s named-state convention.

## Consequences

- Tests assume an installed app: the base `TestCase` writes the lock in
  `setUp` and removes it in `tearDown`; `InstallTest` deletes it after
  `parent::setUp()` to exercise the wizard.
- phpunit.xml's `tests/Unit` suite entry was removed (directory never
  existed; it broke plain `php artisan test`).
- Out of scope, recorded for later: a headless artisan variant of the
  installer; running `npm run build` from the wizard (warning-only check);
  the post-registration onboarding wizard (separate planned feature).

## Alternatives considered

- **Artisan CLI wizard only** — rejected: not "experience it easily" for
  the buyer persona.
- **DB-row installed flag** — rejected: the marker must exist independent
  of the database being configured/dropped.
- **Laravel Installer-style remote setup** — rejected: overkill for a
  self-hosted boilerplate.
