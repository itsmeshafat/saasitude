# 02 — Architecture Decisions (ADRs)

Agent: grill each ADR in Phase 1 before finalising. Record any change as a new ADR file in `docs/adr/`.

## ADR-1: Billable entity = Workspace (not User)

Every user gets an auto-created personal workspace on registration. Billing, entitlements, usage and API tokens hang off the workspace. Solo users and teams work without a rewrite. Tyro roles stay **global staff roles** (`super-admin`, `admin`, `support`, `user`). Workspace roles (`owner`, `admin`, `member`, `billing`) live in our own `workspace_members` table — do not bend Tyro's global RBAC into per-tenant RBAC.

## ADR-2: Own provider-agnostic billing layer

Both Cashier packages likely collide on `subscriptions` table and `Billable` trait (**VERIFY in Phase 0**). Plan: own tables (`plans`, `plan_prices`, `subscriptions`, `payments`, `invoices`, `gateway_customers`, `webhook_events`) + a `PaymentGateway` interface with three adapters. Use `stripe/stripe-php` and Paddle's PHP SDK/HTTP API directly. Alternative: Cashier Stripe only + custom adapters for Paddle and SSLCommerz. Agent must compare and record the final decision.

## ADR-3: SSLCommerz = app-driven renewals until recurring confirmed

Default to app-driven renewals (scheduler reminder emails + payment link + grace period). Recurring via SSLCommerz's encrypted `schedule` parameter only after confirming it is enabled on the merchant account. Subscription state machine must support both gateway-driven and app-driven renewal paths.

## ADR-4: UI stack = Blade + Tailwind + Alpine via Tyro Dashboard

Build billing, workspace and admin pages in Blade using Tyro Dashboard layouts (`tyro-dashboard::layouts.user|admin|app`). Scaffold with `create-*-page` commands. Use `HasCrud` for plain resources (plans, features, coupons, announcements). Do not introduce Inertia, React or Livewire without a new ADR. Tyro Dashboard themes through shadcn CSS variables — all custom colours land in `shadcn-theme.blade.php` (publish with `tyro-dashboard:publish-style --theme-only`), not as hard-coded hex values.

## ADR-5: API-first for mobile

Business logic lives in Actions/Services. Both Blade controllers and `/api/v1` controllers call the same Actions. No logic in controllers or Blade views. Auth for mobile via Sanctum tokens with abilities.

## ADR-6: Entitlements are the single source of truth

Never check `plan === 'pro'` in code. Always check `Entitlements::for($workspace)->can('feature_key')` or `->limit('seats')`. Plans and features are data, not conditionals.

## ADR-7: Money = integer minor units + ISO currency code

No floats. No BigDecimal. Store `amount_minor` (int) + `currency` (ISO 4217, e.g. `USD`, `BDT`). Webhooks and IPN are the source of truth for payment state. Return URLs only display status — they never grant access.

## ADR-8: Webhook/IPN idempotency

Every incoming event is stored in `webhook_events` with a unique `(gateway, event_id)` composite key before processing. Processing is dispatched to a queued job and safe to replay. Duplicate delivery is handled by checking status before processing.

## ADR-9: Feature flags via Laravel Pennant (P2, deferred)

Use `laravel/pennant` with its database driver for both global flags and per-workspace rollout (`feature_flag_workspace`). Do not build a custom flag system. VERIFY at build time: Pennant's scope resolution works cleanly with Workspace as the scope object (not User) — this is the opposite of Pennant's usual per-user default, record any workaround as an ADR addendum.

## ADR-10: Outbound webhooks are a separate system from gateway webhooks

`webhook_subscriptions` / `webhook_deliveries` (customer-facing, workspace-configured, our domain events out) are architecturally distinct from `webhook_events` (gateway-facing, payment provider events in, 04-billing.md). Do not merge the two tables or the two queues — a slow or failing customer endpoint must never block billing webhook processing. Delivery: queued job per event per subscription, exponential backoff, cap retries, sign payload with the subscription's `secret` (HMAC in a header, same pattern as our own gateway signature verification).
