# ADR-012: Static Analysis with Larastan at Level 5

Date: 2026-11-07 (Phase 12 hardening)
Status: Accepted

## Context

AGENTS.md quality gates require static analysis on every ticket ("php artisan
larastan — level set in ADR"), and Open Decision #6 asked for the level to be
chosen and recorded. The codebase had no static analysis. Larastan 3.14 was
installed as a dev dependency; `phpstan.neon` analyses `app/`, `database/`
(excluding migrations) and `tests/`.

## Decision

**Level 5.** The error curve is nearly flat across levels (131 @ L3 → 160 @ L6)
because most findings were missing model property/relation types, which fire at
every level. Fixing them was the real work, so paying for L6's extra array-shape
strictness now would only slow future tickets; L5 already catches the valuable
classes here (property/method access, return types, dead code).

### Codebase-wide changes made to reach zero errors

- **Model annotations**: every model carries `@property` docblocks generated
  from the real DB schema (nullable FKs verified against migrations — e.g.
  `support_tickets.user_id` is nullable for guest tickets, `invoices.payment_id`
  / `subscription_id` are nullable) and `@property-read` relation annotations.
- **Relation generics**: every relation method is annotated with PHPStan
  generics, e.g. `@return \Illuminate\Database\Eloquent\Relations\HasMany<Subscription, $this>`.
- **API Resources** carry `@property` docblocks for the wrapped model columns.
- **Real bugs fixed** (found by the analysis):
  - `SSLCommerzGateway` read `$price->amount` (nonexistent — column is
    `amount_minor`): every SSLCommerz checkout session was created with
    `total_amount = "0.00"`.
  - `Plan` deleting-hook used `whereHas('subscriptions')` but the relation did
    not exist — deleting any plan crashed with an "undefined relationship"
    error instead of protecting active subscribers. Relation added.
- Dead-code removal flagged by the analysis: unused mailable constructor
  params, a tautological test assertion, the stock `ExampleTest`.

### Known caveat

Larastan 3.14's **automatic relation-property inference is unreliable on
Laravel 13** (`$workspace->owner` degraded to bare `Model`), which is why
annotations are explicit rather than relying on inference. Revisit when a
Larastan release officially supports Laravel 13 — the `@property`/`@return`
annotations remain correct documentation either way.

## Consequences

- `vendor/bin/phpstan analyse` (level 5) must pass on every ticket, added to
  the quality gates alongside Pint.
- New models/resources must keep their `@property` docblocks current when
  migrations change — column changes without annotation updates will surface
  as false negatives (annotations now slightly too narrow) or analysis errors
  (annotations too wide).
