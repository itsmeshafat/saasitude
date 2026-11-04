# ADR-002: Own provider-agnostic billing layer (no Laravel Cashier)

**Status:** Accepted  
**Date:** 2026-11-04 (Phase 0 verification completed)

## Context

Three payment gateways are required: Stripe, Paddle, SSLCommerz. Laravel Cashier (`laravel/cashier-stripe`, `laravel/cashier-paddle`) covers only one gateway each and both create `subscriptions` and `subscription_items` tables and add columns to the users table via their `Billable` trait.

## Phase 0 verification

Neither Cashier package is installed. Confirmed conflict: both create `subscriptions`/`subscription_items` tables (identical names, incompatible schemas) and define a `Billable` trait that cannot be used from two packages simultaneously. Installing both is not possible.

## Decision

**Build a custom billing layer.** Own tables: `plans`, `plan_prices`, `subscriptions`, `payments`, `invoices`, `gateway_customers`, `webhook_events`. A `PaymentGateway` interface with three adapters:

```php
interface PaymentGateway {
    public function createCustomer(Workspace $workspace): GatewayCustomer;
    public function createCheckoutSession(Workspace $workspace, PlanPrice $price, array $options = []): CheckoutSession;
    public function cancelSubscription(Subscription $subscription): void;
    public function resumeSubscription(Subscription $subscription): void;
    public function handleWebhook(Request $request): WebhookResult;
}
```

Use `stripe/stripe-php` directly for Stripe. Use Paddle's HTTP API (or `paddle/paddle-php` if available) for Paddle. Thin in-house client for SSLCommerz.

## Rationale (industry standard)

Companies that need multi-gateway support (Braintree, Adyen, Stripe + local gateways) always build a gateway abstraction layer rather than mixing SDK-opinionated packages. Cashier is designed for the single-gateway case. The custom layer gives us:
- Consistent subscription state machine across all gateways
- Shared `subscriptions` table (no duplicate tables)
- Testable via the same fake/mock pattern for all gateways
- Freedom to add a fourth gateway (StoreKit, Play Billing) without an ADR rewrite

## Consequences

- More upfront code than a Cashier install, but significantly less complexity long-term.
- Gateway-specific behaviour (Stripe prorations, Paddle MoR tax, SSLCommerz app-driven renewals) is encapsulated inside each adapter.
- Contract tests: each adapter must pass a standard test suite asserting the interface contract (create checkout, receive webhook, update subscription state). See `docs/08-agents-setup.md` skill `billing-gateway-adapter`.

## Alternatives considered

**Cashier Stripe + custom adapters for Paddle/SSLCommerz** — rejected: Cashier Stripe adds Billable trait to User (not Workspace), requires overriding customer model, and couples the billing layer to Stripe-specific column names (`stripe_id`, `pm_type`, etc.). Migration to the custom layer later is harder.

**Paddle Billing SDK only + custom Stripe/SSLCommerz** — rejected: same asymmetry problem; Paddle Billing SDK also makes strong schema assumptions.
