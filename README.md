# SaaS Boilerplate — Laravel

**Launch your SaaS with billing that just works.**

A production-ready Laravel 12 SaaS starter with multi-gateway billing (Stripe · Paddle · SSLCommerz), workspace-based teams, a data-driven entitlements engine, a full admin console, and a mobile-ready API — on top of the beautiful [Tyro Dashboard](https://tyrolabs.dev) by [Hasin Hayder](https://github.com/hasinhayder).

![Landing Page](docs/screenshots/landing.png)

---

## Why this boilerplate

Stop rebuilding billing for every product. This repo gives you the unglamorous 80% of every SaaS — auth, teams, plans, subscriptions, invoices, webhooks, refunds, notifications, admin tooling — so you can spend your time on the 20% that makes your product unique.

- ✅ **Multi-gateway billing** — Stripe, Paddle (Billing, MoR tax) and SSLCommerz behind one `PaymentGateway` interface. Swap or add gateways without touching business logic.
- ✅ **Verified-webhook-only payment state** — signature/hash verification → unique event store → queued, idempotent processing. Return URLs never grant access.
- ✅ **Workspaces as the billable entity** — every user gets a personal workspace; invite teammates with owner / admin / member / billing roles.
- ✅ **Entitlements engine** — plans and limits are *data*, not code. `Entitlements::for($workspace)->can('feature_key')` everywhere; never check plan-name strings.
- ✅ **Integer money** — minor units + ISO currency (`1200, 'USD'`). No floats, ever.
- ✅ **Full admin console** — MRR/ARR/churn overview, workspace management, subscription timelines, refunds, webhook replay, gateway settings, email previews, support inbox, system health.
- ✅ **Mobile API** — versioned `/api/v1` with Sanctum token abilities, workspace context header, cursor pagination, idempotency keys, one error envelope.
- ✅ **Beautiful out of the box** — Tyro Dashboard (shadcn CSS variables, dark mode, WCAG-conscious), plus a public landing/pricing/changelog site driven by the plans tables.

## Screenshots

| | |
|---|---|
| ![Admin Overview](docs/screenshots/admin-overview.png) | ![Billing Overview](docs/screenshots/billing-overview.png) |
| *Admin console — MRR, trials, churn, failed payments* | *User billing — plan, usage meters, renewal* |
| ![Subscriptions](docs/screenshots/subscriptions-admin.png) | ![Team](docs/screenshots/team.png) |
| *Subscriptions — filter by status/gateway, export CSV* | *Team management — invite, roles, transfer ownership* |
| ![Pricing](docs/screenshots/pricing.png) | |
| *Public pricing — DB-driven, currency & interval toggle* | |

## What's inside

### Billing core
- **Adapters**: `StripeGateway`, `PaddleGateway`, `SSLCommerzGateway` behind a shared interface (`app/Gateways/`)
- **Subscription state machine** with domain events (activated, payment failed, trial ending, canceled…)
- **Webhook pipeline**: verify → store (`webhook_events`, unique per gateway + event id) → queue job → idempotent replay from the admin UI
- **Invoices** with sequential numbers (`INV-YYYY-NNNNNN`) and PDF download
- **Payments & refunds** (audited, privilege-gated), **coupons** with redemption tracking
- **App-driven renewals** for SSLCommerz: 7/3/1-day reminder emails, grace period, auto-downgrade (see `app/Jobs/`)

### Workspaces & teams
- Personal workspace auto-created on registration
- Owner / admin / member / billing roles in `workspace_members`
- Token-based email invitations, ownership transfer, workspace switcher

### Plans & entitlements
- `plans` / `plan_prices` / `features` / `plan_features` — all editable via HasCrud admin resources
- `Entitlements::for($workspace)->can($key)` / `->limit($key)` + `entitled` middleware and Blade directive
- Usage records with period counters; downgrade pre-check warns when usage exceeds the new plan

### Admin console (`/dashboard/*-admin`)
Overview metrics · Workspaces (suspend / restore / force plan / extend trial) · Subscriptions with state timelines · Payments & refunds · Invoices (regenerate PDF) · Webhook events (payload viewer + replay) · Gateway settings (masked keys, connection test) · Email template previews & test sends · Support inbox · System health · CSV/PDF exports

### User screens (`/dashboard`)
Billing overview · Change plan with proration preview · Checkout (gateway selector + coupon) · Invoices & receipts · Cancel flow with reason survey · Payment methods · API keys · Notifications with preferences · Support tickets · Data export & account deletion

### Mobile API (`/api/v1`)
Sanctum token abilities, `X-Workspace-Id` context, cursor pagination, `Idempotency-Key` on payment POSTs, stable snake_case error codes. Covers workspaces, members, subscription lifecycle, checkout, invoices, payments, usage, devices, notifications and data rights.

### Public site
Landing page · DB-driven pricing (monthly/annual toggle) · Changelog · Contact → support inbox · Legal pages · sitemap.xml · OpenGraph tags

## Requirements

- PHP 8.2+ (built on 8.5)
- MySQL 8+, Redis
- Node.js 20+ & npm
- Composer

## Setup

The fastest path is the **web installer** — clone, install dependencies, serve, and open the app in a browser:

```bash
git clone <your-repo-url> saas-boilerplate
cd saas-boilerplate
composer install
npm install && npm run build
php artisan serve
```

Open `http://localhost:8000` and the 5-step installer takes over:

1. **Requirements** — PHP version, extensions, writable paths checked for you
2. **Database** — enter MySQL (or SQLite) credentials; verified, written to `.env`, migrated
3. **Admin account** — your super-admin, with a personal workspace like any registration
4. **Gateways** *(optional)* — Stripe/Paddle/SSLCommerz keys, stored encrypted; skippable
5. **Demo data** *(optional)* — one click and every screen has something to show (`demo@demo.com` / `password`; remove later with `php artisan saas:reset-demo`)

Once finished, the installer locks itself (`storage/app/installed.lock`) and never appears again. Prefer the terminal? The manual route still works:

```bash
cp .env.example .env
php artisan key:generate
# edit DB_* in .env
php artisan migrate --seed
php artisan tyro-dashboard:createsuperuser
```

### Gateway configuration

Add keys per gateway in `.env` (or via **Admin → Gateway Settings**, stored encrypted in the `settings` table):

```env
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
PADDLE_API_KEY=...
PADDLE_WEBHOOK_SECRET=...
SSLCOMMERZ_STORE_ID=...
SSLCOMMERZ_STORE_PASSWORD=...
```

Webhook endpoints (add these in each gateway dashboard; `/webhooks/*` routes are CSRF-exempt):

```
POST /webhooks/stripe
POST /webhooks/paddle
POST /webhooks/sslcommerz
```

### Scheduler & queues

Production `cron` entry:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

Run a worker (webhooks, emails, renewals, data exports are queued):

```bash
php artisan queue:work --tries=3
```

## Testing

```bash
php artisan test          # full suite (166+ tests, gateway fakes — no real API calls)
vendor/bin/pint           # style
vendor/bin/phpstan analyse --memory-limit=1G   # Larastan level 5
```

## Documentation

| Doc | Contents |
|---|---|
| [`docs/00-research.md`](docs/00-research.md) · [`docs/00-audit.md`](docs/00-audit.md) | Research & gap analysis vs Tyro |
| [`docs/01-prd.md`](docs/01-prd.md) · [`docs/02-architecture.md`](docs/02-architecture.md) | Product spec & architecture |
| [`docs/04-billing.md`](docs/04-billing.md) | Gateway rules, emails, plan placeholders |
| [`docs/adr/`](docs/adr/) | 12 Architecture Decision Records |
| [`GLOSSARY.md`](GLOSSARY.md) | Domain terms |
| [`AGENTS.md`](AGENTS.md) | Full contribution rules & decision tree |

## Tech stack

[Laravel 12](https://laravel.com) · [Tyro Dashboard](https://tyrolabs.dev) & [Tyro Login](https://hasinhayder.github.io/tyro-login) by [Hasin Hayder](https://github.com/hasinhayder) · Blade + Tailwind + Alpine (shadcn CSS variables) · MySQL · Redis · Sanctum · PHPUnit · Larastan L5 · Pint

## Credits & gratitude

Built on the shoulders of giants — thank you:

- **[Hasin Hayder](https://github.com/hasinhayder)** for [Tyro](https://tyrolabs.dev), [Tyro Login](https://hasinhayder.github.io/tyro-login) and [Tyro Dashboard](https://github.com/hasinhayder/tyro-dashboard) — the auth, RBAC and dashboard foundations this project builds on.
- The **[Laravel](https://laravel.com)**, **[Tailwind CSS](https://tailwindcss.com)** and **[Stripe](https://stripe.com)** teams and their ecosystems.

## License

MIT — see [LICENSE](LICENSE).
