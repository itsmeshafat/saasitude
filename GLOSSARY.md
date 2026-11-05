# Glossary

Precise definitions for terms used throughout the codebase, docs, and ADRs.
Phase 1 — finalised 2026-11-04. Update this file; never let it drift from the code.

---

**Workspace** — The billable organisational unit. Every user gets one personal workspace on registration. Teams may own additional workspaces. Plans, subscriptions, entitlements, usage records, and API tokens hang off the workspace, not the user. Corresponds to the `workspaces` table.

**Member** — A user with a role in a workspace. Represented by a row in `workspace_members (workspace_id, user_id, role)`. A user may be a member of many workspaces simultaneously. Distinct from Tyro's global staff roles (`super-admin`, `admin`, `support`, `user`) which are platform-level and stored in Tyro's own tables.

**Plan** — A named tier of product access (e.g. Free, Starter, Pro, Enterprise). A plan has zero or more prices (one per interval/currency/gateway combination) and zero or more features. Stored in the `plans` table. Plans are data, not code constants — do not hardcode plan keys in conditionals.

**PlanPrice** — One purchasable variant of a plan: a specific interval (`monthly`/`annual`), currency (ISO 4217), amount, and gateway. The `gateway_price_id` field holds the external identifier (Stripe Price ID, Paddle Price ID, etc.). Stored in `plan_prices`.

**Feature** — A capability or limit that a plan may grant. Three types: `boolean` (on/off access gate), `limit` (integer ceiling, e.g. `max_seats`), `metered` (counted usage, e.g. `api_calls_per_month`). Stored in `features`. Feature keys are stable strings (`max_seats`, `api_access`) — rename only with a migration.

**Entitlement** — The resolved access right of a workspace on its current plan. Computed at runtime by `Entitlements::for($workspace)`. Never check `plan === 'pro'` in application code — always go through the Entitlements service. Source of truth: the `plan_features` join between the workspace's active subscription and the features table.

**Subscription** — The active binding between a workspace and a plan price, managed by a gateway. Tracks status (`trialing`, `active`, `past_due`, `canceled`, `expired`), billing period, trial end, and cancellation intent. Stored in `subscriptions`. One workspace may have at most one active subscription at a time.

**Payment** — A single monetary transaction (charge or renewal payment) linked to a workspace and optionally a subscription. Stores amount in minor units, currency, gateway reference, and status. Stored in `payments`. Payments are created by webhook events, never by return URLs.

**Gateway** — One of the three payment processors integrated: `stripe`, `paddle`, `sslcommerz`. Each gateway has its own adapter implementing the `PaymentGateway` interface. Gateway identity is stored as a string column on subscriptions, payments, and plan prices.

**WebhookEvent** — An inbound notification from a payment gateway (Stripe event, Paddle webhook, SSLCommerz IPN). Stored in `webhook_events` with a `UNIQUE(gateway, event_id)` key before processing. Idempotent: processing a duplicate delivery is a no-op.

**GracePeriod** — The window after a subscription enters `past_due` during which the workspace retains full access while dunning retries proceed. Length is configurable via `settings` (`grace_period_days`). After expiry: downgrade to free plan.

**UsageRecord** — A metered consumption record for a workspace, feature, and billing period. Stored in `usage_records (workspace_id, feature_id, period YYYY-MM, quantity)`. The Entitlements service reads this to enforce metered limits.

**Coupon** — A discount code that reduces the price of a subscription. Types: `percent` or `fixed`. Duration: `once`, `repeating` (N months), or `forever`. Stored in `coupons`; each application is recorded in `coupon_redemptions`.

**Invoice** — A billing record generated for each payment. Has a sequential number (`INV-YYYY-NNNNNN`), line items (JSON), tax, total in minor units, and a PDF. Stored in `invoices`. Invoice numbers use a locked DB counter in `settings` — never `MAX(id)+1`.

**Device** — A registered mobile client (iOS/Android) or web session that may receive push notifications. Stored in `devices (user_id, platform, push_token, app_version, last_seen_at)`. Used by the mobile API layer.

**AuditEntry** — A record of a state-changing action on a model that uses `HasAuditTrail`. Stored in Tyro's `tyro_audit_logs` table. Contains actor, action, model type/ID, old/new values, and timestamp. Read-only after creation.

**Coupon** — A discount code redeemable at checkout. Either `percent` (0–100) or `fixed` (integer minor units + required ISO currency). Optionally restricted to specific plans (`plan_ids` JSON), capped by `max_redemptions`, and dated via `expires_at`. Stored in `coupons`. Once-per-workspace; deletable only while it has no redemptions (deactivate otherwise). Discounts are always computed as integer minor units.

**CouponRedemption** — The record of a workspace applying a coupon: coupon, workspace, optional gateway coupon id, amount off in minor units, and `applied_at`. Stored in `coupon_redemptions`. `coupons.redeemed_count` is incremented atomically when a redemption is recorded.

**GatewayManager** — App service (`app/Services/GatewayManager.php`) that resolves gateway adapters by key (`stripe`, `paddle`, `sslcommerz`), lists all registered gateways, exposes the container-default gateway, and routes checkout by currency (BDT → SSLCommerz, otherwise default). Singleton.
