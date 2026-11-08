# SaaSitude — Documentation Index

Everything you need, organized by what you're trying to do.

## Getting started
- [README](../README.md) — overview, quick start, screenshots
- [ADR-013: Web installer](adr/ADR-013-web-installer.md) — how the 5-step wizard works, the lock file, demo data (`saas:reset-demo`)
- [`config/saas-plans.php`](../config/saas-plans.php) — plans, features and placeholder prices (replace before launch)

## Architecture & decisions
- [01-prd.md](01-prd.md) — product requirements
- [02-architecture.md](02-architecture.md) — system architecture
- [00-research.md](00-research.md) · [00-audit.md](00-audit.md) — Tyro gap analysis
- [GLOSSARY.md](../GLOSSARY.md) — domain terms (Workspace, Entitlement, Installed Lock, …)

### ADRs (decisions of record)
| # | Decision |
|---|---|
| [001](adr/ADR-001-billable-entity-workspace.md) | Billable entity = Workspace, not User |
| [002](adr/ADR-002-custom-billing-layer.md) | Custom billing layer instead of Cashier |
| [003](adr/ADR-003-sslcommerz-app-driven-renewals.md) | SSLCommerz app-driven renewals |
| [004](adr/ADR-004-ui-stack-blade-tyro-dashboard.md) | UI stack: Blade + Tyro Dashboard |
| [005](adr/ADR-005-api-first-actions-services.md) | API-first Actions/Services layering |
| [006](adr/ADR-006-entitlements-single-source-of-truth.md) | Entitlements as single source of truth |
| [007](adr/ADR-007-money-integer-minor-units.md) | Money = integer minor units |
| [008](adr/ADR-008-webhook-idempotency.md) | Webhook idempotency pipeline |
| [009](adr/ADR-009-feature-flags-pennant.md) | Feature flags (Pennant) |
| [010](adr/ADR-010-outbound-webhooks-separate-system.md) | Outbound webhooks are a separate system |
| [011](adr/ADR-011-open-decisions-resolved.md) | Open decisions resolved |
| [012](adr/ADR-012-static-analysis-larastan.md) | Larastan level 5 |
| [013](adr/ADR-013-web-installer.md) | First-run web installer wizard |

## Billing
- [04-billing.md](04-billing.md) — gateway rules, 18 transactional emails, plan placeholders
- Key code paths: `app/Gateways/*` (adapters) · `app/Services/GatewayManager.php` · `app/Jobs/Process*WebhookJob.php` · `app/Actions/CancelSubscription.php` · `app/Actions/ApplyCoupon.php`
- Webhook endpoints: `/webhooks/{stripe,paddle,sslcommerz}` (CSRF-exempt, signature-gated)

## Workspaces, teams & entitlements
- `app/Actions/CreateWorkspace.php` · `AcceptInvitation.php` · `InviteMember.php` · `TransferOwnership.php`
- `app/Services/EntitlementService.php` + `entitled` middleware/Blade directive
- Workspace roles live in `workspace_members` (Tyro roles are global staff only — ADR-001)

## API (`/api/v1`)
- Auth: Sanctum tokens with abilities (reuses Tyro Login's auth surface — never forked)
- Workspace context: `X-Workspace-Id` header · Idempotency-Key on payment POSTs · cursor pagination
- Controllers: `app/Http/Controllers/Api/V1/*`

## Operations
- Admin console screens: see README screenshot gallery
- System health: `/dashboard/system-health` · Webhook replay: `/dashboard/webhook-events`
- Scheduler: renewal reminders, grace expiry, data exports (`routes/console.php`)
- Security: [../SECURITY.md](../SECURITY.md)

## Contributing
- [../CONTRIBUTING.md](../CONTRIBUTING.md) — human workflow
- [../AGENTS.md](../AGENTS.md) — AI-assisted development rules
