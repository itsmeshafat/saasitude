# 09 — Phase Prompts

One prompt per agent session. Start each session by reading your last /handoff note.
Create a Tyro Checkpoint at phase start. Close every phase with /code-review.

---

### Phase 0 — Audit and baseline

You are working in a Laravel app that already has hasinhayder/tyro, tyro-login, tyro-dashboard,
tyro-checkpoint (dev), and laravel/boost installed. Do NOT write any feature code in this phase.

Step 1 — Inventory with Boost MCP + CLI
Run every command below and record the full output in docs/00-audit.md:
  php artisan --version
  php --version
  composer show hasinhayder/tyro hasinhayder/tyro-login hasinhayder/tyro-dashboard hasinhayder/tyro-checkpoint laravel/boost
  php artisan list tyro
  php artisan list tyro-dashboard
  php artisan list tyro-checkpoint
  php artisan route:list --path=dashboard
  php artisan route:list --path=api
  php artisan migrate:status
  php artisan model:show User

Step 2 — Compare against docs/00-research.md
For every command, note: does the output match what the doc says?
Flag every VERIFY item as resolved or unresolved with evidence.
Record any commands, routes or features present but NOT in the doc.

Step 3 — Checkpoint database engine
Run: php artisan tyro-checkpoint:list (or tyro-checkpoint:install if needed)
Confirm whether MySQL is supported or only SQLite. Record in audit doc.

Step 4 — Cashier collision check
Use Context7 to read the migration files of laravel/cashier-stripe and laravel/cashier-paddle.
Check: do their subscriptions tables, Billable trait, and customer models conflict if both installed?
Report your finding with specific file references. This resolves ADR-2.

Step 5 — Install skills and tooling
  php artisan boost:install (select all agents, enable guidelines and skills)
  php artisan tyro-dashboard:setup-ai-skill
  npx skills@latest add mattpocock/skills -a claude (or your agent name)
  /setup-matt-pocock-skills (set issue tracker to local files, docs to docs/)
  Install design skills using the hardened commands in docs/07-design-system.md
  Read EVERY installed third-party SKILL.md and flag anything suspicious.

Step 6 — Create project files
  AGENTS.md already exists at the repo root (maintained there directly — see docs/08-agents-setup.md). Read it, don't recreate it.
  Create docs/skills-lock.md listing every installed skill with version or pinned commit.
  Create docs/adr/ directory (empty, ready for Phase 1).
  Create GLOSSARY.md (empty, ready for Phase 1).

Step 7 — Tyro Checkpoint baseline
  php artisan tyro-checkpoint:create "phase-0-baseline" --note="Tyro install + skills setup"
  Then lock it.

Stop and post a summary. Format:
  VERIFIED: [list of VERIFY items resolved]
  OPEN: [list of VERIFY items still unresolved — ask me]
  SURPRISES: [anything found that differs from docs/00-research.md]
  ADR-2 DECISION: [your Cashier collision finding]

---

### Phase 1 — Grill and spec

Read: docs/01-prd.md, docs/02-architecture.md, docs/00-audit.md, AGENTS.md Part 1.

Step 1 — Grill the architecture
Run /grill-with-docs on docs/01-prd.md then docs/02-architecture.md.
Challenge ADR-1 through ADR-8 individually. For each:
  - What could go wrong with this decision?
  - What alternatives exist?
  - What information do I need from Shafat to finalise it?
Present your challenges. Wait for my answers before finalising.

Step 2 — Build shared vocabulary
Write GLOSSARY.md with precise definitions (1-2 sentences each) for:
Workspace, Member, Plan, PlanPrice, Feature, Entitlement, Subscription, Payment,
Gateway, WebhookEvent, GracePeriod, UsageRecord, Coupon, Invoice, Device, AuditEntry.

Step 3 — Write ADRs
For each ADR-1 through ADR-8, write docs/adr/ADR-00N-title.md with:
  Status / Context / Decision / Consequences / Alternatives considered

Step 4 — Write the spec
/to-spec covering the full boilerplate. The spec must reference AGENTS.md Part 1
to distinguish Tyro-native features (configure only) from features we build.

Step 5 — Write the ticket list
/to-tickets producing vertical tracer-bullet tickets with:
  - One ticket per screen-plus-backend slice (not per layer)
  - Blocking edges declared explicitly
  - Each ticket notes: Tyro scaffold command to use (if any), HasCrud (if applicable),
    or "custom" with the Action/Service name to create

Do NOT write any implementation code in this phase.

---

### Phase 2 — Foundation: settings, workspaces, roles

Read: AGENTS.md Part 1 (scaffold commands, HasAuditTrail, menu.php), docs/03-data-model.md.

Checkpoint: php artisan tyro-checkpoint:create "phase-2-start"

Implement these tickets (TDD with Pest throughout):

1. Encrypted app settings store
   - settings table migration + model with HasAuditTrail
   - Settings service (get/set with encryption for secret group)
   - HasCrud resource: create-admin-page "app-settings", override field types appropriately
   - Seed default values (trial_length_days=14, grace_period_days=5, etc.)

2. Tyro staff roles and privileges seeded
   - Seeder for roles: super-admin, admin, support, finance
   - Seeder for privileges: billing.view, billing.refund, plans.manage, coupons.manage,
     workspaces.manage, webhooks.replay, settings.manage, support.reply, impersonate, audit.view
   - Wire privileges to roles as per docs/05-screens.md Staff privileges section
   - Confirm admin_roles in config/tyro-dashboard.php is correct

3. Personal workspace auto-creation on registration
   - VERIFY which Tyro Login event fires on registration (php artisan route:list --path=api,
     read Tyro Login source). Listen to it in a WorkspaceServiceProvider listener.
   - Create personal Workspace and owner WorkspaceMember in the listener.
   - Test: after registration, user has exactly one workspace with role=owner.

4. Workspaces + workspace_members + workspace_invitations
   - Migrations for all three tables (docs/03-data-model.md)
   - Eloquent models with HasAuditTrail on Workspace
   - WorkspacePolicy (view, update, delete, manageMember, invite) — test each
   - WorkspaceService with: create, switch, transferOwnership, suspend, delete

5. Workspace UI
   - create-user-page "workspace-settings" → WorkspaceSettingsController
   - create-user-page "team" → TeamController (members list, invite, change role, remove)
   - Workspace switcher header component (inject via Tyro layout stack)
   - Menu items added to config/menu.php userMenuItems

6. Extend Tyro invitation system for workspace invitations
   - Add workspace_id to invitation flow using the native invitation_links table where possible
   - Invitation accept endpoint creates WorkspaceMember record

HasAuditTrail: Workspace (create, update, delete, suspend, transferOwnership, memberAdded, memberRemoved).
showDanger: workspace delete, member remove, ownership transfer.
Close phase: php artisan pint && static analysis && full test suite && /code-review.

---

### Phase 3 — Plans, features, entitlements, usage

Read: AGENTS.md (Entitlements rules), docs/03-data-model.md, docs/04-billing.md pricing section.

Checkpoint: php artisan tyro-checkpoint:create "phase-3-start"

1. Plans, prices, features admin resources (HasCrud)
   - Migrations: plans, plan_prices, features, plan_features
   - Plan model + HasCrud: $resourceFieldOverrides for is_public (boolean), sort (number)
     hide gateway_price_id in index, add publish/unpublish action (not delete if active subscribers)
   - PlanPrice model + HasCrud: amount_minor (number type, min:0), gateway (select with enum options),
     interval (select: monthly/annual), price_version (readonly in edit)
   - Feature model + HasCrud: type (select: boolean/limit/metered), unit field
   - PlanFeature: inline editing on Plan detail page (not a separate HasCrud)
   - All three in config/menu.php adminMenuItems

2. Seed from config/saas-plans.php
   - Create config/saas-plans.php with placeholder prices from docs/04-billing.md
   - PlanSeeder reads it and upserts plans, prices, features — idempotent (safe to re-run)

3. Entitlements service
   - Entitlements::for(Workspace $w)
     ->can(string $featureKey): bool
     ->limit(string $featureKey): int|null  (null = unlimited)
     ->remaining(string $featureKey): int|null
   - Reads from active subscription → plan_price → plan → plan_features
   - Falls back to Free plan entitlements if no active subscription
   - Cache per workspace per request (not persisted — clears on subscription change event)

4. entitled: middleware
   - Route middleware: entitled:feature_key (aborts 403 if workspace cannot use feature)
   - @entitled('feature_key') Blade directive

5. Usage metering
   - UsageService::increment(Workspace $w, string $featureKey, int $by = 1): void
   - UsageService::get(Workspace $w, string $featureKey, string $period = null): int
   - Period defaults to current YYYY-MM. Resets automatically at period start.
   - usage_records table: upsert on workspace_id + feature_id + period

6. Downgrade pre-check
   - DowngradeChecker::check(Workspace $w, Plan $targetPlan): array of warnings
   - Used before confirming a plan downgrade in billing UI (Phase 7)

Property-style tests: entitlements at limit boundary, unlimited feature, grandfathering
(subscription at old price_version still gets old limits), usage reset at period start.
No gateway code in this phase.
Close: pint + static analysis + full suite + /code-review.

---

### Phase 4 — Billing core + Stripe adapter

Read: docs/04-billing.md (all sections), AGENTS.md (security rules, idempotency).

Checkpoint: php artisan tyro-checkpoint:create "phase-4-start"

1. Billing core
   - Migrations: subscriptions, payments, refunds, invoices, gateway_customers, webhook_events
   - PaymentGateway interface (docs/04-billing.md gateway contract section — implement exactly)
   - GatewayManager service: resolves gateway by key, registered in service provider
   - Subscription state machine: implement all states and valid transitions
     (trialing→active, active→past_due, past_due→canceled, etc.)
   - Domain events: SubscriptionActivated, SubscriptionCanceled, SubscriptionPastDue,
     TrialEnding, PaymentSucceeded, PaymentFailed, RefundIssued, PlanChanged
   - Invoice service: sequential number generator (INV-YYYY-NNNNNN locked counter in settings),
     PDF generation (use barryvdh/laravel-dompdf or similar — check Laravel 13 compat first)
   - webhook_events pipeline: verify → unique store (gateway+event_id) → dispatch queued job
     → idempotent processing (check status before acting)

2. Stripe adapter
   - Use stripe/stripe-php (or cashier-stripe if ADR-2 permits — apply ADR-2 decision here)
   - Implement ALL PaymentGateway interface methods for Stripe
   - Checkout: Checkout Session in subscription mode, success/cancel URLs
   - Portal: Customer Portal URL generation
   - Webhooks: verify HMAC signature, map events to domain events
   - Check current Stripe event names via Context7 (do not use training memory for event names)
   - Fakes: StripeGatewayFake implementing PaymentGateway for tests
   - Stripe CLI recipe: document in docs/stripe-local.md

3. /wizard script for Stripe setup
   Run wizard to produce an interactive checklist for these human-only steps:
     - Create products and prices in Stripe dashboard
     - Register webhook endpoint and note the signing secret
     - Add STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET to .env
   Save the output as docs/wizard-stripe.md.

NEVER use live Stripe keys in this phase. Sandbox only.
HasAuditTrail: Subscription (all state changes), Payment (created), Refund (issued).
Close: pint + static analysis + full suite (all billing paths via fake) + /code-review.

---

### Phase 5 — Paddle adapter

Read: docs/04-billing.md (Paddle section), docs/adr/ (ADR-2, ADR MoR tax).

Checkpoint: php artisan tyro-checkpoint:create "phase-5-start"

Step 1 — Research first (use Context7)
  Read current Paddle Billing API docs: webhook signature scheme, subscription event names,
  transaction event names, customer creation flow, custom_data field spec.
  Do NOT rely on training memory for Paddle API details.

Step 2 — Architecture decision
  Propose overlay checkout vs hosted checkout with trade-offs for this boilerplate.
  Present to me and wait for my choice before implementing.

Step 3 — Implement
  Paddle adapter implementing the same PaymentGateway interface and passing
  the same contract test suite used for Stripe.
  Pass workspace_id in custom_data on every checkout session.
  Handle: subscription.created, subscription.updated, subscription.canceled,
  transaction.completed, transaction.payment_failed (confirm names via Context7).
  Idempotent on replay. MoR tax: record on invoices that tax is handled by Paddle.

Step 4 — /wizard for Paddle dashboard setup
  Save output as docs/wizard-paddle.md.

Step 5 — ADR
  Write docs/adr/ADR-009-paddle-mor-tax.md documenting that Paddle handles VAT/GST
  and what this means for invoice format and reporting.

NEVER use live Paddle credentials. Sandbox only.
Close: pint + static analysis + full suite + /code-review.

---

### Phase 6 — SSLCommerz adapter

Read: docs/04-billing.md (SSLCommerz section), AGENTS.md (SSLCommerz security rules).

Checkpoint: php artisan tyro-checkpoint:create "phase-6-start"

Step 1 — Package evaluation
  Evaluate sslcommerz/laravel vs rayhan-bapari/sslcommerz vs thin in-house client:
  - Check maintenance (last commit date, open issues), license, Laravel 13 compatibility
  - Read the IPN + val_id implementation in each
  - Recommend one and write docs/adr/ADR-010-sslcommerz-package.md with your reasoning.
  Wait for my approval before implementing.

Step 2 — VERIFY recurring before touching it
  ASK ME: "Is the SSLCommerz schedule parameter enabled on your merchant account?"
  If YES: implement gateway-driven recurring with encrypted schedule parameter.
  If NO (default): skip schedule parameter entirely, implement app-driven renewals only.

Step 3 — Implement checkout + IPN
  Rules (all mandatory — see AGENTS.md security section):
  1. Never grant access from browser redirect URL — display status only
  2. On IPN: verify hash signature first
  3. Call /validator/api/validationDetails/ with val_id
  4. Match amount + currency + tran_id against pending payment record
  5. Only then update payment and subscription state
  6. Store raw IPN payload in webhook_events.payload (raw json column)
  7. Idempotent on tran_id
  8. Exclude /webhooks/sslcommerz/* from CSRF middleware
  9. Sandbox credentials only — never live in development

Step 4 — App-driven renewals (always implement regardless of recurring answer)
  billing:renewals scheduler command:
  - Runs daily
  - 7, 3, 1 days before current_period_end: queue RenewalReminderEmail with payment link
  - On current_period_end: transition to past_due
  - After grace_period_days (from settings): transition to canceled, fire SubscriptionCanceled
    event which triggers entitlement revocation (data kept, features locked)

Step 5 — /wizard for SSLCommerz merchant setup
  Save output as docs/wizard-sslcommerz.md.

Close: pint + static analysis + full suite + /code-review.

---

### Phase 6.5 — Design system

Full prompt is in docs/07-design-system.md. Read it and follow exactly.
Gate before proceeding to Phase 7: design-critique audit at 375, 768, 1024, 1440 px + dark mode.
No critical findings allowed.

---

### Phase 7 — User billing UI

Read: docs/05-screens.md (user dashboard section), design-system/<project>/MASTER.md.

Checkpoint: php artisan tyro-checkpoint:create "phase-7-start"

Build each screen using the scaffold command noted in docs/05-screens.md.
For every screen:
  - Controller calls an Action/Service — no logic in the controller
  - Reuse Blade components from the design system component kit (Phase 6.5)
  - Empty state, loading state, error state required
  - showDanger/showConfirm for all destructive actions

Screens in this phase (in order):
  1. Billing overview (usage meters, plan badge, gateway badge, renewal countdown)
  2. Change plan (plan cards with current highlighted, proration preview via gateway capabilities())
  3. Checkout (gateway selector by currency: USD→Stripe/Paddle, BDT→SSLCommerz; coupon field)
  4. Invoices & receipts (paginated list, PDF signed URL download)
  5. Payment methods (conditional on gateway capabilities().portal)
  6. Cancel flow (reason survey with required selection, downgrade offer, showDanger confirm)
  7. Payment status pages (success, failed, pending — never grant access here, display status only)

Gate: run design-critique at 375, 768, 1024, 1440 px + dark mode after all screens done.
Fix all critical findings before closing. Close: pint + static analysis + full suite + /code-review.

---

### Phase 8 — Admin console

Read: docs/05-screens.md (admin dashboard section), AGENTS.md Part 1 (native Tyro routes).

Checkpoint: php artisan tyro-checkpoint:create "phase-8-start"

Tyro-native screens (configure/theme only — do NOT rebuild):
  Users, Roles & privileges, Audit trail, Tyro settings pages
  For impersonation: add audit entry listener + billing block middleware + UI banner

Build these screens using scaffold commands from docs/05-screens.md:
  1. Overview/analytics — document ALL metric formulas as inline comments in the controller:
     MRR = sum of active monthly-normalised subscription amounts
     ARR = MRR x 12
     Churn rate = canceled this period / active at start of period
     Trial conversion = converted trials / started trials (same period)
  2. Workspaces (search + detail with actions — use showDanger for suspend/delete)
  3. Subscriptions (filter + state timeline)
  4. Payments & refunds (billing.refund privilege check, showDanger confirm, HasAuditTrail)
  5. Invoices (list + regenerate PDF)
  6. HasCrud resources: Plan, PlanPrice, Feature, Coupon, Announcement
     (use $resourceFieldOverrides, $resourceRoles as needed — see docs/00-research.md HasCrud section)
  7. Webhook events (replay button requires webhooks.replay privilege, must be idempotent)
  8. Gateway settings (secrets encrypted in settings table, showDanger on live-mode switch)
  9. Email templates & log
  10. Support inbox
  11. System health
  12. App settings
  13. Data requests

Gate: design-critique at 375, 768, 1024, 1440 px + dark mode.
Close: pint + static analysis + full suite + /code-review.

---

### Phase 9 — Mobile API v1

Read: docs/06-api.md, AGENTS.md (API rules section).

Checkpoint: php artisan tyro-checkpoint:create "phase-9-start"

Step 1 — Tyro Login API surface audit
  php artisan route:list --path=api
  List every endpoint Tyro Login already ships. These are REUSED, not rebuilt.
  Document in docs/api-auth-surface.md.

Step 2 — Implement /api/v1 endpoints
  Follow endpoint groups in docs/06-api.md.
  Every endpoint: thin controller → Action → API Resource response.
  Apply workspace isolation (X-Workspace-Id header middleware).
  Apply Sanctum ability checks per endpoint.
  Apply rate limits (per token + per IP).
  Idempotency-Key middleware on payment and subscription POSTs.
  One error envelope for all errors.

Step 3 — OpenAPI spec
  Install Scramble or Scribe (check Laravel 13 support via Context7 first — record choice as ADR).
  Generate spec. Publish at /docs/api.
  Verify spec covers all endpoints with correct request/response shapes.

Step 4 — Contract tests
  For every endpoint test:
    - Unauthenticated → 401
    - Wrong workspace → 403 (workspace isolation)
    - Missing ability → 403
    - Invalid input → 422 with error envelope
    - Valid input → correct response shape
  Do NOT implement store billing. Document the open decision in docs/adr/.

Close: pint + static analysis + full suite + /code-review.

---

### Phase 10 — Notifications, email, support

Read: docs/04-billing.md (transactional emails list), docs/05-screens.md (notification screens).

Checkpoint: php artisan tyro-checkpoint:create "phase-10-start"

1. Transactional emails (all 18 from docs/04-billing.md)
   - Queued Mailable per email type
   - Match Tyro Login email template visual style (read its template partials first)
   - Previewable via admin email-templates screen built in Phase 8
   - Test ALL with Mail::fake — assert correct mailable dispatched with correct data

2. In-app notifications
   - notifications table (Laravel native or custom)
   - NotificationService::send(User $u, string $type, array $data)
   - Notification preferences (per type: email on/off, push on/off) stored in user settings
   - API endpoint: GET /api/v1/notifications, PATCH /api/v1/notifications/{id}/read
   - Blade: notifications list in user dashboard (Phase 7 scaffold extended)

3. Push notification stub
   - PushNotificationDriver interface with send(Device $d, string $title, string $body, array $data)
   - FCMDriver implementing it (stub — does not need to send real FCM in tests)
   - Bound in AppServiceProvider; swap in tests with a fake

4. Support ticket system
   - support_tickets + ticket_messages migrations + models
   - SupportTicketService: create, reply, close
   - User UI: create-user-page "support" screens (Phase 7 was placeholder — wire up here)
   - Admin UI: support-inbox screen (Phase 8 was placeholder — wire up here)

5. AdminNotice integration
   - TrialEndingNoticeMiddleware: if workspace trial ends in 3 days, call AdminNotice::show()
   - PastDueBannerMiddleware: if subscription is past_due, show payment link banner
   - Register both in user dashboard middleware group

Close: pint + static analysis + full suite (Mail::fake, notification assertions) + /code-review.

---

### Phase 11 — Public site

Read: docs/05-screens.md (public site section), design-system/<project>/MASTER.md.

Checkpoint: php artisan tyro-checkpoint:create "phase-11-start"

Propose 2 visual directions with /prototype before building. Wait for my choice.

Build order:
  1. Landing page (outside Tyro layouts — own Blade layout for public site)
  2. Pricing page (plan cards + feature comparison table queried from DB,
     monthly/annual toggle with Alpine.js, USD/BDT toggle, CTA → register)
  3. Contact page (rate-limited + captcha → creates support ticket, success message)
  4. Legal pages (Terms, Privacy, Refund policy — flag each with a comment: LAWYER REVIEW REQUIRED)
  5. Changelog / docs (markdown files in resources/docs/, rendered with a simple parser)
  6. Status page (link to external provider or simple ping-based page)

SEO for every public page:
  - Unique <title> and meta description
  - OpenGraph tags (og:title, og:description, og:image)
  - Canonical URL
  - sitemap.xml generated dynamically
  - No render-blocking resources, lazy-load images

Performance gate: Lighthouse score >= 90 on mobile for landing and pricing pages.
Design gate: design-critique at 375, 768, 1024, 1440 px + dark mode.
Close: pint + static analysis + full suite + /code-review.

---

### Phase 12 — Hardening

Checkpoint: php artisan tyro-checkpoint:create "phase-12-start"

Step 1 — Security review (use security skill + manual)
  Check each category and document findings in docs/security-review.md:
  - IDOR: can workspace A access workspace B's billing, invoices, members, API keys?
  - Mass assignment: every model has correct $fillable or $guarded
  - Webhook/IPN forgery: all three gateways reject forged signatures
  - CSRF exclusion list: only /webhooks/* routes are excluded — nothing extra
  - Rate limits: verify all required routes are limited (auth, API, webhooks, contact)
  - Secrets in logs: grep for key/secret/password in log::* calls
  - File upload: MIME + extension validation, stored outside webroot
  - Impersonation abuse: cannot issue refund or change plan while impersonating
  - Session + token expiry: Sanctum tokens have TTL set
  - Security headers: CSP, HSTS, X-Frame-Options, X-Content-Type-Options set in middleware

Step 2 — Dependency audit
  composer audit
  npm audit
  Fix all critical and high severity findings. Document any accepted risks.

Step 3 — Architecture health
  /improve-codebase-architecture
  Fix the top findings. Document what was improved.

Step 4 — Coverage push
  Identify billing paths below 80% coverage. Write tests to close the gap.

Step 5 — CI pipeline
  Create .github/workflows/ci.yml with jobs:
  - PHP lint: vendor/bin/pint --test
  - Static analysis: php artisan larastan (at level set in ADR)
  - Tests: php artisan test --parallel
  - Security: composer audit && npm audit (fail on critical/high)
  All jobs must pass on every PR before merge.

Output: docs/security-review.md with finding, severity, status (fixed/accepted/deferred).
Close: full test suite green + CI passing + /code-review.

---

### Phase 13 — Docs and release

Checkpoint: php artisan tyro-checkpoint:create "phase-13-start"

Write these documents:

README.md
  - What the boilerplate is and what it ships
  - Prerequisites (PHP, Node, MySQL, Redis, Composer, npm)
  - Quick start (clone, env, install, migrate, seed, serve)
  - Link to all docs/ files

docs/DEPLOY.md
  - Nginx config (PHP-FPM, SSL, gzip)
  - PHP-FPM pool settings
  - Redis config (queues, cache, sessions)
  - Queue workers: Horizon or Supervisor config with restart on deploy
  - Scheduler: cron entry
  - Backups: what to back up (DB + storage/), how to test restore
  - Environment variables: full .env reference with descriptions
  - Webhook URLs for each gateway and how to register them
  - Go-live checklist (from docs/10-quality-gates.md)

docs/NEW-SAAS-GUIDE.md
  - How to create a new SaaS product from this boilerplate
  - Step 1: clone/fork, rename app
  - Step 2: update config/saas-plans.php with real plan names and prices
  - Step 3: run PlanSeeder
  - Step 4: create your first feature with HasCrud (example walkthrough)
  - Step 5: scaffold your first custom page (example walkthrough)
  - Step 6: run Phase 0 prompt to set up agent for the new product
  - Phase prompts reference

CHANGELOG.md (v0.1.0 entry documenting all phases completed)

Then:
  Git tag v0.1.0
  Write scripts/bootstrap.sh (or Makefile fresh target):
    - Copies .env.example to .env
    - Installs composer + npm dependencies
    - Generates app key
    - Runs migrations
    - Seeds demo data (plans, one demo workspace, one admin user)
    - Runs php artisan tyro-checkpoint:create "fresh-install"

Close: /code-review + confirm CI green.

---

### Phase 14 — Platform extras (P2, v0.2 — optional, post-launch)

Read: docs/00-research.md (reference-SaaS section), docs/03-data-model.md (Platform extras table),
docs/05-screens.md (Platform extras section), docs/02-architecture.md ADR-9 and ADR-10.

Everything here is deferred work queued from researching github.com/itsmeshafat/Laravel-SAAS-Starter.
None of it blocks v0.1. Pick items individually — this is not one ticket list, it's a backlog.
Do not start any of these before v0.1 (Phases 0-13) is shipped and tagged.

Suggested build order (cheapest/most-reusable first):
  1. Feature flags (Pennant) — ADR-9. VERIFY workspace-scope resolution before building UI.
  2. Email templates (ours), changelog, status page — plain CRUD/content, no new architecture.
  3. Feedback widget + inbox — small, isolated.
  4. Onboarding checklist, advanced search — extend existing Phase 2/7 screens.
  5. Custom fields, tags, workspace templates — workspace metadata layer.
  6. Activity comments & reactions, internal announcements — collaboration layer.
  7. Connected accounts, login activity log — extend existing auth screens.
  8. Outbound webhooks — ADR-10. New queue, contract test the retry/backoff logic same as
     the gateway webhook test matrix in docs/10-quality-gates.md.
  9. System ops: health monitor (already Phase 8), scheduled tasks monitor, data retention,
     SEO management, maintenance mode.
  10. CSV import, bulk user actions, app log viewer, localization management.

For each item: checkpoint before starting, TDD with Pest, scaffold command per docs/05-screens.md,
HasAuditTrail on anything admin-mutable, close with pint + static analysis + full suite + /code-review.
Update docs/00-research.md / 03-data-model.md / 05-screens.md if the actual build differs from the plan.

---

### Bug-hunt loop (use anytime)

Use the diagnosing-bugs skill.

Step 1: Reproduce with a failing test. Show me the exact test and the error output.
Step 2: List 3-5 hypotheses about the root cause (most likely first).
Step 3: Instrument the code to confirm which hypothesis is correct. Show me the evidence.
Step 4: Write the minimal fix (no unrelated changes in the same commit).
Step 5: Write a regression test that would have caught this bug.
Step 6: Run the full test suite. Run /code-review on the fix.
Report: red test → evidence → fix → green test → review. Nothing else in the commit.
