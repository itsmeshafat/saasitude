# ADR-006: Entitlements are the single source of truth for feature access

**Status:** Accepted  
**Date:** 2026-11-04

## Context

Feature access in SaaS applications is commonly gated by checking `$workspace->plan->key === 'pro'` or `$user->subscription->plan_id === 3` in application code. This pattern creates brittle conditionals scattered across the codebase that break when plans are renamed, restructured, or when grandfathered customers are on legacy plans.

## Decision

**Never check plan names, plan IDs, or plan keys in application code.**

Always call the Entitlements service:

```php
Entitlements::for($workspace)->can('api_access');         // boolean feature
Entitlements::for($workspace)->limit('max_seats');        // integer or INF
Entitlements::for($workspace)->metered('api_calls');      // current period usage vs limit
Entitlements::for($workspace)->enforce('api_access');     // throws EntitlementException if denied
```

`Entitlements::for($workspace)` resolves the workspace's active subscription → plan → plan_features, caches the result per request, and returns a typed value object. Free/unsubscribed workspaces receive the entitlements of a configured free plan (never null).

## Rationale (industry standard)

This is the standard approach used by Stripe (entitlement checks via their Entitlements API), GitHub (feature flags + plan checks go through a single `Feature.enabled?` method), and Basecamp (plan object is the gatekeeper). The benefits:
- Plan restructuring requires only data changes, not code changes.
- Grandfathering is handled by the data model (`price_version` on subscriptions locks grandfathered customers to old feature sets).
- Trial entitlements follow the same path as paid entitlements (trial maps to the subscribed plan's features).
- Testing a feature under different plan configurations requires only factory data, not mocking plan checks.

## Consequences

- An `Entitlements` facade/service must be one of the first things built (Phase 3, alongside plans/features).
- Middleware (`EnsureEntitlement`) uses this service for route-level access gates.
- Blade directives (`@entitlement('api_access')`) use it for view-level gates.
- `EntitlementException` is caught by the exception handler and returns a 402 for API requests, a redirect with a flash upgrade prompt for web requests.
- Usage metering (`usage_records`) is updated by the same service on metered feature consumption.

## Alternatives considered

**Check plan key in conditionals** — rejected: creates O(features × files) coupling between plan names and code; breaks on grandfathering and plan restructuring.

**Laravel Gates and Policies** — partial use: Gates can call the Entitlements service, but Gates alone are not sufficient because they don't handle limit/metered access patterns. Use Gates as thin wrappers over Entitlements for compatibility with standard Laravel middleware (`can:` middleware).
