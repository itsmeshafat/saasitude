# 05 — Screens

Convention:
  [T]    = Tyro native — configure/theme only, do NOT rebuild
  [T+]   = Tyro native + we extend (add audit, billing block, etc.)
  [C]    = HasCrud resource on an Eloquent model
  [C+]   = HasCrud + custom validation or extra actions
  [S]    = scaffold with create-*-page command, then add controller/logic
  [B]    = fully custom Blade view in Tyro layout

Scaffold command reminder:
  php artisan tyro-dashboard:create-user-page "name"
  php artisan tyro-dashboard:create-admin-page "name"
  php artisan tyro-dashboard:create-common-page "name"

---

## Public site (unauthenticated — outside Tyro layouts)

| Screen | Build | Notes |
|---|---|---|
| Landing | [B] | Hero, features, social proof placeholder, pricing teaser, FAQ, CTA |
| Pricing | [B] | Monthly/annual toggle, USD/BDT toggle, plan cards + comparison table from DB features, FAQ |
| Features / use cases | [B] | Content blocks |
| Docs / Changelog | [B] | Markdown-driven, versioned |
| Legal | [B] | Terms, Privacy, Refund/Cancellation, Cookie notice — flag for lawyer review |
| Contact / Sales | [B] | Rate-limited + captcha form that creates a support ticket |
| Status | [B] | Simple page or embed from external status provider |
| Auth screens | [T] | Login, register, forgot/reset, verify email, OTP, magic link, 2FA — Tyro Login owns all of these |

---

## User dashboard (workspace members — tyro-dashboard::layouts.user)

| Screen | Scaffold command | Build | Notes |
|---|---|---|---|
| Home / dashboard | create-user-page "home" | [S] | Plan badge, usage meters, onboarding checklist, announcements via AdminNotice; override default dashboard view |
| Onboarding wizard | create-user-page "onboarding" | [S] | Step 1: name workspace. Step 2: invite team. Step 3: pick plan or start trial. Mark complete in settings |
| Workspace switcher | — | [B] | Header component injected via Tyro layout; lists workspaces, creates new |
| Workspace settings | create-user-page "workspace-settings" | [S] | Name, logo, slug, danger zone (transfer ownership, delete workspace) |
| Team members | create-user-page "team" | [S] | Members list, invite by email (extends Tyro invitation system), change role, remove, pending invites |
| Billing overview | create-user-page "billing" | [S] | Current plan, status pill, next renewal date, usage vs limits bars, gateway badge, trial countdown |
| Change plan | create-user-page "plans" | [S] | Plan cards, monthly/annual toggle, proration preview (Stripe/Paddle), downgrade usage warnings |
| Checkout | create-user-page "checkout" | [S] | Gateway selector by currency/region, coupon field, redirect to gateway hosted page |
| Payment methods | create-user-page "payment-methods" | [S] | Stripe/Paddle: link to Customer Portal. SSLCommerz: last payment info + Pay now link |
| Invoices & receipts | create-user-page "invoices" | [S] | Paginated list, PDF download via signed URL |
| Cancel flow | create-user-page "cancel" | [S] | Reason survey (required), offer pause/downgrade, confirm cancel-at-period-end date |
| API keys / tokens | create-user-page "api-keys" | [S] | Create with abilities, name, expiry; revoke; last used at; copy-once display |
| Notifications | create-user-page "notifications" | [S] | List with mark read, preferences (email on/off per type, push on/off) |
| Profile & security | — | [T] | Native at /dashboard/profile — avatar, password, 2FA, active sessions |
| Referrals | — | [T] | Native at /dashboard/referrals — Tyro invitation system |
| Support | create-user-page "support" | [S] | New ticket form + my tickets list with status |
| Data & privacy | create-user-page "data-privacy" | [S] | Request data export (queued job), delete account with confirmation via showDanger |

---

## Admin dashboard (staff — tyro-dashboard::layouts.admin)

| Screen | Scaffold / source | Build | Notes |
|---|---|---|---|
| Users | — | [T] | Native at /dashboard/users — list, create, edit, suspend, delete |
| Roles & privileges | — | [T] | Native at /dashboard/roles and /dashboard/privileges |
| Impersonation | — | [T+] | Native POST /dashboard/users/{id}/impersonate — we add: audit entry on start/end, billing action block while impersonating, persistent banner |
| Audit trail | — | [T] | Native at /dashboard/audit-trail — field-level diffs, filterable |
| Invitations | — | [T] | Native at /dashboard/invitations — create, revoke, stats |
| Tyro settings | — | [T] | Native at /dashboard/settings/tyro and /dashboard/settings/tyro-login |
| Overview / analytics | create-admin-page "overview" | [S] | MRR, ARR, active subs, trials started, churn rate, failed payments, signups chart, revenue by gateway. Date range filter. Document metric formulas in the controller. |
| Workspaces | create-admin-page "workspaces" | [S] | Search + list. Detail view: members, current plan, usage, payment history, grant/extend trial, force plan change, suspend workspace |
| Subscriptions | create-admin-page "subscriptions" | [S] | Filter by status + gateway. Detail: full state-change timeline, raw webhook event list |
| Payments & refunds | create-admin-page "payments" | [S] | List with filters. Issue refund button (requires billing.refund privilege, showDanger confirm, writes audit entry) |
| Invoices | create-admin-page "invoices" | [S] | List, view, regenerate PDF |
| Plans & prices | Model: Plan with HasCrud | [C+] | Publish/unpublish toggle, prevent deleting active-subscriber plans, custom validation for price_version |
| Features | Model: Feature with HasCrud | [C] | key, name, type, unit |
| Coupons | Model: Coupon with HasCrud | [C+] | Custom validation: max_redemptions, expiry, plan_ids restriction |
| Announcements | Model: Announcement with HasCrud | [C+] | audience, starts_at, ends_at; integrate with AdminNotice::show() for urgent (non-scheduled) messages |
| Webhook events | create-admin-page "webhook-events" | [S] | Status list, payload JSON viewer, replay button (requires webhooks.replay privilege, idempotent), error count, last attempt |
| Gateway settings | create-admin-page "gateway-settings" | [S] | Per-gateway: keys (masked, encrypted in settings table), test/live mode toggle, webhook URL display, connection test button. showDanger on switching to live mode. |
| Email templates & log | create-admin-page "email-templates" | [S] | Preview rendered template, send test to admin email, sent-mail log |
| Support inbox | create-admin-page "support-inbox" | [S] | Ticket list, thread view, reply, close, assign |
| Feature flags (optional) | create-admin-page "feature-flags" | [S] | Via Laravel Pennant — toggle features per workspace or globally |
| System health | create-admin-page "system-health" | [S] | Queue depth, failed jobs count + list, scheduler last run, disk usage, DB size, cache hit rate |
| App settings | create-admin-page "app-settings" | [S] | Brand name, support email, legal page URLs, trial length (days), grace period (days), invoice company info. Secrets encrypted in settings table. |
| Data requests | create-admin-page "data-requests" | [S] | Export/deletion request queue — status, trigger/retry, mark complete |

---

## Staff privileges to seed (Tyro privileges)

billing.view       view subscriptions, payments, invoices
billing.refund     issue refunds (always audited + showDanger)
plans.manage       create/edit/publish plans, features, prices
coupons.manage     create/edit/delete coupons
workspaces.manage  suspend, trial extend, force plan change
webhooks.replay    replay failed webhook events
settings.manage    gateway keys, app settings, email templates
support.reply      access support inbox, reply to tickets
impersonate        start/leave impersonation (always audited)
audit.view         read-only access to audit trail

Suggested roles:
  super-admin   all privileges
  admin         all except settings.manage and billing.refund
  support       billing.view + support.reply + impersonate
  finance       billing.view + billing.refund

---

## Platform extras — P2, build after Phase 11 (see docs/00-research.md, docs/03-data-model.md)

Not part of the MVP path. Build only when scheduled — each references the data model table of the same name added in `03-data-model.md`.

| Screen | Scaffold / source | Build | Notes |
|---|---|---|---|
| Feature flags | create-admin-page "feature-flags" | [S] | Toggle global + per-workspace via Pennant (ADR-9) |
| Outbound webhooks | create-user-page "webhooks" | [S] | Workspace creates subscription (url, events, secret shown once); delivery log with retry/replay |
| Email templates (ours) | create-admin-page "email-templates" | [C+] | Distinct from the existing Phase 8 screen of the same name if that one only previews — this one edits `email_templates` rows |
| Changelog editor | Model: ChangelogEntry with HasCrud | [C] | Feeds the public changelog page in 05-screens public site |
| Status page admin | create-admin-page "status-incidents" | [S] | Create/update/resolve incidents; feeds public status page |
| Feedback inbox | create-admin-page "feedback" | [S] | List + status (triage) |
| Feedback widget | — | [B] | Small floating button on every authenticated screen, posts to feedback_submissions |
| Onboarding checklist | — | [T+] | Extends the Phase 2 onboarding wizard with a persistent dismissible checklist card, backed by `onboarding_steps` |
| Advanced search | create-user-page "search" | [S] | Cross-resource search within a workspace (workspaces, members, invoices, tickets) |
| Custom fields | create-admin-page "custom-fields" | [S] | Define fields per entity type; values rendered in the matching HasCrud/custom screen |
| Workspace tags | create-user-page "tags" | [C] | Manage tags; apply from workspace settings or team screen |
| Workspace templates | create-user-page "templates" | [S] | Save current workspace config as template; create new workspace from template |
| Activity comments & reactions | — | [B] | Attach to audit-trail / activity feed entries |
| Internal announcements | create-user-page "workspace-announcements" | [C] | Workspace-scoped, distinct from the global `announcements` admin screen |
| Connected accounts | — | [T+] | Surface Tyro Login's linked Socialite providers; add disconnect action |
| Login activity log | — | [S] | Per-user login history (ip, device, success/fail), separate view from Tyro's global audit trail |
| CSV import | create-user-page "import" | [S] | Upload → queued job → row-level error report |
| System ops: health monitor | create-admin-page "system-health" | already listed above | — |
| System ops: scheduled tasks monitor | create-admin-page "scheduled-tasks" | [S] | Last-run time, next-run time, failure count per `schedule()` entry |
| System ops: data retention | create-admin-page "data-retention" | [S] | Configure per-entity retention period; shows a dry-run preview of what a purge job would delete |
| System ops: SEO management | create-admin-page "seo-settings" | [S] | Per-public-page meta title/description/OG image overrides, sitemap toggle |
| System ops: maintenance mode | create-admin-page "maintenance" | [S] | Wraps `php artisan down/up`; shows countdown + allowed-IP bypass list |
| Admin: bulk user actions | — | [T+] | Multi-select on native Tyro users list, add bulk suspend/delete/export |
| Admin: application log viewer | create-admin-page "app-logs" | [S] | Tail `storage/logs`, filter by level — read-only, no secrets rendered |
| Localization management | create-admin-page "localization" | [S] | Manage translation strings per locale (supports NFR i18n-ready goal) |

---

## Global UI rules (apply to every screen)

- Use showConfirm/showDanger for ALL confirm dialogs — never browser confirm()
- Empty state required: helpful message + primary action button
- Loading state required: skeleton or spinner (not blank)
- Error state required: clear message + retry action
- Test at 1440px desktop AND 390px mobile before marking done
- Dark mode: check after every screen (Tyro dark mode is automatic but custom CSS vars need both)
- WCAG 2.2 AA contrast required — run design-critique audit after each UI phase
