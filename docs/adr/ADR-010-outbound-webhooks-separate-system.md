# ADR-010: Outbound webhooks are architecturally separate from gateway webhooks

**Status:** Deferred — implement in Phase 14  
**Date:** 2026-11-04

## Context

The system has two distinct webhook directions:
1. **Inbound** (`webhook_events`): Payment gateways POST to us (Stripe, Paddle, SSLCommerz IPN). Covered by ADR-008.
2. **Outbound** (`webhook_subscriptions`, `webhook_deliveries`): Customers subscribe to our domain events and we POST to their URLs.

## Decision

Keep these two systems entirely separate — different tables, different queues, different job classes. A failing or slow customer endpoint must never block billing webhook processing.

**Outbound webhook system:**

```
webhook_subscriptions: workspace_id, url, secret (encrypted), events (json), is_active
webhook_deliveries:    subscription_id, event, payload (json), response_status, attempts, delivered_at
```

Delivery flow:
1. Domain event fires (e.g. `SubscriptionActivated`, `WorkspaceMemberAdded`).
2. `DispatchOutboundWebhooks` listener queries active subscriptions for this event name.
3. One `DeliverWebhook` job dispatched per subscription. Queue: `outbound-webhooks` (separate from `default` and `billing`).
4. Job POSTs JSON payload to the customer URL with `X-Webhook-Signature: sha256=HMAC(secret, payload)`.
5. On non-2xx response or timeout: exponential backoff, max 5 retries over 24 hours, then mark `failed`.
6. Delivery log visible to workspace admin (Phase 14 screen).

## Rationale (industry standard)

Stripe, GitHub, Shopify, and every serious platform-layer SaaS run outbound webhooks on a completely isolated queue and worker pool. The reason: customer endpoints can be slow, flaky, or completely down. If outbound webhook jobs share a queue with billing processing, a flood of delivery attempts to a slow endpoint can delay subscription renewals — a P1 billing bug. Queue isolation is the only safe design.

Payload signing (HMAC-SHA256) is the standard pattern — same as how Stripe signs its outbound events to us. Customers verify the `X-Webhook-Signature` header to authenticate the payload.

## Consequences

- Three queues in production: `default`, `billing` (gateway webhooks), `outbound-webhooks`.
- `horizon.php` (or Supervisor config) must configure workers for each queue with independent scaling.
- Secret stored encrypted at rest (`webhook_subscriptions.secret` uses `encrypted` cast).
- Workspace admin sees delivery log with retry button (Phase 14).
- Payload schema versioning: include a `api_version` field in every payload (e.g. `"api_version": "2026-11-01"`).

## Alternatives considered

**Share the billing queue** — rejected: a slow customer endpoint can delay subscription events; unacceptable in production.

**Synchronous delivery in the event listener** — rejected: HTTP calls in event listeners cause request timeout risk and block other event processing.
