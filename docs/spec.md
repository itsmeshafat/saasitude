# Laravel SaaS Boilerplate — Spec

Phase 1 finalised 2026-11-04. Source of truth for Phases 2–13 (MVP) and Phase 14 (P2 extras).

---

## What we are building

A multi-tenant SaaS boilerplate for Laravel 13 / PHP 8.5 / PostgreSQL. It ships as a runnable Laravel application with all billing, workspace management, auth, and admin features production-ready. Developers clone it and add their product's domain on top.

**Three billing gateways from day one:** Stripe (USD/international), Paddle (USD/international, MoR), SSLCommerz (BDT, Bangladesh market).

**Stack:** Blade + Tailwind + Alpine · Tyro Dashboard (admin/user shell) · Tyro Login (auth) · Sanctum (API tokens) · PostgreSQL · Redis (queues, cache, sessions) · PHPUnit · Scramble (OpenAPI).

---

## Tyro-native (configure/theme only — do NOT rebuild)

| Feature | Tyro package | Status |
|---|---|---|
| Login, register, forgot password, verify email | tyro-login v2.17.3 | Installed |
| TOTP 2FA (optional on register) | tyro-login | Installed |
| Social OAuth (Google, GitHub etc.) | tyro-login | Installed |
| OTP, magic links, passkeys | tyro-login | Installed |
| Captcha on register | tyro-login | Installed |
| Invitation system (email invites) | tyro-login | Installed |
| User management (admin) | tyro-dashboard | Installed |
| Roles & privileges (global RBAC) | tyro + tyro-dashboard | Installed |
| User suspension | tyro | Installed |
| Impersonation | tyro-dashboard | Installed |
| Audit trail (HasAuditTrail) | tyro | Installed |
| Media management | tyro-dashboard | Installed |
| Profile page (password, 2FA, avatar) | tyro-dashboard | Installed |
| SMTP settings (admin) | tyro-dashboard | Installed |
| System health / logs | tyro-dashboard | Installed — VERIFY covers what `docs/01-prd.md` calls "system ops admin"; if not sufficient, supplement in Phase 14 |
| DB checkpoints | tyro-checkpoint | Installed (dev-only) |

---

## What we build (custom)

### P0 — MVP core (Phases 2–13)

**Phase 2 — Foundation**
- App settings store (encryption, groups, HasCrud admin screen)
- Tyro staff roles + privileges seed (`super-admin`, `admin`, `support`, `user`; privilege set from `docs/05-screens.md`)
- Workspace CRUD: create, rename, slug, logo, switch, settings, transfer ownership, delete
- Workspace members: invite (extends Tyro invitations), accept, change role, remove, pending list
- Personal workspace auto-created on registration

**Phase 3 — Plans & entitlements**
- Plans CRUD (admin HasCrud, publish/unpublish)
- Plan prices (per gateway, interval, currency — data only, no gateway calls yet)
- Features CRUD (key, type, unit)
- Plan features (assign feature limits to plans)
- Entitlements service (`Entitlements::for($workspace)`) + `EnsureEntitlement` middleware + Blade directive
- Free plan seed (all workspaces start here until they subscribe)

**Phase 4 — Billing: Stripe**
- `gateway_customers` table + GatewayCustomer resolver
- Stripe checkout (Checkout Session, subscription mode, 14-day trial)
- Stripe Customer Portal (payment method update, invoice history)
- Stripe webhooks: `checkout.session.completed`, `customer.subscription.*`, `invoice.paid`, `invoice.payment_failed`, `charge.refunded` — idempotent via ADR-008
- Subscription state machine: `trialing` → `active` → `past_due` → `canceled`
- Dunning: `PastDueReminderJob` (3 retries over 7 days), `GracePeriodExpiryJob` (downgrade to Free)
- Proration on plan change

**Phase 5 — Billing: Paddle**
- Paddle checkout (overlay or hosted, `workspace_id` in `custom_data`)
- Paddle webhooks: `subscription.created`, `subscription.updated`, `subscription.canceled`, `transaction.completed`, `transaction.payment_failed`
- MoR tax: record on invoices
- No Customer Portal needed (Paddle handles it)

**Phase 6 — Billing: SSLCommerz**
- SSLCommerz checkout (hosted, BDT only)
- IPN handler: hash verify → `validationDetails` API call → amount/currency/tran_id check → update subscription
- App-driven renewals: `billing:renewals` daily scheduler, reminder emails at D-7, D-3, D-1
- Grace period + downgrade flow (same state machine as Stripe/Paddle)

**Phase 7 — Invoices & receipts**
- Invoice generation on payment confirmation (PDF via `barryvdh/laravel-dompdf` or `spatie/laravel-pdf`)
- Sequential invoice number (`INV-YYYY-NNNNNN` via locked settings counter)
- Signed URL for PDF download (no public S3 URLs)
- Coupon redemption records

**Phase 8 — Public site**
- Landing, pricing (dynamic from DB), features, legal, contact form
- Contact form → creates support ticket → notifies admin
- Rate limiting on auth and contact endpoints
- Pricing page: monthly/annual toggle, currency toggle, features comparison table from `plan_features`

**Phase 9 — Mobile API (`/api/v1`)**
- Device registration + push token management
- Workspace management endpoints (list, create, switch, members)
- Billing endpoints (current plan, available plans, checkout redirect, portal redirect)
- Notifications endpoints
- Scramble OpenAPI docs at `/api/v1/documentation`
- Sanctum token abilities: `workspace:read`, `workspace:write`, `billing:read`, `billing:write`, `api:*`

**Phase 10 — P1 features**
- Coupons: percent/fixed, duration, redemption limits, plan restrictions, apply at checkout
- Usage metering: `IncrementUsage` action, limit enforcement via Entitlements, monthly counter reset
- In-app notifications: list, mark read, email/push preferences, dispatch from domain events
- Admin analytics: MRR, ARR, churn (formula documented in controller), trials, payment failures, gateway split
- Support inbox: admin reply, close, assign
- Data rights: export my data (queued ZIP), delete account (showDanger, cascades, anonymises audit logs)

**Phase 11 — Hardening**
- Larastan level 5 — zero errors
- Security headers middleware (CSP, HSTS, X-Frame-Options)
- Rate limiting: auth endpoints, `/api/v1`, webhooks, contact form
- N+1 query audit (Telescope or query log on CI)
- WCAG 2.2 AA audit (design-critique at 375/768/1024/1440px + dark mode)

**Phase 12 — Docs & launch prep**
- README with quickstart (clone → `.env` → `php artisan tyro:install` → `php artisan tyro-dashboard:install` → seed → serve)
- `CHANGELOG.md` initial entry
- `.env.example` with all required keys documented
- Legal pages flagged for lawyer review

**Phase 13 — Go-live checklist** (`docs/10-quality-gates.md`)

---

### P2 — Platform extras (Phase 14, post-launch)

Feature flags (Pennant), outbound webhooks, changelog, email templates, status page, feedback widget, onboarding wizard, advanced search, custom fields/tags/templates, workspace collaboration extras, security extras, system ops admin screens. See `docs/01-prd.md` and `docs/00-research.md` reference-SaaS section.

---

## Key architecture rules (see `docs/02-architecture.md` for full ADRs)

| Rule | ADR |
|---|---|
| Workspace is the billable entity | ADR-001 |
| Custom billing layer, no Cashier | ADR-002 |
| SSLCommerz defaults to app-driven renewals | ADR-003 |
| Blade + Alpine, no React/Livewire/Inertia | ADR-004 |
| Actions/Services for business logic | ADR-005 |
| Always use `Entitlements::for($workspace)` | ADR-006 |
| Money = integer minor units + ISO currency | ADR-007 |
| All webhooks idempotent via `webhook_events` | ADR-008 |
| Outbound webhooks on a separate queue | ADR-010 |
| Three queues: `default`, `billing`, `outbound-webhooks` | ADR-010 |

---

## Done means (from AGENTS.md)

Tests pass · Feature works in browser (desktop + 390px) · Audit/logging in place · Docs updated · No silent TODOs.
