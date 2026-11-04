# ADR-003: SSLCommerz defaults to app-driven renewals

**Status:** Accepted — recurring to be revisited when merchant account confirmed  
**Date:** 2026-11-04

## Context

SSLCommerz supports recurring payments via an encrypted `schedule` parameter, but this feature is NOT enabled by default on merchant accounts. It requires a separate merchant agreement and is not universally available. Many SSLCommerz integrations in production use app-driven renewals instead.

## Decision

**Default to app-driven renewals for SSLCommerz:**

1. Subscription is created with `gateway = sslcommerz` and an `app_driven_renewal = true` flag.
2. A scheduled job runs N days before renewal date, sends a renewal reminder email with a payment link.
3. The workspace enters a `grace_period` state if payment is not received by the renewal date.
4. After `grace_period_days` (configurable in `settings`), subscription moves to `canceled`.

The SSLCommerz gateway adapter's `cancelSubscription` and `resumeSubscription` methods are no-ops (no API call needed; state is managed locally).

If the `schedule` parameter is confirmed available on the merchant account, it can be enabled per-subscription without changing the state machine — the adapter's `createCheckoutSession` will include the encrypted schedule parameter and the IPN handler will treat the recurring IPN as a standard payment webhook.

## Rationale (industry standard)

App-driven renewals are the standard for local/regional payment gateways in markets where recurring API support is limited or unreliable (Paytm India, PayFast South Africa, bKash Bangladesh). The pattern is: scheduler → reminder email → payment link → IPN confirmation. This gives the customer explicit control and avoids failed silent charges that could harm the user relationship in price-sensitive markets.

## Consequences

- The subscription state machine must have an `app_driven_renewal` path alongside the gateway-driven path.
- `SubscriptionRenewalReminderJob` runs N days before period end, dispatched by the scheduler.
- `GracePeriodExpiryJob` downgrades after grace period.
- Both paths emit the same events (`SubscriptionRenewed`, `SubscriptionLapsed`) so downstream code is gateway-agnostic.

## Alternatives considered

**Always use SSLCommerz recurring** — rejected: requires confirmed merchant account feature; treating it as default would cause production failures for merchants without the feature enabled.

**SSLCommerz for one-time payments only, no subscriptions** — rejected: too limiting; SSLCommerz is the primary gateway for BDT-denominated subscriptions.
