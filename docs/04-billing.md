# 04 — Billing

## Pricing (placeholders — tune per product)

| Plan | Monthly USD | Annual USD | Monthly BDT | Annual BDT | Trial | Example limits |
|---|---|---|---|---|---|---|
| Free | $0 | $0 | ৳0 | ৳0 | — | 1 workspace, 1 seat, 100 units |
| Starter | $12 | $120 | ৳990 | ৳9,900 | 14 days | 3 seats, 2,500 units |
| Pro | $29 | $290 | ৳2,490 | ৳24,900 | 14 days | 10 seats, 25,000 units, API keys |
| Business | $79 | $790 | ৳6,990 | ৳69,900 | 14 days | 50 seats, 250,000 units, SLA |
| Enterprise | Contact | Contact | Contact | Contact | — | Manual invoicing |

Gateway routing: USD → Stripe or Paddle. BDT → SSLCommerz. Annual = 10× monthly (2 months free). Prices live in `plan_prices` rows — no code change needed to add a currency.

## Subscription state machine

trialing → active → past_due → canceled / expired
Also: canceled_pending (cancel at period end), incomplete, paused (optional).

- Gateway-driven (Stripe, Paddle): state changes ONLY from verified, idempotent webhooks.
- App-driven (SSLCommerz): `billing:renewals` scheduler runs daily. Sends payment link emails at 7, 3 and 1 day before `current_period_end`. On the end date: → `past_due` with configurable grace (default 5 days). After grace: → downgrade to Free. Data kept, features locked.
- Upgrade: immediate + prorated where gateway supports it.
- Downgrade: at period end. Pre-check warns if current usage exceeds new plan limits.
- Every transition fires a domain event (SubscriptionActivated, PaymentFailed, etc.) driving emails, notifications, audit entries and MRR metrics.

## Gateway interface (PHP)

interface PaymentGateway {
    public function key(): string;          // 'stripe' | 'paddle' | 'sslcommerz'
    public function capabilities(): array;  // ['recurring','portal','refunds','proration','tax_mor']
    public function startCheckout(Workspace $w, PlanPrice $p, CheckoutOptions $o): CheckoutSession;
    public function portalUrl(Workspace $w, string $returnUrl): ?string;
    public function cancel(Subscription $s, bool $atPeriodEnd): void;
    public function resume(Subscription $s): void;
    public function changePlan(Subscription $s, PlanPrice $to): void;
    public function refund(Payment $p, ?int $amountMinor, string $reason): Refund;
    public function verifyWebhook(Request $r): WebhookEvent; // throws on failure
    public function handleEvent(WebhookEvent $e): void;      // idempotent
}

UI asks capabilities() to decide what to show (no portal button for SSLCommerz; "Pay now" instead of "Update card").

## Per-gateway implementation rules

### Stripe
- Checkout Sessions in subscription mode → Customer Portal → webhooks with signature verification.
- Events to handle: checkout.session.completed, customer.subscription.*, invoice.paid, invoice.payment_failed, charge.refunded. Agent confirms full list from current Stripe docs via Context7.
- Local testing: Stripe CLI (stripe listen --forward-to localhost/webhooks/stripe).

### Paddle (Billing, not Classic)
- Overlay or hosted checkout. Pass workspace_id in custom data.
- Events: subscription.created, subscription.updated, subscription.canceled, transaction.completed, transaction.payment_failed.
- Paddle is MoR — it handles VAT/tax. Record this on invoices.
- Cashier Paddle 2.x targets Paddle Billing — do NOT mix with Classic.

### SSLCommerz
1. Never trust the browser redirect URL to confirm payment.
2. On IPN: verify hash signature, then call /validator/api/validationDetails/ with val_id.
3. Check that amount, currency and your tran_id match the pending payment before granting access (prevents amount tampering).
4. Store raw IPN payload in webhook_events raw json column.
5. Idempotency on tran_id — check status before processing.
6. Exclude /webhooks/sslcommerz/* from CSRF middleware.
7. Use sandbox credentials from SSLCommerz developer portal. Never use live credentials in development.
8. Refunds via SSLCommerz refund API — VERIFY availability and settlement times on your merchant account.

## Seat-based billing (P2)

Only needed for plans that charge per active member instead of a flat price. Quantity = count of `workspace_members` with status active.
- Stripe: update subscription item quantity on member add/remove (`changePlan`-style call, prorated).
- Paddle: same via subscription update API.
- SSLCommerz: no native quantity concept — recompute amount and apply on next app-driven renewal only (no mid-cycle proration).
- Add `plans.billing_mode` enum (`flat` / `per_seat`). `per_seat` plans recalculate on `MemberAdded`/`MemberRemoved` events; flat plans ignore seat count except as an entitlement limit (existing `plan_features` row).

## Transactional emails required

Welcome · Verify email (Tyro Login) · Workspace invitation · Trial started · Trial ending in 3 days · Payment succeeded + receipt · Payment failed · Renewal reminder x3 (SSLCommerz, 7/3/1 day) · Plan changed · Subscription canceled · Subscription resumed · Refund issued · Card expiring (Stripe) · Account deletion confirmation · Data export ready
