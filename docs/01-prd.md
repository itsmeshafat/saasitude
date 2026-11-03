# 01 — Product Requirements

## Modules and priorities

| Module | Scope summary | Priority |
|---|---|---|
| Auth & identity | Tyro Login configured: email verify on, TOTP optional, social login optional, captcha on register | P0 |
| Workspaces | Personal workspace auto-created on register, create/switch, members, invitations, roles, ownership transfer | P0 |
| Plans & entitlements | Plans, monthly/annual prices per currency, features (boolean/limit/metered), trial, grandfathering | P0 |
| Billing: Stripe | Checkout, portal, webhooks, proration, cancel/resume, refunds | P0 |
| Billing: Paddle | Checkout (overlay or hosted), webhooks, cancel/resume, MoR tax | P0 |
| Billing: SSLCommerz | BDT checkout, IPN, server-side validation, manual renewal flow, refunds | P0 |
| Invoices & receipts | PDF + list, sequential number, gateway receipt link | P0 |
| Dunning | Retry reminders, `past_due` grace, downgrade to free | P0 |
| Public site | Landing, pricing, legal, contact, changelog | P0 |
| Mobile API | `/api/v1`, Sanctum tokens, devices, OpenAPI docs | P0 |
| Coupons & trials | Percent/fixed, duration, plan restriction, redemption limits | P1 |
| Usage metering | Counters per workspace per feature per period, limit enforcement | P1 |
| In-app notifications | List, mark read, email + push preferences | P1 |
| Admin analytics | MRR, ARR, churn, trials, failed payments, signups by gateway | P1 |
| Support inbox | Contact form → ticket inbox, reply, status | P1 |
| Data rights | Export my data, delete account, retention policy | P1 |
| Referrals | Tyro invitation system + reward credit later | P2 |
| Customer API keys | Workspace-scoped keys with abilities and rate limits, published API docs, usage dashboard | P2 |
| Feature flags | DB-driven, workspace-scoped rollout (Laravel Pennant) | P2 |
| Platform content | Public changelog, admin-editable email templates, public status page | P2 |
| Outbound webhooks | Workspace-configured webhook subscriptions + delivery logs for our own events (separate from inbound gateway webhooks in 04-billing.md) | P2 |
| Growth & activation | Feedback widget, onboarding checklist/wizard, product tour, advanced global search | P2 |
| Workspace collaboration | Custom fields, tags, reusable templates, comments & announcements on activities, activity reactions | P2 |
| Workspace admin extras | Custom branding, trash/restore, suspension, email-domain restriction, CSV import, activity digest/analytics | P2 |
| Security extras | Per-workspace 2FA enforcement policy, login activity log, password change history, session activity summary | P2 |
| System ops (admin) | System health monitor, scheduled tasks monitor, data retention policies, SEO management, maintenance mode | P2 |

See docs/00-research.md "Reference SaaS: itsmeshafat/Laravel-SAAS-Starter" for the full feature-to-module mapping and what our Tyro stack already covers for free.

## Non-functional requirements

- Laravel 12/13, PHP 8.2+, MySQL, Redis (queues, cache, sessions), Nginx — matches existing stack.
- Every state-changing billing path covered by feature tests with gateway fakes.
- Rate limiting: auth endpoints, `/api/v1`, webhooks, contact form.
- All gateway keys and secrets in `.env` or encrypted DB settings — never logged.
- WCAG 2.2 AA target for all public and dashboard pages.
- i18n-ready strings from day one (English first, Bangla-ready).
- No N+1 queries on list pages (verify with Boost query tools or debug bar).
- Money stored as integer minor units + ISO currency code. No floats anywhere.
