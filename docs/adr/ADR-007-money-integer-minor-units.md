# ADR-007: Money stored as integer minor units + ISO currency code

**Status:** Accepted  
**Date:** 2026-11-04

## Context

PHP has no native decimal type. Using `float` for money causes rounding errors (the classic `0.1 + 0.2 ≠ 0.3`). Using PHP's `bcmath` or `brick/money` adds complexity. All major payment APIs (Stripe, Paddle, PayPal) already communicate amounts as integers in the smallest currency unit (cents, pence, paise, poisha).

## Decision

**All monetary amounts are stored and computed as integers in the smallest currency unit.** No floats, no BigDecimal in money columns.

```php
// Storage (migration)
$table->unsignedBigInteger('amount_minor');  // e.g. 999 = $9.99 USD, 50000 = ৳500 BDT
$table->string('currency', 3);              // ISO 4217: 'USD', 'BDT', 'GBP'

// Display only — convert at the boundary
number_format($amount_minor / 100, 2)       // only in view layer
```

**Webhooks and IPN are the authoritative source of payment state.** Return URLs from gateways display confirmation only — they never trigger state changes in our database. `PaymentConfirmed` and `SubscriptionUpdated` events are only dispatched from webhook handlers.

## Rationale (industry standard)

Stripe, PayPal, Adyen, Braintree — every major payment processor uses integer minor units in their API. Storing the same way eliminates the conversion layer. The pattern is universal among FinTech companies. Martin Fowler's Money pattern and the `moneyphp/money` library both operate on integer minor units.

The webhook-as-source-of-truth pattern is explicitly recommended by Stripe ("Don't rely on the redirect — your webhook must be the trigger") and prevents duplicate provisioning from redirect replay attacks.

## Consequences

- No `DECIMAL` or `FLOAT` columns for any amount field — only `UNSIGNED BIGINT`.
- Conversion to display currency happens only in Blade views and API response transformers, never in business logic or storage.
- BDT amounts: SSLCommerz returns amounts in full taka (not poisha). Multiply by 100 when receiving, divide by 100 when displaying. Document this conversion explicitly in the SSLCommerz adapter.
- Multi-currency: `currency` column stored alongside every `amount_minor`. Never compare amounts of different currencies.

## Alternatives considered

**`DECIMAL(10,2)`** — rejected: works for simple cases but causes subtle rounding issues in multi-currency arithmetic and doesn't match the wire format of payment APIs.

**`brick/money` library** — not needed: the minor-units integer approach is simpler and brick/money's value is mainly for arithmetic across currencies, which we avoid by converting only at display boundaries.
