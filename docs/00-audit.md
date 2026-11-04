# 00 — Phase 0 Audit

Recorded: 2026-11-04. Run by Claude Code (Phase 0 — Audit and baseline).

---

## Step 1 — Inventory

### Framework and runtime
```
Laravel Framework 13.39.0
PHP 8.5.10 (cli) — Homebrew build
```

### Installed packages

| Package | Version | Source commit |
|---|---|---|
| hasinhayder/tyro | v1.13.0 | ecce823 |
| hasinhayder/tyro-login | v2.17.3 | 060219d |
| hasinhayder/tyro-dashboard | v1.60.0 | 3eed2db |
| hasinhayder/tyro-checkpoint | v1.9.0 | 2491708 |
| laravel/boost | v2.10.4 | 9cd5e7d |
| intervention/image | v4.4.1 | (pulled via tyro-dashboard) |

Production require (composer.json direct): `hasinhayder/tyro`, `tyro-dashboard`, `tyro-login`, `laravel/framework`, `laravel/sanctum`, `laravel/tinker`.

Dev require: `hasinhayder/tyro-checkpoint`, `laravel/boost`, `laravel/pint`, `phpunit/phpunit`, `fakerphp/faker`, `mockery/mockery`, `nunomaduro/collision`, `laravel/pail`, `laravel/pao`.

### Migration status — all ran, batch 1 and 2

| Migration | Batch |
|---|---|
| 0001_01_01_000000_create_users_table | 1 |
| 0001_01_01_000001_create_cache_table | 1 |
| 0001_01_01_000002_create_jobs_table | 1 |
| 2022_05_17_181447_create_roles_table | 1 |
| 2022_05_17_181456_create_user_roles_table | 1 |
| 2024_01_01_000000_create_social_accounts_table | 1 |
| 2024_01_01_000001_add_two_factor_columns_to_users_table | 1 |
| 2024_01_01_000002_create_invitation_system_tables | 1 |
| 2025_01_01_000001_create_media_table | 1 |
| 2025_01_01_000001_create_privileges_table | 1 |
| 2025_01_01_000002_create_privilege_role_table | 1 |
| 2025_01_01_000002_create_starred_import_images_table | 1 |
| 2025_01_01_000003_add_suspension_columns_to_users_table | 1 |
| 2025_02_08_000000_add_profile_photo_to_users_table | 1 |
| 2026_02_15_000000_create_tyro_audit_logs_table | 1 |
| 2026_02_20_000000_create_smtp_presets_table | 1 |
| 2026_03_01_000000_add_is_favorite_to_tyro_media_table | 1 |
| 2026_03_26_000000_create_media_categories_tables | 1 |
| 2026_11_03_140550_create_personal_access_tokens_table | 2 |

No pending migrations.

### User model (confirmed traits and columns)

Database: PostgreSQL (`pgsql`). Table: `users`.

Columns confirmed: id, name, email, email_verified_at, password, remember_token, created_at, updated_at, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at (from tyro-login), suspended_at, suspension_reason (tyro), profile_photo_path, use_gravatar.

Relations: `tokens` (Sanctum MorphMany), `roles` (BelongsToMany → Tyro Role).

### Artisan namespaces

- `tyro:` — 80+ commands: auth, roles, privileges, users, audit, seed, checkpoint management
- `tyro-dashboard:` — 17 commands: install, make-resource, create-admin-page, create-user-page, create-common-page, remove-*, publish, publish-style, setup-ai-skill, update, update-config, update-script, update-style, version, createsuperuser
- `tyro-checkpoint:` — 17 commands: create, restore, delete, list, details, install, lock, unlock, flag, unflag, flush, encrypt, generate-key, import, add-note, version

### Routes

**Dashboard (/dashboard): 115 routes** — full CRUD for users (with impersonation, suspend/unsuspend, 2FA reset, bulk delete), roles, privileges, media (categories, upload, crop, starred, import), audits (export, bulk delete, flush), checkpoints UI, invitations (admin + user flows), SMTP settings + presets, system settings, profile (password, 2FA setup/reset, photo), health, logs, widgets, component examples, generic resource CRUD.

**API (/api): 29 routes** — Tyro RBAC: login, me, users CRUD + roles, roles CRUD + privileges, privileges CRUD, audit logs, tyro info/version. Plus default Laravel `GET /api/user`.

---

## Step 2 — Comparison with docs/00-research.md

| VERIFY item | Status | Evidence |
|---|---|---|
| 1. artisan list tyro-dashboard and tyro match doc | ✅ RESOLVED | All commands present and match the documented scaffold pattern |
| 2. dashboard routes match doc | ✅ RESOLVED | 115 routes confirmed; covers everything documented in Parts 1 and 2 of AGENTS.md |
| 3. API routes documented before designing /api/v1/auth/* | ✅ RESOLVED | 29 Tyro API routes recorded above. Our own API will live under `/api/v1/` (versioned) — no conflict with Tyro's `/api/` namespace |
| 4. tyro-checkpoint DB engine for our setup | ✅ RESOLVED | Package description: "Supports SQLite, MySQL, MariaDB, and PostgreSQL." Our live DB is PostgreSQL — confirmed supported |
| 5. intervention/image installed | ✅ RESOLVED | v4.4.1 installed as transitive dep of tyro-dashboard |
| 6. Cashier Stripe + Paddle collision | ✅ RESOLVED (ADR-2) | Neither package installed — see ADR-2 below |
| 7. Stripe/Paddle Bangladesh eligibility | ⚠️ OPEN — cannot resolve at Phase 0 | Requires merchant registration test with real credentials. Deferred to Phase 4 billing setup. |
| 8. SSLCommerz recurring schedule parameter | ⚠️ OPEN — cannot resolve at Phase 0 | Requires merchant account access. Deferred to Phase 4. |

---

## Step 3 — Checkpoint database engine

tyro-checkpoint v1.9.0 description: "Supports SQLite, MySQL, MariaDB, and PostgreSQL."

Live database: PostgreSQL (confirmed from `php artisan model:show User` — connection `pgsql`, column type `character varying`).

Result: **PostgreSQL checkpoints are fully supported.** No action needed.

Existing checkpoints:
- ID 1: `initial_checkpoint` — 55.25 KB, created 2026-11-03 14:07
- ID 2: `clean-baseline` — "Fresh Tyro install", 55.25 KB, created 2026-11-03 14:07 (locked candidate)

---

## Step 4 — Cashier collision check (ADR-2)

Neither `laravel/cashier-stripe` nor `laravel/cashier-paddle` is installed. No collision is currently possible.

**Research finding (from package knowledge):** If both were installed simultaneously, the conflicts would be:
- Both create `subscriptions` and `subscription_items` tables (identical names, incompatible schemas).
- Both add columns to the users table via their `Billable` trait (overlapping column names).
- The `Billable` trait cannot be applied from two packages simultaneously.

**Decision (ADR-2):** Do not install either Cashier package. Build a custom `PaymentGateway` interface with per-driver adapters (Stripe, Paddle, SSLCommerz). This avoids the collision entirely and gives us the provider-agnostic architecture documented in `docs/02-architecture.md`.

VERIFY item #6: **RESOLVED — custom gateway interface is confirmed correct.**

---

## Surprises (differs from docs/00-research.md)

### 1. Database is PostgreSQL, not MySQL

docs/00-research.md and docs/00-audit.md planning assumed MySQL as the operational database. The live environment uses **PostgreSQL**.

Impact:
- All migrations must use PostgreSQL-compatible syntax (avoid MySQL-specific types/functions).
- `tyro-checkpoint` supports PostgreSQL — no issue.
- Our own migrations (workspaces, billing, etc.) should be written database-agnostic; prefer standard SQL types over MySQL-specific ones.
- **Action required:** Update planning docs to reference PostgreSQL as the operational DB. The `.env.example` defaults to `sqlite` (Laravel default for new projects) but the live instance uses `pgsql`.

### 2. Test runner is PHPUnit, not Pest

`phpunit/phpunit` is in `require-dev`. Pest is not installed. The phase prompts reference "TDD with Pest" but the current stack uses PHPUnit only.

Impact: Phase prompts saying "TDD with Pest" should be treated as PHPUnit until/unless Pest is added. This is a minor inconsistency — both AGENTS.md CLAUDE.md guidelines and the CLAUDE.md `testing-best-practices` skill support PHPUnit.

**Decision:** Keep PHPUnit. Do not install Pest unless explicitly requested. Update Phase 2 prompt note when needed.

### 3. tyro-login v2 provides social OAuth / passkeys / magic links natively

tyro-login v2.17.3 description confirms: "10 premium layouts, social OAuth, passkeys, TOTP 2FA, OTP, magic links, invitations, lockout, captcha". The AGENTS.md gap analysis listed several of these as "build ourselves" but most are already provided — confirm and cross off in Phase 1.

---

## Step 5 — Skills and tooling (already done before Phase 0)

- Laravel Boost v2.10.4 — installed, `boost:install` already run
- `mattpocock/skills` (38 skills) — installed, symlinked/copied to all agent dirs
- `tyro:setup-ai-skill` and `tyro-dashboard:setup-ai-skill` — available but not run yet
- `/setup-matt-pocock-skills` — run 2026-11-04 (user confirmed)
- Design skills — deliberately deferred to Phase 6.5 (see docs/07-design-system.md)

---

## Step 6 — Project files

- `AGENTS.md` — exists at repo root, 667 lines, authoritative (Parts 1–6 + Boost block)
- `CLAUDE.md` — exists at repo root with Boost guidelines block
- `docs/skills-lock.md` — created and up to date
- `skills-lock.json` — auto-generated by skills CLI, 38 skills from mattpocock/skills
- `docs/adr/` — created (empty, ready for Phase 1)
- `GLOSSARY.md` — created (empty, ready for Phase 1)

---

## Phase 0 summary

**VERIFIED:**
- All Tyro artisan commands match documentation
- Dashboard routes (115) and API routes (29) confirmed
- PostgreSQL checkpoint support confirmed
- intervention/image v4.4.1 installed
- Cashier collision check: ADR-2 resolved — custom gateway interface is the right call

**OPEN:**
- Stripe/Paddle Bangladesh merchant eligibility (VERIFY #7) — deferred to Phase 4
- SSLCommerz recurring schedule parameter (VERIFY #8) — deferred to Phase 4

**SURPRISES:**
- Database is PostgreSQL (not MySQL) — update planning docs; no migration impact, tyro-checkpoint supports it
- Test runner is PHPUnit (not Pest) — treat "Pest" references in phase prompts as PHPUnit
- tyro-login v2 covers more auth features natively than expected — verify against AGENTS.md gap list in Phase 1

**ADR-2 DECISION:** Neither Cashier package installed. No collision possible. Confirm custom PaymentGateway interface architecture — build Stripe, Paddle, SSLCommerz adapters ourselves. No Cashier dependency ever.
