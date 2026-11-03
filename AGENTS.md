# AGENTS.md — SaaS Boilerplate Agent Guidelines
# Stack: Laravel 12/13 · PHP 8.2+ · MySQL · Redis · Nginx
# UI: Tyro Dashboard v1.15.0 (Blade + Tailwind + Alpine + shadcn CSS vars)
# Auth: Tyro Login · RBAC: Tyro · Snapshots: Tyro Checkpoint (dev only)
#
# Read before touching ANY code:
#   docs/00-research.md · docs/00-audit.md · docs/01-prd.md
#   docs/02-architecture.md · GLOSSARY.md · docs/adr/

# ============================================================
# PART 1 — TYRO NATIVE FEATURES (USE THESE, DON'T REBUILD)
# ============================================================
# Knowing what Tyro already ships is the most important thing
# for avoiding wasted work. Check this list first every time.

## 1A. Authentication (Tyro Login) — never rewrite any of this
- Login, register, forgot/reset password, email verification
- Email OTP/PIN login, magic login link
- TOTP 2FA (authenticator app), Socialite social login
- Captcha on register, brute-force lockout, user suspension
- 5 UI layouts, dark/light themes, email templates built in
- Hook into events (Registered, Login, etc.) — don't fork flows

## 1B. Dashboard UI shell
- Responsive Blade layouts:
    tyro-dashboard::layouts.user   (authenticated users)
    tyro-dashboard::layouts.admin  (staff only)
    tyro-dashboard::layouts.app    (both)
- Collapsible sidebar: TYRO_DASHBOARD_COLLAPSIBLE_SIDEBAR=true
- Sidebar colours: TYRO_DASHBOARD_SIDEBAR_BG / _SIDEBAR_TEXT env vars
- Theming: shadcn CSS variables (oklch) in:
    resources/views/vendor/tyro-dashboard/partials/shadcn-theme.blade.php
  Publish first: php artisan tyro-dashboard:publish-style --theme-only
  Visual editor: tweakcn.com
- Available CSS vars: --background, --foreground, --primary, --secondary,
  --muted, --accent, --destructive, --border, --input, --ring, --card,
  --popover, --sidebar, --sidebar-*, --success, --warning, --info,
  --chart-1 through --chart-5, --card-shadow, --card-shadow-hover
- Global $user variable injected via view composer into ALL dashboard views
  → NEVER pass $user manually to dashboard views
- Dark mode: built in, respects user OS preference automatically

## 1C. User & staff management — /dashboard/users|roles|privileges
- Full user CRUD: list, create, edit, suspend, delete
- Role CRUD (protected: admin, super-admin, user cannot be deleted)
- Privilege CRUD (fine-grained permissions within roles)
- admin_roles in config/tyro-dashboard.php defines who is staff
- Superuser: php artisan tyro-dashboard:createsuperuser

## 1D. Profile — /dashboard/profile
- Name, email, password change, avatar upload, Gravatar fallback
- HasProfilePhoto trait handles upload, crop, and Gravatar
- Env: TYRO_DASHBOARD_ENABLE_GRAVATAR / _PHOTO_MAX_SIZE / _PHOTO_CROP_POSITION

## 1E. Impersonation — use as-is, do not rebuild
- Admin impersonates user: POST /dashboard/users/{id}/impersonate
- Leave impersonation:      GET  /dashboard/leave-impersonation
- Protected by TyroImpersonation middleware; state in server-side session
- Rules we add on top:
    * Write a HasAuditTrail entry on every impersonation start/end
    * Block billing-destructive actions (refunds, plan changes) while impersonating
    * Show a persistent banner in the UI while impersonating

## 1F. Invitation & referral system — hook into this, don't duplicate
- Enable: TYRO_DASHBOARD_ENABLE_INVITATION=true
- Tables auto-migrated: invitation_links, invitation_referrals
- Admin UI: /dashboard/invitations (create, view stats, revoke)
- User UI:  /dashboard/referrals  (own referral history)
- Workspace invitations should reuse or extend this system

## 1G. Audit trail — HasAuditTrail trait
- Admin UI: /dashboard/audit-trail (filterable, paginated)
- Detail:   /dashboard/audit-trail/{id} (shows field-level old/new diffs)
- Apply HasAuditTrail to: Workspace, Subscription, Plan, PlanPrice,
  Coupon, gateway_customers, Settings — anything an admin can mutate
- Every impersonation start/end, refund, and privilege change MUST
  produce an explicit audit entry

## 1H. Modal dialogs — use these, never browser confirm()
Available globally in all dashboard Blade views, no import needed:
  showConfirm(title, message, callback)  — standard confirm (blue)
  showAlert(title, message)              — informational
  showDanger(title, message, callback)   — destructive (red)
HasCrud uses showDanger automatically for delete actions.
Apply the same pattern to ALL custom destructive actions.

## 1I. Admin announcement bar
- Static config: TYRO_DASHBOARD_ADMIN_BAR_ENABLED / _MESSAGE / _BG_COLOR /
  _TEXT_COLOR / _ALIGN / _HEIGHT
- Dynamic (from PHP): AdminNotice::show($message, $bgColor, $textColor, $align)
- Use for: billing alerts, maintenance windows, trial expiry notices
- Allowed HTML in message: <p> <a> <b> <span> and inline styles only

## 1J. Package settings pages (already exist — do not recreate)
- /dashboard/settings/tyro        → Tyro RBAC settings  (admin only)
- /dashboard/settings/tyro-login  → Tyro Login settings (admin only)

## 1K. Page scaffolding — ALWAYS run this before hand-writing a dashboard page
One command creates the Blade view + route + sidebar menu link atomically.

  # User-only page (auth middleware, user layout, user sidebar)
  php artisan tyro-dashboard:create-user-page "page-name"

  # Admin-only page (auth + admin middleware, admin layout, admin sidebar)
  php artisan tyro-dashboard:create-admin-page "page-name"

  # Both users and admins (auth middleware, app layout, both sidebars)
  php artisan tyro-dashboard:create-common-page "page-name"

  # Remove (deletes view + route + sidebar link cleanly)
  php artisan tyro-dashboard:remove-user-page "page-name"
  php artisan tyro-dashboard:remove-admin-page "page-name"
  php artisan tyro-dashboard:remove-common-page "page-name"

Generated output:
  resources/views/dashboard/{page-name}.blade.php
  routes/web.php                    (route appended)
  partials/{user|admin}-sidebar.blade.php  (menu link added)

Views are auto-published if missing. Use --force to re-publish.

## 1L. HasCrud — ALWAYS use for plain CRUD resources before writing a custom controller
Add to any Eloquent model for automatic CRUD at /dashboard/resources/{plural-model}

  use HasinHayder\TyroDashboard\Concerns\HasCrud;

HasCrud reads $fillable to generate form fields automatically.

Auto-detected field types from column name patterns:
  *_id suffix             → select (BelongsTo relationship)
  is_* / has_* / can_*   → boolean checkbox
  description / bio / content / body → textarea
  BelongsToMany method    → multi-select with auto pivot sync

Override any field without rewriting the resource:
  protected $resourceFieldOverrides = [
    'field_name' => [
      'type'                => 'text|number|textarea|markdown|richtext|boolean|
                               select|multiselect|radio|checkbox|date|time|
                               datetime-local|password|email|url|file',
      'label'               => 'Display Label',
      'rules'               => 'required|min:5',
      'default'             => 'value',
      'placeholder'         => 'hint text',
      'help_text'           => 'shown below input',
      'hide_in_index'       => true,
      'hide_in_create'      => true,
      'hide_in_edit'        => true,
      'hide_in_form'        => true,   // hides in both create and edit
      'hide_in_single_view' => true,
      'readonly'            => true,
      'searchable'          => true,
      'sortable'            => true,
      'attributes'          => ['min' => '0', 'step' => '0.01'],
      // BelongsTo / BelongsToMany
      'relationship'        => 'methodName',
      'option_label'        => 'name',
      'multiple'            => true,
      // File / image
      'display_image'       => true,
      'display_image_position' => 'top|bottom',
    ],
  ];

Role-based access control on a resource:
  protected $resourceRoles    = ['admin', 'manager'];  // who can access
  protected $resourceReadonly = ['editor'];             // read-only role
  protected $readonly         = true;                  // fully read-only

Other useful overrides:
  $resourceKey              // URL slug (default: plural snake_case class name)
  $resourceTitle            // Plural display name in UI
  $resourceTitleSingular    // Singular display name
  $resourceUploadDisk       // Storage disk (default: public)
  $resourceUploadDirectory  // Upload path (default: uploads)

File uploads: old file is auto-deleted when replaced.
Set display_image => true for inline image preview on edit form.

## 1M. Sidebar menu — config/menu.php
After scaffolding, fine-tune sidebar entries:
  'adminMenuItems'  → admin sidebar only
  'commonMenuItems' → both sidebars
  'userMenuItems'   → user sidebar only
Each item: ['title' => 'Label', 'url' => '/path', 'icon' => '<svg>...</svg>']
NEVER hand-edit sidebar blade partials directly — use menu.php or the
scaffold commands (remove commands won't clean up hand-edited partials).

## 1N. Branding env vars
  TYRO_DASHBOARD_APP_NAME="My SaaS"
  TYRO_DASHBOARD_LOGO="/images/logo.svg"
  TYRO_DASHBOARD_PREFIX=dashboard       # or 'admin'
  TYRO_DASHBOARD_DISABLE_EXAMPLES=true

## 1O. All native routes at a glance
/dashboard                              All authenticated
/dashboard/profile                      All authenticated
/dashboard/referrals                    All authenticated
/dashboard/leave-impersonation          All authenticated
/dashboard/users                        Admin only
/dashboard/users/create                 Admin only
/dashboard/users/{id}/edit              Admin only
POST /dashboard/users/{id}/impersonate  Admin only
/dashboard/roles                        Admin only
/dashboard/roles/create                 Admin only
/dashboard/roles/{id}                   Admin only
/dashboard/privileges                   Admin only
/dashboard/privileges/create            Admin only
/dashboard/settings/tyro                Admin only
/dashboard/settings/tyro-login          Admin only
/dashboard/invitations                  Admin only
/dashboard/audit-trail                  Admin only
/dashboard/audit-trail/{id}             Admin only
/dashboard/resources/{model}            Role-gated by $resourceRoles


# ============================================================
# PART 2 — WHAT WE BUILD ON TOP (gap analysis)
# ============================================================
# These are NOT in Tyro. Build them. Never duplicate Part 1.

## Decision tree before writing ANY new dashboard screen
1. Does Tyro already provide this? (check Part 1) → configure/theme only
2. Is it a plain CRUD resource?       → HasCrud + create-admin/user-page
3. Is it CRUD + custom business logic? → scaffold with create-*-page, add controller/action
4. Is it fully custom?                 → Blade view in Tyro layout + menu.php entry

## Must build (not in Tyro)
Workspaces
  - workspace, workspace_members, workspace_invitations tables
  - Personal workspace auto-created on registration
    (hook Tyro Login's Registered event — confirm event name at Phase 0)
  - Workspace switcher header component
  - Workspace settings page (create-user-page), team management page

Plans, features, entitlements
  - plans, plan_prices, features, plan_features (HasCrud admin resources)
  - Seeder from config/saas-plans.php (placeholder prices in docs/04-billing.md)
  - Entitlements service: Entitlements::for($workspace)->can($key) / ->limit($key)
  - entitled: middleware and @entitled Blade directive
  - usage_records table with period counters and reset
  - Downgrade pre-check that warns if current usage exceeds new plan limits

Billing core
  - PaymentGateway interface + GatewayManager
  - Subscription state machine with domain events
    (SubscriptionActivated, PaymentFailed, TrialEnding, SubscriptionCanceled, etc.)
  - webhook_events pipeline: verify → unique store → queue → idempotent replay
  - payments, refunds, invoices (sequential INV-YYYY-NNNNNN, PDF export)
  - gateway_customers table

Gateway adapters
  - Stripe: checkout sessions, customer portal, webhooks, proration, refunds
  - Paddle (Billing not Classic): overlay/hosted checkout, webhooks, MoR tax
  - SSLCommerz: hosted checkout, IPN hash verify + val_id + amount check,
    raw IPN log, CSRF exclusion, refunds
  - billing:renewals scheduler (SSLCommerz app-driven: 7/3/1-day reminders,
    grace period, auto-downgrade to Free)
  - Coupons (HasCrud) + coupon_redemptions

User billing screens (create-user-page for each)
  - Billing overview (plan badge, status, renewal date, usage meters, gateway badge)
  - Change plan (plan cards, proration preview, downgrade warnings)
  - Checkout (gateway selector by currency/region, coupon field)
  - Invoices & receipts (list, PDF download)
  - Cancel flow (reason survey, offer pause/downgrade, confirm end date)
  - Payment methods (Stripe/Paddle → portal link; SSLCommerz → last payment)
  - API keys (create, scope, revoke, last used)
  - Notifications (list, mark read, email/push preferences)
  - Support (new ticket, my tickets)
  - Data & privacy (export my data, delete account)
  - Onboarding wizard (name workspace → invite team → pick plan)

Admin screens (create-admin-page for each)
  - Overview: MRR, ARR, churn, trials, failed payments, signups by gateway
    (document metric formulas in the page controller)
  - Workspaces: search, detail (members/plan/usage/payments),
    grant/extend trial, force plan change, suspend
  - Subscriptions: filter by status/gateway, timeline of state changes
  - Payments & refunds: list, issue refund (billing.refund privilege, audited)
  - Invoices: list, regenerate PDF
  - Webhook events: status, payload viewer, replay button, error list
  - Gateway settings: keys (masked), test/live mode, webhook URLs,
    connection test button (secrets encrypted in settings table)
  - Email templates: preview, send test
  - Support inbox: ticket list, reply, close
  - Feature flags (optional, via Laravel Pennant)
  - System health: queue depth, failed jobs, scheduler last run, disk, DB size
  - App settings: brand, support email, legal URLs, trial length, grace days
  - Data requests: export/deletion queue

Notifications & comms
  - 18 transactional emails (see docs/04-billing.md for full list)
  - In-app notifications table with preferences
  - Push notification job (interface-backed, FCM/APNs)
  - AdminNotice::show() for billing alerts from scheduler/webhooks

Mobile API
  - /api/v1 with Sanctum token abilities
  - devices table (push tokens, platform, app version)
  - OpenAPI spec at /docs/api

Public site
  - Landing, pricing (DB-driven, USD/BDT toggle, monthly/annual toggle)
  - Legal pages (flag for lawyer review)
  - Contact form → support inbox
  - Changelog/docs (markdown-driven)
  - SEO: title, meta, sitemap.xml, OpenGraph


# ============================================================
# PART 3 — NON-NEGOTIABLES
# ============================================================

## Architecture rules
- Billable entity = Workspace. Tyro roles = global staff only.
  Workspace roles (owner/admin/member/billing) = our workspace_members table.
- Business logic lives in Actions/Services.
  Web controllers and /api/v1 controllers call the same Actions — stay thin.
- NEVER check plan name strings in code (if $plan === 'pro').
  ALWAYS use: Entitlements::for($workspace)->can('feature_key')
- Money = integer minor units + ISO currency (1200, 'USD'). NO floats, ever.
- Payment state is determined ONLY by verified webhooks/IPN.
  Return URLs display status only — they NEVER grant access or change state.

## Tyro-specific rules
- ALWAYS run create-*-page BEFORE hand-writing any dashboard page.
- ALWAYS try HasCrud before writing a custom resource controller.
- ALWAYS publish views before editing:
    php artisan vendor:publish --tag=tyro-dashboard-views
  NEVER edit files under /vendor directly.
- Apply HasAuditTrail to every model an admin can mutate.
- ALL custom colours go into shadcn-theme.blade.php CSS variables.
  NEVER hard-code hex or oklch values in Blade views or component files.
- Sidebar entries go in config/menu.php or via scaffold commands only.
  NEVER hand-edit sidebar blade partials.
- The global $user is always available in dashboard views — NEVER pass it manually.
- Use showConfirm / showAlert / showDanger for ALL JS dialogs.
  HasCrud already uses showDanger for delete — apply the same to custom pages.
- Use AdminNotice::show() for dynamic admin bar messages (never a custom banner div).
- Invitation tables are already migrated when the env var is set.
  Extend this system for workspace invitations; don't create a parallel table.

## Security rules
- Webhook/IPN: verify signature/hash → store in webhook_events
  (UNIQUE on gateway + event_id) → queue job → idempotent on replay.
- SSLCommerz specifically: verify hash → call val_id API →
  match amount + currency + tran_id → THEN update state.
- Exclude /webhooks/* routes from CSRF — only those routes.
- Secrets (gateway keys) live in .env or encrypted settings table. Never logged.
- No secrets in tests, factories or fixtures.
- showDanger() on all destructive admin actions — no silent deletes.
- Impersonation: audit entry on start and end; block refunds and plan changes.

## API rules (/api/v1)
- Reuse Tyro Login's API auth surface (verify at Phase 0 with route:list --path=api).
  NEVER fork the auth rules (email verification, suspension, 2FA, lockout).
- Workspace context: X-Workspace-Id header, validated against workspace_members.
- Cursor pagination. ETag/If-None-Match on cacheable lists.
- Idempotency-Key header required on payment and subscription POSTs.
- One error envelope: { message, code (stable snake_case), errors: {} }.
- Rate limits: per Sanctum token (sliding window) + per IP.

## Design rules
- Follow design-system/<project>/MASTER.md (generated in Phase 6.5).
- Tyro's shadcn CSS variables = single source of truth for colour tokens.
- WCAG 2.2 AA required everywhere. Run design-critique audit after every UI phase.
- Every screen must have: empty state, loading state, error state.
- Check at both 1440px (desktop) and 390px (mobile) before marking done.
- Dark mode must work — test it.


# ============================================================
# PART 4 — WORKFLOW (follow for every ticket)
# ============================================================

Step 1 — READ
  Read: relevant ADR(s) in docs/adr/, GLOSSARY.md, Part 1 of this file.
  If touching billing: also read docs/04-billing.md gateway rules.

Step 2 — CHECKPOINT
  php artisan tyro-checkpoint:create <phase>-<feature>-start
  (Local dev only. Never in production. Not a backup.)

Step 3 — SCAFFOLD FIRST
  Run create-*-page or add HasCrud before writing anything from scratch.
  Ask: "Is there a Tyro native for this?" before writing any UI code.

Step 4 — TDD
  Write the failing Pest test first. Then make it pass.
  Billing tests: use gateway fakes (no real API calls in tests).

Step 5 — BUILD
  Action/Service → Controller calls Action → API Resource or Blade view.
  Apply HasAuditTrail. Fire domain events. No logic in controllers or views.

Step 6 — QUALITY CHECKS
  ./vendor/bin/pint                  (zero style errors)
  php artisan larastan               (static analysis — level set in ADR)
  Check for N+1 queries on all list pages (Boost MCP query tools or debug bar)
  Run /code-review

Step 7 — VERIFY AUTH & AUDIT
  Test: correct role can access, wrong role gets 403.
  Test: HasAuditTrail fires on every expected model mutation.
  Test: showDanger appears on every destructive action.

Step 8 — UI GATE (for every screen)
  Test at 1440px and 390px. Check dark mode.
  Empty state, loading state, error state all present.
  After each UI phase: run design-critique audit.

Step 9 — CLOSE
  Full test suite passes. Update GLOSSARY.md and relevant ADR if anything changed.
  No silent TODOs (comment with FIXME + ticket reference if deferring).

## Ask me before:
  - Changing any ADR
  - Adding a Composer or npm dependency
  - Touching anything in Tyro Login's auth flow
  - Any action in live-mode gateway (Stripe/Paddle/SSLCommerz dashboard)
  - Installing or updating any third-party skill
  - Installing anything globally (npm -g, composer global)

## Definition of done — a ticket is done when ALL of these are true:
  Tests pass (including auth, authz, billing fakes)
  Pint clean
  Static analysis clean
  No N+1 on list pages
  HasAuditTrail fires on all expected mutations
  showDanger/showConfirm on all destructive actions
  Design-critique gate passed (if UI work)
  Works at 1440px and 390px, dark mode checked
  GLOSSARY.md and ADRs updated if anything changed
  No silent TODOs


# ============================================================
# PART 5 — TOOLS AND SKILLS
# ============================================================

## Always use these tools for current information
  Boost MCP     → schema, routes, logs, tinker, docs search
  Context7      → Stripe docs, Paddle docs, Laravel docs, SSLCommerz SDK
                  NEVER rely on training memory for API event names, SDK method
                  signatures, or version-specific behaviour

## Matt Pocock skills — when to use each
  /grill-with-docs         align on a feature before coding
  /grill-me                align on a non-code decision
  /to-spec                 discussion → spec
  /to-tickets              spec → vertical tracer-bullet tickets (with blocking edges)
  /implement               build one ticket (wraps tdd + code-review)
  /implement-spec          build a whole spec
  /wayfinder               large-scale planning
  diagnosing-bugs          reproduce failing test first → hypothesise → instrument → fix
  tdd                      test-first always
  code-review              Standards axis + Spec axis; run before every PR
  /improve-codebase-architecture   run every few days
  /handoff  /retro         session boundary hygiene
  wizard                   interactive script for human-only steps
                           (gateway dashboards, webhook setup, live keys)
  pr                       PR description

## Design skills
  ui-ux-pro-max            MASTER.md is the design system source of truth
  designskills/dashboard-design       dashboard page layout guidance
  designskills/mobile-ui-design       mobile layout
  designskills/design-critique        accessibility + visual audit (run after each UI phase)
  designskills/email-design           transactional email layout
  designskills/landing-page-design    public site

## Third-party skill safety rules
  - All skills pinned — versions recorded in docs/skills-lock.md
  - NEVER run uipro update, npx skills update, or any self-update command
  - NEVER install global packages without asking me first
  - Image generation (Gemini) only on explicit request from me
  - NEVER send screenshots, customer data, or PII to image generation
  - NEVER print, log, or commit environment variables or API keys
  - Read every new SKILL.md before using it; flag anything suspicious


# ============================================================
# PART 6 — OPEN DECISIONS (must be answered in Phase 1)
# ============================================================
# Do not guess on these. Raise them, document the answer as an ADR.

1. Gateway eligibility
   Verify Stripe and Paddle merchant onboarding for Bangladesh-based businesses
   before designing the gateway-selection UI. If neither works natively,
   the checkout flow design changes significantly.

2. SSLCommerz recurring
   Is the schedule parameter enabled on the merchant account?
   If not confirmed: implement app-driven renewals (reminder emails + grace period).

3. Mobile billing
   Web checkout in in-app browser only, or add store billing (StoreKit/Play Billing)?
   Store billing requires a separate ADR and a fourth gateway adapter.

4. Cashier package collision
   Confirm via Context7: can laravel/cashier-stripe and laravel/cashier-paddle
   coexist without migration or trait collision?
   Decision: custom billing layer vs Cashier Stripe + custom adapters for Paddle/SSLCommerz.

5. Tyro Login API surface
   At Phase 0: run php artisan route:list --path=api
   Document exactly which auth endpoints already exist before designing /api/v1/auth/*.

6. Larastan analysis level
   Choose level (1–9) and record in an ADR. Level 5 is a reasonable starting point.

7. OpenAPI generation tool
   Scramble or Scribe — verify Laravel 13 support before choosing.

8. Final plan names, prices, feature limits
   The numbers in docs/04-billing.md are placeholders. Set real values before Phase 3.


<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>


# ============================================================
# PART 7 — AGENT SKILLS
# ============================================================

## Agent skills

### Issue tracker

Issues and specs live in GitHub Issues on `itsmeshafat/saas-boilerplate-laravel`, via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

Default vocabulary: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: one `GLOSSARY.md` at the repo root plus `docs/adr/`. See `docs/agents/domain.md`.
