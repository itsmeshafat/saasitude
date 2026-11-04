# Ticket List — Laravel SaaS Boilerplate MVP (Phases 2–13)

Format: one ticket per screen+backend slice. Blocking edges declared explicitly.
Build types: [T] Tyro native · [C] HasCrud · [S] Scaffold+logic · [B] Custom Blade · [A] Action/Service only (no screen)

Phase 14 (P2) tickets live in `docs/09-phase-prompts.md` Phase 14 section.

---

## Phase 2 — Foundation

**T-001: App settings store**
Scope: `settings` migration, Settings model (HasAuditTrail, encrypted cast for secret group), `SettingsService::get(key)/set(key,value)`, seed defaults (trial_length_days=14, grace_period_days=5, invoice_prefix=INV), HasCrud admin screen at `/dashboard/resources/settings`.
Blocks: T-003 (settings needed for trial length), T-016 (invoice number counter)
Build: [A] SettingsService + [C] Settings HasCrud

**T-002: Seed staff roles and privileges**
Scope: Seeder for roles (super-admin, admin, support, user) and privileges (billing.view, billing.refund, plans.manage, coupons.manage, workspaces.manage, webhooks.replay, settings.manage, support.reply, impersonate, audit.view). Wire privileges to roles per `docs/05-screens.md`.
Blocks: T-003 (admin roles needed to gate workspace admin screens)
Build: [A] DatabaseSeeder extension

**T-003: Workspace create + switch**
Scope: `workspaces` migration + model (HasAuditTrail), auto-create personal workspace on `Registered` event, `CreateWorkspace` action, workspace switcher header component, middleware `SetActiveWorkspace` (reads from session, falls back to personal). No billing yet.
Blocks: ALL Phase 3+ tickets (workspace is the billing anchor)
Build: [S] + [A] CreateWorkspace

**T-004: Workspace settings**
Scope: `create-user-page "workspace-settings"`, form for name/slug/logo, `UpdateWorkspace` action, `TransferOwnership` action (requires showDanger confirm, target must be existing member), danger zone (delete workspace — requires no active paid subscription, showDanger).
Blocks: none
Depends on: T-003
Build: [S] + [A]

**T-005: Workspace members + invitations**
Scope: `workspace_members` migration + model, `workspace_invitations` migration + model, `InviteMember` action (extends Tyro invitation system — creates tyro invitation + our workspace_invitations row), `AcceptInvitation` action, member list page (create-user-page "team"), change role, remove member. Admin invitation management page (create-admin-page "workspace-members").
Depends on: T-003
Build: [S] + [A]

---

## Phase 3 — Plans & entitlements

**T-006: Plans + plan prices CRUD**
Scope: `plans`, `plan_prices` migrations + models (Plan HasAuditTrail), HasCrud admin resource for Plans with custom validation (prevent deleting plan with active subscribers, price_version bump on price change), nested plan prices form (per gateway, interval, currency, amount_minor).
Blocks: T-007, T-008 (entitlements and billing need plans/prices)
Build: [C+]

**T-007: Features + plan features**
Scope: `features`, `plan_features` migrations + models, HasCrud admin resource for Features (key, type, unit). Plan detail view includes feature assignment form (value per feature per plan). Seed example features: `max_seats` (limit), `api_access` (boolean), `api_calls_per_month` (metered).
Blocks: T-008
Build: [C+]

**T-008: Entitlements service**
Scope: `App\Services\Entitlements` (resolves workspace → active subscription → plan → plan_features, caches per request), `Entitlements` facade, `EnsureEntitlement` middleware, `@entitlement('key')` Blade directive, `EntitlementException` → 402 API / redirect web. Free plan fallback (always resolves, never null). Seed Free plan with minimal limits.
Blocks: T-009 (billing screens need entitlement checks), all feature-gated screens
Depends on: T-006, T-007
Build: [A] Service + [A] Middleware + [A] Facade

---

## Phase 4 — Billing: Stripe

**T-009: Gateway customers**
Scope: `gateway_customers` migration + model, `GatewayCustomerResolver::getOrCreate(Workspace, string $gateway)` service, called lazily before checkout. No UI.
Depends on: T-003
Build: [A]

**T-010: Stripe checkout**
Scope: Stripe adapter implementing `PaymentGateway` interface (`startCheckout`, `verifyWebhook`, `handleEvent`). `startCheckout` → Stripe Checkout Session (subscription mode, `trial_period_days` from settings). `checkout` user page (gateway selector by currency, coupon field, redirects to Stripe). `CheckoutOptions` DTO. Success/cancel pages.
Depends on: T-006, T-008, T-009
Build: [S] + [A] StripeGateway adapter

**T-011: Stripe webhooks**
Scope: `POST /webhooks/stripe` controller, HMAC signature verify, `WebhookEvent::firstOrCreate` idempotency (ADR-008), dispatch `ProcessWebhookEvent` job to `billing` queue. Job handles: `checkout.session.completed` → activate subscription, `customer.subscription.updated/deleted` → sync status, `invoice.paid` → record payment + generate invoice, `invoice.payment_failed` → `past_due`, `charge.refunded` → create Refund row.
Blocks: T-013 (state machine needs webhook handler)
Depends on: T-010
Build: [A] + queued job

**T-012: Stripe Customer Portal**
Scope: `portalUrl(Workspace, returnUrl)` on adapter, "Manage billing" button on billing overview page (redirects to Stripe-hosted portal). No local UI for payment method update.
Depends on: T-010
Build: [A]

**T-013: Subscription state machine + dunning**
Scope: `subscriptions` migration + Subscription model (HasAuditTrail, status enum cast), state transition methods (`activate()`, `markPastDue()`, `cancel()`, `cancelAtPeriodEnd()`, `resume()`). Each transition dispatches domain events (`SubscriptionActivated`, `PaymentFailed`, `SubscriptionCanceled`). `PastDueReminderJob` (3 emails over 7 days), `GracePeriodExpiryJob` (downgrade to Free after `grace_period_days`). Scheduler registration.
Depends on: T-011
Build: [A] + queued jobs

**T-014: Billing overview screen**
Scope: `create-user-page "billing"` — current plan, status pill (trialing/active/past_due), next renewal date, usage vs limit bars per feature, gateway badge, trial countdown, upgrade/downgrade/cancel links.
Depends on: T-008, T-013
Build: [S]

**T-015: Change plan + cancel flow**
Scope: `create-user-page "plans"` (plan cards, monthly/annual toggle, proration preview via `changePlan` capabilities check), `ChangePlan` action (immediate + proration for Stripe, at-period-end for downgrade). `create-user-page "cancel"` (reason survey, offer pause/downgrade, `CancelSubscription` action with showDanger).
Depends on: T-013, T-014
Build: [S] + [A]

---

## Phase 5 — Billing: Paddle

**T-016: Paddle adapter + webhooks**
Scope: Paddle adapter (overlay/hosted checkout, `workspace_id` in `custom_data`). `POST /webhooks/paddle` controller with Paddle-Signature verification. Handle: `subscription.created`, `subscription.updated`, `subscription.canceled`, `transaction.completed`, `transaction.payment_failed`. MoR tax amount recorded on invoice. Reuses Phase 4 subscription state machine.
Depends on: T-013
Build: [A] PaddleGateway adapter + webhook controller

---

## Phase 6 — Billing: SSLCommerz

**T-017: SSLCommerz adapter + IPN**
Scope: SSLCommerz adapter (hosted checkout, BDT only). `POST /webhooks/sslcommerz` IPN handler: hash verify → `validationDetails` API call → amount/currency/tran_id match check → dispatch webhook job. `app_driven_renewal` flag on subscription.
Depends on: T-013
Build: [A] SSLCommerzGateway adapter + IPN controller

**T-018: App-driven renewals (SSLCommerz)**
Scope: `billing:renewals` Artisan command (scheduler, runs daily), `SubscriptionRenewalReminderJob` (D-7, D-3, D-1 emails with payment link), `GracePeriodExpiryJob` (same as Stripe dunning — reuse). Renewal payment link uses SSLCommerz checkout session URL stored in settings.
Depends on: T-017
Build: [A] + scheduled jobs

---

## Phase 7 — Invoices & receipts

**T-019: Invoice generation**
Scope: `invoices` migration + Invoice model, `GenerateInvoice` action (called from webhook handlers on `invoice.paid`). Sequential number via locked settings counter (`INV-YYYY-NNNNNN`). PDF via `barryvdh/laravel-dompdf`. Signed S3/local URL for download. `payments`, `refunds` migrations + models.
Depends on: T-011, T-016, T-017
Build: [A]

**T-020: Invoice list screen**
Scope: `create-user-page "invoices"` — paginated list, status, amount, PDF download link. Admin invoices page (create-admin-page "invoices") — full list, search, regenerate PDF.
Depends on: T-019
Build: [S]

**T-021: Coupons**
Scope: `coupons`, `coupon_redemptions` migrations + models (Coupon HasAuditTrail). HasCrud admin resource (C+). `ApplyCoupon` action (validate restrictions, redemption limit, expiry, then apply via gateway's discount API). Coupon field on checkout page.
Depends on: T-010
Build: [C+] + [A]

---

## Phase 8 — Public site

**T-022: Landing page**
Scope: `routes/web.php` public routes, `resources/views/public/` layout, landing page (hero, features, social proof, pricing teaser, CTA), features page, legal pages (flag for lawyer review).
Depends on: none (can start in parallel with any phase)
Build: [B]

**T-023: Pricing page**
Scope: Dynamic pricing from `plans` + `plan_prices` + `plan_features` DB. Monthly/annual toggle (Alpine). USD/BDT currency toggle. Features comparison table. CTA → checkout.
Depends on: T-006, T-007
Build: [B]

**T-024: Contact form + support ticket**
Scope: Contact page (rate-limited + hCaptcha), `CreateSupportTicket` action. `support_tickets`, `ticket_messages` migrations + models. Admin support inbox (create-admin-page "support-inbox") — list, thread view, reply, close, assign.
Depends on: none
Build: [S] + [A]

---

## Phase 9 — Mobile API

**T-025: Device registration**
Scope: `devices` migration + Device model. `POST /api/v1/devices` (register push token), `DELETE /api/v1/devices/{id}` (unregister), `PATCH /api/v1/devices/{id}` (update token/app_version).
Depends on: T-003
Build: [A] + API controller

**T-026: API workspace + billing endpoints**
Scope: `/api/v1/workspaces` CRUD + switch, `/api/v1/workspaces/{id}/members`, `/api/v1/workspaces/{id}/subscription` (read), `/api/v1/workspaces/{id}/plans` (available), `/api/v1/workspaces/{id}/checkout` (returns gateway URL), `/api/v1/workspaces/{id}/portal` (returns portal URL). All reuse Actions from web layer.
Depends on: T-003, T-013
Build: [A] thin API controllers (call existing Actions)

**T-027: OpenAPI docs**
Scope: `dedoc/scramble` install + config (`prefix: api/v1`, Sanctum auth scheme). Verify all Phase 9 routes appear correctly. Publish docs at `/api/v1/documentation`.
Depends on: T-025, T-026
Build: [A] config only

---

## Phase 10 — P1 features

**T-028: Usage metering**
Scope: `usage_records` migration + model. `IncrementUsage(Workspace, string $feature, int $qty)` action. Entitlements service reads usage for metered feature enforcement. Monthly counter reset via scheduler. Admin usage overview per workspace.
Depends on: T-008
Build: [A]

**T-029: In-app notifications**
Scope: `notifications` table (Laravel built-in + custom). `Notifiable` trait on User. Notification list page (create-user-page "notifications"), mark read (single + all), preferences (email on/off per type, push on/off via device tokens). Dispatch notifications from domain events (SubscriptionActivated, PaymentFailed, etc.).
Depends on: T-013, T-025
Build: [S] + [A]

**T-030: Admin analytics dashboard**
Scope: `create-admin-page "overview"`. MRR (sum of active subscription amounts, formula: `SUM(plan_prices.amount_minor WHERE subscriptions.status = active) / 100`), ARR (MRR × 12), churn rate (canceled/active last 30d), trials started, failed payments, gateway split chart. Date range filter. No external analytics SDK.
Depends on: T-013, T-019
Build: [S]

**T-031: Data rights**
Scope: `create-user-page "data-privacy"`. Export: queued job zips user data (profile, workspaces, invoices, usage) as JSON + sends download link email. Delete account: showDanger confirm, `DeleteAccount` action (cancels active subscription, removes workspace if sole owner, anonymises audit log actor, deletes user).
Depends on: T-013
Build: [S] + [A]

---

## Phase 11 — Hardening

**T-032: Larastan level 5**
Scope: `composer require --dev larastan/larastan`, `phpstan.neon` at level 5. Run `vendor/bin/phpstan analyse`. Fix all errors. Add to CI.
Build: [A] config + fixes

**T-033: Security hardening**
Scope: `SecurityHeaders` middleware (CSP, HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy). Rate limiting: `throttle:10,1` on auth endpoints, `throttle:60,1` on `/api/v1`, `throttle:300,1` on webhooks, `throttle:3,1` on contact form. `cors.php` config for `/api/v1`.
Build: [A] middleware + route configuration

**T-034: N+1 and performance audit**
Scope: Enable Laravel Telescope on dev or use `DB::listen` in test. Add `preventLazyLoading()` to `AppServiceProvider` in non-production. Fix all N+1 queries found on list pages. Run query count assertions in feature tests for key list endpoints.
Build: [A] fixes

---

## Phase 12 — Docs

**T-035: README and quickstart**
Scope: `README.md` with prerequisites, clone, `.env.example` setup, `php artisan tyro:install`, `php artisan tyro-dashboard:install`, seed, serve, run tests. All `.env` keys documented in `.env.example`.
Build: docs only

**T-036: Admin screen: plans + subscriptions + payments + webhook events**
Scope: Remaining admin screens not yet built: subscriptions detail (create-admin-page "subscriptions"), payments + refunds (create-admin-page "payments"), webhook events replay (create-admin-page "webhook-events"), gateway settings (create-admin-page "gateway-settings").
Depends on: T-011, T-013, T-019
Build: [S]

---

## Blocking edge summary

```
T-003 (Workspace) blocks everything
T-006+T-007 (Plans+Features) blocks T-008
T-008 (Entitlements) blocks T-009+
T-009 (Gateway customers) blocks T-010+
T-010 (Stripe checkout) blocks T-011+
T-011 (Stripe webhooks) blocks T-013
T-013 (State machine) blocks T-014,T-015,T-016,T-017,T-018,T-029,T-031
T-019 (Invoices) blocks T-020
T-023 (Pricing page) depends on T-006,T-007
```

T-022 (Landing page) can start any time — no dependencies.
T-024 (Contact form) can start any time.
Phases 5 and 6 (Paddle, SSLCommerz) depend only on T-013 and are independent of each other.
