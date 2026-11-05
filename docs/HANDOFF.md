# Agent Handoff — saas-boilerplate-laravel

**Last updated:** 2026-11-04  
**Last commit:** `3c0f367` — Phase 6.5: Design system — Cobalt Pro

---

## What is this project?

A Laravel 13 / PHP 8.5 SaaS boilerplate with multi-gateway billing (Stripe, Paddle, SSLCommerz), multi-workspace support, entitlements, invoice generation, and a Cobalt Pro design system. Built on `hasinhayder/tyro-dashboard` v1.60.0.

Key doc files in `docs/`:
- `01-prd.md` — product requirements
- `02-architecture.md` — ADRs 1–8
- `03-data-model.md` — full schema
- `04-billing.md` — billing layer contract
- `05-screens.md` — screen inventory and scaffold commands
- `09-phase-prompts.md` — phase-by-phase build instructions (the master guide)
- `tickets.md` — ticket list T-001 through T-036

---

## Completed phases

| Phase | Commits | Tickets done |
|-------|---------|--------------|
| Phase 2 — Foundation | ✓ | T-001–T-005 |
| Phase 3 — Plans & entitlements | ✓ | T-006–T-008 |
| Phase 4 — Billing: Stripe | ✓ | T-009–T-015 |
| Phase 5 — Billing: Paddle | ✓ | T-016 |
| Phase 6 — Billing: SSLCommerz + renewals | ✓ | T-017–T-018 |
| Phase 7 — Invoices & receipts (backend) | ✓ | T-019–T-020 |
| Phase 6.5 — Design system (Cobalt Pro) | ✓ | — |
| T-021 Coupons + Phase 7 user billing UI | ✓ (2026-11-05) | T-021 + billing screens |

---

## Critical architectural decisions (read before touching billing)

- **ADR-002**: Custom billing layer — no Laravel Cashier. Own tables: plans, plan_prices, subscriptions, payments, invoices, gateway_customers, webhook_events. `PaymentGateway` interface with adapters.
- **ADR-007**: Money as integer minor units (BIGINT UNSIGNED) + ISO currency code. **No floats anywhere.**
- **ADR-008**: Webhook idempotency via `UNIQUE(gateway, event_id)`. Store-then-process. HMAC verify before storage.
- **ADR-006**: Entitlements as single source of truth.

Security rules (non-negotiable):
- Gateway keys/secrets only in `.env` or encrypted DB settings — never logged
- Never trust browser redirect URL to confirm payment
- SSLCommerz IPN: verify hash → call `/validator/api/validationDetails/` → match amount/currency/tran_id
- Every incoming webhook stored in `webhook_events` with `UNIQUE(gateway, event_id)` before processing

---

## Current state of migrations

All migrations are run (including coupons/coupon_redemptions, 2026-11-05).

## Done on 2026-11-05 (T-021 + Phase 7 user billing UI)

- `GatewayManager` (`app/Services/GatewayManager.php`): resolve by key, `all()`, `default()`, `forCurrency()` (BDT→SSLCommerz). Singleton.
- T-021 Coupons: `coupons` + `coupon_redemptions` tables, `Coupon` (HasCrud, admin-gated, audit, delete-guard), `CouponRedemption`, `ApplyCoupon` action (`execute()` validates, `record()` is atomic), `CouponFactory`. Appears in admin sidebar under Resources automatically.
- Phase 7 screens (routes in `routes/web.php` auth group, views in `resources/views/dashboard/`):
  - `GET dashboard/billing` — overview (plan, status pill, renewal, usage meters, empty state)
  - `GET dashboard/billing/plans` — plan cards, USD/BDT toggle, monthly/annual Alpine toggle
  - `GET/POST dashboard/billing/checkout` — currency→gateway routing, coupon validated via `ApplyCoupon` before redirect (redemption recorded only by webhook)
  - `GET dashboard/billing/payment-methods` — portal link or last payment
  - `GET/POST dashboard/billing/cancel-subscription` — reason survey, downgrade offer, showDanger, `CancelSubscription` action (blocked while impersonating, audited, reason in metadata)
  - `success.blade.php` — "processing" only, never confirms status
- Fake gateway for tests: `tests/Fakes/FakeGateway.php` (bind over `\App\Gateways\StripeGateway::class`).

**Phase 8 admin console — DONE (2026-11-05):**
- Controllers in `app/Http/Controllers/Admin/`: AdminOverviewController (MRR/ARR/churn/trial-conversion formulas documented inline), AdminWorkspacesController (search/detail/suspend/restore/force-plan), AdminSubscriptionsController (filters + timeline), AdminPaymentsController (refund via `billing.refund` privilege, blocked while impersonating, audited), WebhookEventsController (viewer + idempotent replay via `webhooks.replay`), GatewaySettingsController (secrets encrypted via SettingsService, masked display, `settings.manage`), SystemHealthController (queue/failed jobs/disk/db).
- `PaymentGateway::refundPayment()` added to contract; Stripe implemented, Paddle/SSLCommerz throw (issue via dashboards).
- `Workspace::latestSubscription()` hasOne-of-many for eager loading (activeSubscription() is NOT a relation).
- `SettingsService::set()` now creates rows (optional `secret:` param).
- `admin_roles` config now includes `finance` + `support` (console entry; actions still privilege-gated).
- 15 new tests in AdminConsoleTest; suite 93/93.

**Phase 9 mobile API — DONE (2026-11-05):**
- `/api/v1` in routes/api.php: me (+password/sessions), devices (push tokens), workspaces CRUD + members/invitations, plans, billing (subscription/usage/invoices/payments/checkout/cancel/resume/change-plan), app/config.
- Middleware: `workspace.context` (X-Workspace-Id vs membership, cached), `idempotency` (required header on payment POSTs). Error envelope in bootstrap/app.php. Rate limits per token (60/min) + per IP (240/min).
- Tyro Login API surface audited and documented in `docs/api-auth-surface.md` — auth reused (`POST /api/login`), no forking.
- Checkout returns hosted-checkout URL for in-app browser — no native card form, no store billing (open decision documented in 06-api.md).
- 19 contract tests in ApiV1Test; suite 112/112.
- NOT done: OpenAPI spec (Scramble/Scribe — needs user approval for new dependency); P1 endpoints (notifications, support, data rights); ETag caching (T-025 follow-up).

**Phase 10 notifications/email/support — DONE (2026-11-06):**
- 12 queued transactional mailables on a shared `TransactionalMail` base + Tyro-style table layout (`emails.layouts.base`): TrialStarted/TrialEnding/PaymentSucceeded/PaymentFailedMail/PlanChanged/SubscriptionCanceledMail/SubscriptionResumed/RefundIssued/CardExpiring/AccountDeletionConfirmed/DataExportReady (+ existing SubscriptionRenewalReminder). Wired to SubscriptionActivated/PaymentFailed/SubscriptionCanceled events in WorkspaceServiceProvider.
- In-app notifications: `notifications` table + `AppNotification` model, `NotificationService` (in-app row + push via `PushNotificationDriver` interface, `FCMDriver` stub bound in container), per-type push/email prefs in settings (`user.{id}.notify.{type}.{push|email}`). API: GET/PATCH notifications + preferences (list shape, no dotted keys). User screen at /dashboard/notifications.
- Support: `support_tickets` (TKT-YYYY-NNNNN via lockForUpdate counter) + `ticket_messages`, `SupportTicketService` (create/reply/close, staff reply → customer notification; customer reply → pending; closed tickets immutable). User screens /dashboard/support(+/{ticket}); admin inbox /dashboard/support-inbox (reply/close behind support.reply, showDanger on close).
- Email templates admin: /dashboard/email-templates list + per-template iframe preview with sample data + "send test to me" (settings.manage).
- AdminNotice middlewares appended to web group: TrialEndingNotice (≤3 days left → amber banner w/ plan link), PastDueBanner (red banner w/ payment-method link).
- 12 new tests; suite 124/124. Push faked via `tests/Fakes/FakePushDriver`.

**Next: Phase 11 public site** — landing/pricing/contact/legal/changelog with SEO; spec requires proposing 2 visual directions with /prototype before building and waiting for user choice.

---

## Design system (Phase 6.5 — DONE)

**Direction A — Cobalt Pro.** Primary: `oklch(0.546 0.237 258)` (#2563eb).

Key files:
- `design-system/saas-boilerplate/MASTER.md` — canonical token reference + contrast table
- `docs/design-system-tokens.md` — usage guide
- `.ai/skills/design-rules/SKILL.md` — **load this before every UI task**
- `resources/views/vendor/tyro-dashboard/partials/shadcn-theme.blade.php` — dashboard CSS vars
- `resources/css/app.css` — Tailwind `@theme` + `@layer base` vars

Component kit at `resources/views/components/ui/`:
- `x-ui.button` (variant: primary/secondary/outline/ghost/danger/link; size: sm/md/lg)
- `x-ui.input` (label, error, hint)
- `x-ui.card` (header slot, footer slot)
- `x-ui.badge` (variant: default/secondary/success/warning/danger/info/muted/outline)
- `x-ui.status-pill` (maps subscription status → badge variant automatically)
- `x-ui.empty-state` (title, description, icon slot, action slot)
- `x-ui.usage-meter` (label, used, limit, unit — bar turns amber at 70%, red at 90%)
- `x-ui.plan-card` (plan, price, featured, current)

**Rule:** No hard-coded hex in Blade views. Use `bg-[var(--primary)]` etc.

---

## What's next — Phase 7 (User billing UI) + T-021 (Coupons)

The `docs/09-phase-prompts.md` Phase 7 section is the authoritative guide. Summary:

### T-021: Coupons (do this first — checkout depends on it)

Create:
1. **Migration:** `coupons` table — `code (unique), type (percent|fixed), value, currency (nullable), plan_ids (json nullable), max_redemptions (nullable), redeemed_count default 0, expires_at (nullable), is_active bool, timestamps`
2. **Migration:** `coupon_redemptions` table — `coupon_id FK, workspace_id FK, gateway_coupon_id (nullable), amount_off_minor (nullable unsigned bigint), applied_at timestamp, timestamps`
3. **Model:** `Coupon` with `HasCrud` (like `Plan` model pattern). Validation: prevent delete if has redemptions, check expiry + max_redemptions on apply.
4. **Model:** `CouponRedemption` with `BelongsTo Coupon, Workspace`
5. **Action:** `ApplyCoupon::execute(Workspace $w, string $code, ?PlanPrice $price): Coupon` — validates: active, not expired, not over limit, currency match if fixed, plan restriction if set. Throws `\RuntimeException` with message on failure.
6. **Admin resource:** follow `Plan` HasCrud pattern. Add to `docs/05-screens.md` admin screen (Coupons row). Wire into `config/menu.php` admin.

### Phase 7 screens (after T-021)

All screens use `x-ui.*` components. Load `.ai/skills/design-rules` first.

**1. Billing overview** (`create-user-page "billing"`)
- Route: `GET dashboard/billing` → `BillingOverviewController@show`
- Shows: current plan name + `x-ui.status-pill`, next renewal date, gateway badge, trial countdown (if trialing), `x-ui.usage-meter` per feature, action buttons (Upgrade, Manage billing portal, Cancel)
- Data: `$workspace->activeSubscription()->load('plan.planFeatures.feature')`, `app(EntitlementService::class)->for($workspace)`
- Empty state: `x-ui.empty-state` with "No active plan" and CTA to plans page

**2. Plans / change plan** (`create-user-page "plans"`)
- Route: `GET dashboard/billing/plans` → `PlansController@show`
- Shows: `x-ui.plan-card` for each public plan. Alpine.js monthly/annual toggle. Current plan highlighted (`current: true`). Currency selector (USD/BDT).
- Currency routing: USD → Stripe/Paddle checkout, BDT → SSLCommerz checkout (pass `?currency=BDT` to checkout)

**3. Checkout** (already exists, needs update)
- Route stays: `GET/POST dashboard/billing/checkout`
- Add: currency-based gateway selector (USD → stripe/paddle prices, BDT → sslcommerz prices)
- Add: `ApplyCoupon` validation in `startCheckout()` — if coupon provided, validate it before redirect
- Improve UI with `x-ui.card`, `x-ui.button`, `x-ui.input`

**4. Payment methods** (`create-user-page "payment-methods"`)
- Route: `GET dashboard/billing/payment-methods` → `PaymentMethodsController@show`
- Logic: if `$gateway->capabilities()` has `'portal'` → show "Manage in billing portal" button (links to portal route). Otherwise show last payment info + "Pay now" for SSLCommerz.

**5. Cancel flow** (`create-user-page "cancel"` — name it `cancel-subscription` to avoid clash with existing `cancel.blade.php` which is "checkout cancelled")
- Route: `GET dashboard/billing/cancel-subscription` → `CancelSubscriptionController@show`
- Route: `POST dashboard/billing/cancel-subscription` → `CancelSubscriptionController@cancel`
- Requires active subscription. Shows reason survey (required radio: too expensive / missing features / switching to competitor / pausing / other). Downgrade offer if lower plan exists. `showDanger` confirm before submitting (wire Tyro's showDanger).
- Action: `CancelSubscription::execute(Workspace, string $reason, bool $immediately = false): void` — calls `$gateway->cancel($subscription, atPeriodEnd: true)`. Records reason in subscription metadata.

**6. Payment status pages** (improve stubs)
- `success.blade.php` — confirm "Payment received — do not refresh" message, link to billing overview. **Never display subscription status as confirmed here** — just show "we're processing."
- `cancel.blade.php` (the checkout-cancelled one) — already fine, minor UI polish

**7. Admin screens for Phase 8 (can queue for next session):**
See `docs/09-phase-prompts.md` Phase 8 section. Many screens need `create-admin-page` commands.

---

## Key models + patterns to follow

### Getting current workspace
```php
$workspace = $request->user()->currentWorkspace();
if (!$workspace) return redirect()->route('dashboard.workspace-settings');
```

### Getting active subscription
```php
$subscription = $workspace->activeSubscription(); // returns Subscription|null
// Loads: $subscription->load('plan.planFeatures.feature')
```

### Entitlements
```php
$entitlements = app(EntitlementService::class)->for($workspace);
// $entitlements->can('api_access') → bool
// $entitlements->limit('max_seats') → int|null
// $entitlements->remaining('api_calls_per_month') → int|null
```

### Subscription state machine (Subscription model)
```php
$subscription->activate(['current_period_start' => ..., 'current_period_end' => ...]);
$subscription->markPastDue();
$subscription->cancel(immediately: false); // sets cancel_at_period_end
$subscription->resume();
$subscription->expire();
```

### Gateway access
```php
// Resolve the active gateway (injected via DI from service provider)
$gateway = app(\App\Contracts\PaymentGateway::class);
// Or by key:
$gateway = app(\App\Services\GatewayManager::class)->resolve('stripe');
```

### Design system components
```blade
<x-ui.button variant="primary">Upgrade</x-ui.button>
<x-ui.status-pill :status="$subscription->status" />
<x-ui.usage-meter label="API Calls" :used="4200" :limit="10000" />
<x-ui.empty-state title="No active plan" description="Choose a plan to get started.">
    <x-slot:action><x-ui.button>View Plans</x-ui.button></x-slot:action>
</x-ui.empty-state>
```

---

## Important file locations

| What | Where |
|------|-------|
| PaymentGateway interface | `app/Contracts/PaymentGateway.php` |
| Gateway adapters | `app/Gateways/StripeGateway.php`, `PaddleGateway.php`, `SSLCommerzGateway.php` |
| GatewayManager | `app/Services/GatewayManager.php` |
| BillingController (checkout) | `app/Http/Controllers/BillingController.php` |
| InvoiceController | `app/Http/Controllers/InvoiceController.php` |
| Subscription model | `app/Models/Subscription.php` |
| Plan model (HasCrud example) | `app/Models/Plan.php` |
| Feature model | `app/Models/Feature.php` |
| EntitlementService | `app/Services/EntitlementService.php` |
| GenerateInvoice action | `app/Actions/GenerateInvoice.php` |
| shadcn CSS vars | `resources/views/vendor/tyro-dashboard/partials/shadcn-theme.blade.php` |
| Tailwind theme | `resources/css/app.css` |
| UI components | `resources/views/components/ui/*.blade.php` |
| Routes | `routes/web.php` |
| Menu config | `config/menu.php` |

---

## Commands to run at start

```bash
php artisan migrate          # run the 3 pending migrations
vendor/bin/pint --dirty      # after any PHP changes
php artisan test --compact   # run full suite
```

---

## Tyro scaffold commands to use

```bash
php artisan tyro-dashboard:create-user-page "billing"
php artisan tyro-dashboard:create-user-page "plans"
php artisan tyro-dashboard:create-user-page "payment-methods"
php artisan tyro-dashboard:create-user-page "cancel-subscription"
php artisan tyro-dashboard:create-admin-page "overview"
php artisan tyro-dashboard:create-admin-page "workspaces"
php artisan tyro-dashboard:create-admin-page "subscriptions"
php artisan tyro-dashboard:create-admin-page "webhook-events"
php artisan tyro-dashboard:create-admin-page "gateway-settings"
```

Each command scaffolds a controller + view. Wire them into `routes/web.php` and `config/menu.php`.

---

## Test setup

- PHPUnit (not Pest)
- SQLite in-memory for tests
- `php artisan test --compact` or `vendor/bin/phpunit`
- Gateway fake classes exist for Stripe — follow same pattern for Paddle/SSLCommerz tests
- Create factories for new models (Coupon, etc.)

---

## Notes / gotchas

1. **Pint:** Run `vendor/bin/pint --dirty --format agent` after every PHP file change.
2. **Frontend changes:** Run `npm run build` if Tailwind classes don't show, or ask user to run `npm run dev`.
3. **Currency routing in checkout:** USD → resolve gateway from `config/services` (stripe or paddle), BDT → always SSLCommerz. The `GatewayManager` service resolves by key string.
4. **Webhook CSRF exclusion:** Already configured in `bootstrap/app.php` — `/webhooks/*` routes are excluded.
5. **Invoice counter:** Uses `DB::table('settings')->lockForUpdate()` for concurrency safety — do not change this.
6. **No floats for money:** Always use `amount_minor` (integer) + `currency` (ISO code).
7. **DashboardColors:** Admin can override CSS vars at runtime via `storage/app/dashboard-colors.json`. Never hard-code hex in Blade.
