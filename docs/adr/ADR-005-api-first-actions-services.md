# ADR-005: API-first — business logic in Actions/Services, not controllers

**Status:** Accepted  
**Date:** 2026-11-04

## Context

The product needs both a Blade web UI and a `/api/v1` mobile API. If logic lives in controllers, it must be duplicated. If it lives in models (fat model pattern), it becomes hard to test and couples unrelated concerns.

## Decision

**Business logic lives in Action classes** (single-purpose, `__invoke`-able) or Service classes (multi-step, stateful operations). Both the Blade controllers and the `/api/v1` controllers call the same Actions. Controllers are thin dispatchers — they validate input, call an Action, and return a response.

```
app/Actions/Workspaces/CreateWorkspace.php
app/Actions/Billing/StartCheckout.php
app/Actions/Subscriptions/CancelSubscription.php
```

Eloquent models contain only: relationships, scopes, casts, mutators, and factory definitions. No business logic in models.

## Rationale (industry standard)

The Action pattern (popularised by Laravel Fortify, Jetstream, and the wider Laravel community) is the dominant pattern for testable Laravel applications. It maps cleanly to vertical ticket slices (one Action = one thing the system does), making unit testing straightforward without mocking controllers. GitHub's internal Ruby services, Shopify's service objects, and Stripe's internal action modules all follow this same separation.

The `/api/v1` controller being a thin wrapper means mobile API completeness is essentially free — if a feature exists in the web UI, the API route is a few lines of dispatch.

## Consequences

- Every feature ticket produces at least one Action class.
- Actions are tested directly in unit tests, not through HTTP.
- Blade controllers in `app/Http/Controllers/Dashboard/` — thin.
- API controllers in `app/Http/Controllers/Api/V1/` — thin.
- Event dispatch happens inside Actions, not controllers.
- Authentication (Sanctum) is handled by middleware before the controller; Actions assume an authenticated user is available.

## Alternatives considered

**Fat controllers** — rejected: duplicates logic between web and API layers; difficult to test without HTTP overhead.

**Fat models** — rejected: couples billing, workspace, and notification logic to Eloquent lifecycle hooks; circular dependency risk; impossible to reuse logic outside the model context.

**Domain-driven design with repositories** — rejected as overkill for a boilerplate. Actions provide 90% of the testability benefit of DDD at 10% of the structural overhead.
