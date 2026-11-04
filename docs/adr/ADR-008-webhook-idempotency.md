# ADR-008: Webhook/IPN idempotency via webhook_events table

**Status:** Accepted  
**Date:** 2026-11-04

## Context

Payment gateways deliver webhooks with at-least-once semantics — duplicates are guaranteed. Stripe explicitly documents this. Processing a webhook twice (double-provisioning, double-emailing, double-invoicing) is a critical billing bug.

## Decision

Every incoming webhook/IPN is stored in `webhook_events` with a **database-level** `UNIQUE(gateway, event_id)` constraint before any processing occurs.

Flow:
1. Gateway sends POST to `/webhooks/{gateway}`.
2. Controller verifies HMAC signature (before anything else — reject unauthenticated events).
3. Controller attempts `WebhookEvent::firstOrCreate(['gateway' => ..., 'event_id' => ...], [...])`.
4. If the row already existed (duplicate): return `200 OK` immediately, no job dispatched.
5. If new: dispatch `ProcessWebhookEvent` queued job. Return `200 OK`.
6. Job processes the event, updates subscription/payment state, dispatches domain events.
7. Job marks `webhook_events.status = 'processed'` and `processed_at`.
8. On job failure: `status = 'failed'`, `attempts++`. Retry with exponential backoff. Admin can replay via `webhooks:replay` command.

The `webhook_events` table stores the raw payload (JSON). Never lose a payload.

## Rationale (industry standard)

The `store then process` pattern is used by every serious payment integration. Stripe's official webhook guide recommends it. The `UNIQUE` constraint is the only guarantee that works under concurrent load — application-level checking has a race condition when two deliveries arrive within milliseconds. The job queue provides retryability; storing the raw payload enables replaying without hitting the gateway API again.

Signature verification before storage prevents the table being filled with unauthenticated garbage during DDoS attacks on the webhook endpoint.

## Consequences

- Each gateway's controller must implement HMAC signature verification using the gateway's specific method:
  - Stripe: `\Stripe\Webhook::constructEvent($payload, $sig, $secret)`
  - Paddle: `Paddle-Signature` header, HMAC-SHA256
  - SSLCommerz: `val_id` server-side re-validation (not HMAC — SSLCommerz uses a separate confirmation API call)
- `webhook_events` table has indexes on `(gateway, event_id)` and `(status, created_at)` for the replay query.
- Admin dashboard shows failed events with a replay button (Phase 8 screen).
- Webhook endpoint rate limit: 300 req/min per gateway IP range.

## Alternatives considered

**Application-level duplicate check only** — rejected: race condition under concurrent delivery; not safe.

**Process synchronously in the controller** — rejected: gateway expects `200` within ~5 seconds; queued processing is mandatory for any non-trivial work.

**Discard duplicates at the queue level** — rejected: queue deduplication is unreliable; the DB unique constraint is the only safe guarantee.
