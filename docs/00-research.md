# 00 — Research & Verified Facts

## Tyro ecosystem (source: hasinhayder.github.io/tyro-dashboard/doc.html, Packagist, GitHub)

| Package | Version | What it ships |
|---|---|---|
| hasinhayder/tyro | v1.15.0 | RBAC foundation: role/privilege models, HasAuditTrail trait, Blade directives (@hasRole, @hasPrivilege), middleware (tyro-dashboard.admin, TyroImpersonation), 40+ Artisan commands. Bootstrap: php artisan tyro:sys-install |
| hasinhayder/tyro-login | bundled | Full auth UI: login, register, forgot/reset password, email verification, email OTP/PIN, magic login link, TOTP 2FA, Socialite social login, captcha, brute-force lockout, user suspension, 5 layouts, dark/light themes, email templates |
| hasinhayder/tyro-dashboard | v1.15.0 | See detailed breakdown below |
| hasinhayder/tyro-checkpoint | dev-only | Local DB snapshots: create, list, restore, lock, flush, optional encryption. NOT a production backup. |
| laravel/boost | dev | MCP server (schema, routes, logs, tinker, docs search), AI guidelines, Agent Skills. boost:install / boost:update. Custom guidelines in .ai/guidelines. Custom skills in .ai/skills/{name}/SKILL.md |

---

## Tyro Dashboard — detailed feature inventory

### Authentication shell
All Tyro Login flows wired in automatically.
Hook via Laravel events: Registered, Login, Logout, Attempting, Failed.
DO NOT rewrite. Listen to events to extend behaviour.

### Dashboard layouts (Blade)
tyro-dashboard::layouts.user   authenticated users only
tyro-dashboard::layouts.admin  staff/admin only
tyro-dashboard::layouts.app    both users and admins
Global $user variable injected via view composer into ALL dashboard views.
NEVER pass $user manually.

### Theming — shadcn CSS variables (oklch)
Publish: php artisan tyro-dashboard:publish-style --theme-only
Edit: resources/views/vendor/tyro-dashboard/partials/shadcn-theme.blade.php
Visual editor: tweakcn.com

Available CSS variables:
--background, --foreground, --primary, --primary-foreground,
--secondary, --secondary-foreground, --muted, --muted-foreground,
--accent, --accent-foreground, --destructive, --destructive-foreground,
--border, --input, --ring, --card, --card-foreground,
--popover, --popover-foreground,
--sidebar, --sidebar-foreground, --sidebar-primary, --sidebar-primary-foreground,
--sidebar-accent, --sidebar-accent-foreground, --sidebar-border, --sidebar-ring,
--success, --warning, --info, --chart-1 through --chart-5,
--card-shadow, --card-shadow-hover

Env-based sidebar:
TYRO_DASHBOARD_SIDEBAR_BG / _SIDEBAR_TEXT / _COLLAPSIBLE_SIDEBAR

### User & staff management (native routes — do not rebuild)
/dashboard/users                  list, search, suspend, delete
/dashboard/users/create           create user
/dashboard/users/{id}/edit        edit user
/dashboard/roles                  list roles (protected: admin, super-admin, user)
/dashboard/roles/create
/dashboard/roles/{id}             edit role
/dashboard/privileges             list privileges
/dashboard/privileges/create
/dashboard/settings/tyro          Tyro RBAC settings (admin only)
/dashboard/settings/tyro-login    Tyro Login settings (admin only)
Superuser: php artisan tyro-dashboard:createsuperuser
admin_roles in config/tyro-dashboard.php defines who is staff.

### Profile (native — /dashboard/profile)
Name, email, password, avatar (HasProfilePhoto + intervention/image), Gravatar fallback.
Env: TYRO_DASHBOARD_ENABLE_GRAVATAR / _PHOTO_MAX_SIZE / _PHOTO_CROP_POSITION

### Impersonation (native)
POST /dashboard/users/{id}/impersonate  start (admin only)
GET  /dashboard/leave-impersonation     end
State in server-side session. We add: HasAuditTrail entry on start/end,
block billing-destructive actions, persistent impersonation banner in UI.

### Invitation & referral system (native)
Enable: TYRO_DASHBOARD_ENABLE_INVITATION=true
Tables auto-migrated: invitation_links, invitation_referrals
/dashboard/invitations  admin (create, revoke, stats)
/dashboard/referrals    user (own referral history)
Workspace invitations should extend this system — do not create a parallel one.

### Audit trail (native)
/dashboard/audit-trail       filterable, paginated (admin only)
/dashboard/audit-trail/{id}  field-level old/new diffs
Apply HasAuditTrail to every model an admin can mutate:
Workspace, Subscription, Plan, PlanPrice, Coupon, Settings, gateway_customers.

### Modal dialogs (globally available in all dashboard views — no import needed)
showConfirm(title, message, callback)  standard confirm (blue)
showAlert(title, message)              informational
showDanger(title, message, callback)   destructive action (red)
HasCrud uses showDanger for delete automatically.
Apply showDanger to ALL custom destructive admin actions.

### Admin bar announcements
Static env: TYRO_DASHBOARD_ADMIN_BAR_ENABLED / _MESSAGE / _BG_COLOR / _TEXT_COLOR / _ALIGN / _HEIGHT
Dynamic PHP: AdminNotice::show($message, $bgColor, $textColor, $align)
Allowed HTML in message: <p> <a> <b> <span> + inline styles only.
Use for: billing alerts, trial expiry notices, maintenance windows.

### HasCrud trait — auto-generates full CRUD for any Eloquent model
Route: /dashboard/resources/{plural-model}
Add: use HasinHayder\TyroDashboard\Concerns\HasCrud;
Reads $fillable to generate form fields automatically.

Auto-detected field types by column name pattern:
  *_id              select (BelongsTo relationship)
  is_*/has_*/can_*  boolean checkbox
  description/bio/content/body  textarea
  BelongsToMany method  multi-select with auto pivot sync

Override any field with $resourceFieldOverrides array. Available keys:
  type, label, rules, default, placeholder, help_text,
  hide_in_index, hide_in_create, hide_in_edit, hide_in_form, hide_in_single_view,
  readonly, searchable, sortable, attributes,
  relationship, option_label, multiple,
  display_image, display_image_position (top|bottom)

Available field types:
  text, number, textarea, markdown, richtext, boolean, select,
  multiselect, radio, checkbox, date, time, datetime-local,
  password, email, url, file

Role access: $resourceRoles (access), $resourceReadonly (read-only role), $readonly (fully read-only).
Other: $resourceKey, $resourceTitle, $resourceTitleSingular,
       $resourceUploadDisk, $resourceUploadDirectory.
Old files auto-deleted on replacement. display_image => true shows inline preview.

### Page scaffold commands — ALWAYS run before hand-writing a dashboard page
php artisan tyro-dashboard:create-user-page "name"    user layout + route + user sidebar link
php artisan tyro-dashboard:create-admin-page "name"   admin layout + route + admin sidebar link
php artisan tyro-dashboard:create-common-page "name"  app layout + route + both sidebar links
php artisan tyro-dashboard:remove-user-page "name"    cleans up view + route + sidebar link
php artisan tyro-dashboard:remove-admin-page "name"   cleans up all three
php artisan tyro-dashboard:remove-common-page "name"  cleans up all three
Add --force to overwrite an already-published view.

Generated files:
  resources/views/dashboard/{name}.blade.php
  routes/web.php (route appended)
  resources/views/vendor/tyro-dashboard/partials/{user|admin}-sidebar.blade.php (link added)

### Sidebar — config/menu.php
  adminMenuItems   admin sidebar only
  commonMenuItems  both sidebars
  userMenuItems    user sidebar only
Each: ['title' => '', 'url' => '', 'icon' => '<svg>...</svg>']
NEVER hand-edit sidebar blade partials directly.

### Branding env vars
TYRO_DASHBOARD_APP_NAME / _LOGO / _PREFIX (dashboard or admin) / _DISABLE_EXAMPLES

### Tyro Checkpoint commands (dev only)
php artisan tyro-checkpoint:create "name" [--note="..."] [--encrypt]
php artisan tyro-checkpoint:list
php artisan tyro-checkpoint:restore "name"
php artisan tyro-checkpoint:lock "name"
php artisan tyro-checkpoint:flush
DB support: SQLite confirmed. MySQL/PostgreSQL needs mysqldump/pg_dump — VERIFY on install.

---

## Things to VERIFY at Phase 0 (agent resolves before writing any code)

1. php artisan list tyro-dashboard and list tyro — confirm all commands match this doc.
2. php artisan route:list --path=dashboard — confirm native routes match this doc.
3. php artisan route:list --path=api — document Tyro Login API endpoints before designing /api/v1/auth/*.
4. php artisan tyro-checkpoint:list — confirm DB engine support for your setup.
5. Check composer.json for intervention/image (required for avatar upload).
6. Verify Cashier Stripe + Cashier Paddle coexistence (migration/trait collision) via Context7. Record as ADR-2.
7. Verify Stripe and Paddle merchant eligibility for Bangladesh-based businesses.
8. Confirm SSLCommerz recurring (schedule parameter) is enabled on the merchant account.

---

## Payment gateways

| Gateway | Integration path | Key notes |
|---|---|---|
| Stripe | stripe/stripe-php OR laravel/cashier-stripe | Checkout Sessions (subscription mode), Customer Portal, webhooks + HMAC signature. Stripe CLI for local testing. VERIFY Bangladesh merchant eligibility. |
| Paddle | paddle/paddle-php or HTTP OR laravel/cashier-paddle v2 (Billing only, NOT Classic) | Overlay or hosted checkout, workspace_id in custom_data, webhooks + signature. Merchant of Record — handles VAT/tax automatically. VERIFY Bangladesh eligibility. |
| SSLCommerz | sslcommerz/laravel or rayhan-bapari/sslcommerz or thin in-house client | Hosted checkout, IPN + hash verify + val_id server validation. Recurring via schedule parameter (VERIFY on merchant account). Default plan: app-driven renewals with scheduler. |

---

## Design skills (code-level audit 2026-11-02)

ArnavPuri/designskills — MIT, pinned commit c7873015. 28 skills. No install hooks, no permission
overrides. Gemini key read from env only, sent only to Google genai client. One CDN fallback
in audit.mjs (axe-core from jsdelivr) — neutralised by installing axe-core locally.

nextlevelbuilder/ui-ux-pro-max-skill — MIT, ui-ux-pro-max-cli@2.15.0 audited. Core search
is local-only (no network). Default uipro init downloads latest GitHub release zip — use --offline.
Gemini scripts read .env from skill folder / ~/.claude/skills/.env / ~/.claude/.env only.
Never reads your Laravel project .env.

Full audit findings and hardened install commands: docs/07-design-system.md.

---

## Reference SaaS: itsmeshafat/Laravel-SAAS-Starter (fork of XCO-Agency/Laravel-SAAS-Starter)

Source: github.com/itsmeshafat/Laravel-SAAS-Starter README + `/docs` and `/docs/features` index (read 2026-11-03). Different stack from this project (React/Inertia + Fortify + Cashier vs. our Tyro/Blade stack) — use as a **feature checklist to port**, not as code to copy.

Stack: Laravel 12, PHP 8.4, React 19 + Inertia v2, Tailwind v4 + shadcn/ui, Laravel Fortify (auth), Laravel Cashier/Stripe (billing), Spatie Activitylog (audit), Laravel Pennant (feature flags), Pest (1,400+ tests).

### Features already covered by our Tyro stack — no new build needed
Admin panel, impersonation, audit trail (HasAuditTrail ~ Spatie Activitylog), 2FA (TOTP via Tyro Login), social login ("Connected Accounts"), invitations/roles (Workspaces P0 module), admin bar announcements (~ "Global Announcements"), i18n-ready (NFR).

### Features to add to our module list (not yet in 01-prd.md)
Grouped as in the source repo's `docs/features/` index:

| Category | Feature | Notes |
|---|---|---|
| Admin ops | System Health Monitor, Scheduled Tasks Monitor, Data Retention Policies, SEO Management | Admin-only dashboards/config pages; low complexity, mostly read-only views over existing queue/scheduler/model state |
| Admin ops | Admin Bulk User Actions, Admin User Session Management, Admin Application Log Viewer, Admin Maintenance Mode, Admin System Notifications, Admin Cohort Retention Analysis | Extend our existing admin panel (Tyro) rather than new subsystem |
| Platform | Feature Flags | Laravel Pennant, DB-driven, workspace-scoped rollout — VERIFY coexistence with Tyro's role system |
| Platform | Public Changelog, Email Templates (admin-editable), Public Status Page | Simple content/CMS-style pages |
| Platform | Webhooks & Delivery Logs | Outbound webhooks (workspace subscribes a URL to our events) — distinct from the *inbound* gateway webhooks we already plan in 04-billing.md |
| Platform | Real-time Notifications, Notification Channel Preferences, Notification Delivery Analytics | Extends existing P1 "In-app notifications" row; real-time needs a broadcast driver (Reverb/Pusher) — ADR needed |
| Platform | User Feedback Widget, Onboarding Checklist, Onboarding Wizard, In-App Product Tour, Onboarding Completion Insights | Growth/activation features, independent of billing |
| Platform | Advanced Global Search | Cross-resource search within a workspace |
| Workspace extras | Custom Fields, Workspace Tags, Workspace Templates, Workspace Comments, Workspace Announcements, Activity Reactions | Metadata/collaboration layer on top of the Workspaces P0 module |
| Workspace extras | Workspace Custom Branding, Workspace Trash & Restore, Workspace Suspension, Workspace Email Domain Restriction, Workspace Data Import (CSV), Weekly Workspace Activity Digest, Workspace Retention Insights, Workspace Analytics Dashboard, Workspace Member Activity Report | Workspace-admin-facing extras, mostly P2/P3 |
| Security | 2FA Enforcement **per workspace** (policy, not just per-user), Login Activity Log, Password Change History, Session Activity Summary, User Login Methods Summary | Extends our existing auth/session modules |
| API | Workspace API Keys, API Authentication, API Documentation, API Usage Dashboard | We already plan "Customer API keys" (P2) and `/api/v1` (06-api.md) — add a usage-dashboard view and published API docs page |
| Misc | Permission Preset Templates, Granular Roles & Permissions, Localization Management, Global Support Ticket System, Revenue Analytics, User Timezone & Date Format, User Last Seen Timestamp, Shareable Invite Links, Billing Reminder Notifications | Mostly refinements of existing P0/P1 modules (roles, support inbox, admin analytics, invitations) |

### Suggested priority for the new items
Treat all of the above as **P2** (nice-to-have, post-MVP) unless a specific launch requirement pulls one up — none of this blocks the P0 auth/workspace/billing path. Revisit per-item priority during roadmap planning once P0/P1 modules are built. Full per-feature implementation notes live in the source repo's `docs/features/*.md` (not mirrored here) — open the specific page on github.com/itsmeshafat/Laravel-SAAS-Starter when a feature is actually scheduled, since our stack (Blade/Tyro, not React/Inertia) means the implementation will differ from source even when the feature list matches.
