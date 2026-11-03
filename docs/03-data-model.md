# 03 — Data Model

Agent: generate migrations, factories and Eloquent models from this table. Apply `HasAuditTrail` on the models marked ★.

| Table | Key columns |
|---|---|
| `workspaces` ★ | id, name, slug, owner_id, is_personal, settings (json), trial_used_at |
| `workspace_members` | workspace_id, user_id, role (owner/admin/member/billing), joined_at |
| `workspace_invitations` | workspace_id, email, role, token_hash, expires_at, accepted_at |
| `plans` ★ | id, key (unique), name, description, is_public, sort, metadata (json) |
| `plan_prices` ★ | id, plan_id, interval (monthly/annual), currency (ISO), amount_minor, gateway (stripe/paddle/sslcommerz), gateway_price_id, price_version, active |
| `features` | id, key (unique), name, type (boolean/limit/metered), unit |
| `plan_features` | plan_id, feature_id, value (null=unlimited / int / bool stored as int) |
| `subscriptions` ★ | id, workspace_id, plan_price_id, gateway, gateway_subscription_id, status, trial_ends_at, current_period_start, current_period_end, cancel_at_period_end, canceled_at, price_version |
| `payments` | id, workspace_id, subscription_id (nullable), gateway, gateway_payment_id, amount_minor, currency, status, paid_at, raw (json) |
| `refunds` | id, payment_id, amount_minor, reason, gateway_refund_id, status, by_user_id |
| `invoices` | id, workspace_id, number (sequential), payment_id, lines (json), tax_minor, total_minor, pdf_path |
| `gateway_customers` | id, workspace_id, gateway, gateway_customer_id |
| `webhook_events` | id, gateway, event_id, type, payload (json), status, attempts, processed_at, error — UNIQUE(gateway, event_id) |
| `coupons` ★ | id, code (unique), type (percent/fixed), value, duration (once/repeating/forever), duration_months, max_redemptions, plan_ids (json), expires_at |
| `coupon_redemptions` | id, coupon_id, workspace_id, subscription_id, redeemed_at |
| `usage_records` | id, workspace_id, feature_id, period (YYYY-MM), quantity |
| `devices` | id, user_id, platform (ios/android/web), push_token, app_version, last_seen_at |
| `notifications` | id, notifiable_type, notifiable_id, type, data (json), read_at |
| `announcements` ★ | id, title, body, audience (all/users/admins), starts_at, ends_at |
| `settings` ★ | id, key (unique), value (encrypted when secret), group |
| `support_tickets` | id, workspace_id, user_id, subject, status (open/closed), created_at |
| `ticket_messages` | id, ticket_id, user_id, body, is_staff, created_at |

## Platform extras (P2 — see docs/00-research.md reference-SaaS section)

Not needed for MVP (P0/P1 phases). Add each table only when its phase is scheduled.

| Table | Key columns |
|---|---|
| `feature_flags` | id, key (unique), name, is_global_enabled | via Laravel Pennant — this table backs Pennant's database driver, don't hand-roll |
| `feature_flag_workspace` | feature_flag_id, workspace_id, enabled | per-workspace rollout override |
| `webhook_subscriptions` ★ | id, workspace_id, url, secret (encrypted), events (json array of our event names), is_active | outbound — customer-configured, distinct from inbound `webhook_events` in 04-billing.md |
| `webhook_deliveries` | id, webhook_subscription_id, event, payload (json), response_status, attempts, delivered_at | retry with backoff; show in a delivery-log screen |
| `email_templates` ★ | id, key (unique), subject, body_html, body_text, updated_by | admin-editable overrides for our own Mailables (not Tyro Login's built-in templates) |
| `changelog_entries` | id, title, body, published_at, tags (json) | public changelog page content |
| `status_incidents` | id, title, status (investigating/identified/monitoring/resolved), impact, body, started_at, resolved_at | public status page |
| `feedback_submissions` | id, user_id, workspace_id, type (bug/idea/other), message, page_url, status | from in-app feedback widget |
| `onboarding_steps` | workspace_id, step_key, completed_at | checklist progress; one row per step per workspace |
| `custom_fields` ★ | id, workspace_id, entity_type, key, label, type, options (json) | workspace-defined metadata fields |
| `custom_field_values` | custom_field_id, entity_type, entity_id, value | polymorphic value storage |
| `tags` | id, workspace_id, name, color | |
| `taggables` | tag_id, taggable_type, taggable_id | polymorphic pivot |
| `workspace_templates` | id, workspace_id (nullable = global), name, config (json snapshot of settings/roles) | "save as template" / "create from template" |
| `activity_comments` | id, activity_id (audit_trail entry or domain activity), user_id, body | comments on an activity item |
| `internal_announcements` | id, workspace_id, title, body, created_by | per-workspace, distinct from global `announcements` table |
| `activity_reactions` | activity_id, user_id, emoji | unique(activity_id, user_id, emoji) |
| `connected_accounts` | id, user_id, provider, provider_user_id, token (encrypted) | Socialite providers already linked — surfaced as a settings screen, Tyro Login owns the OAuth flow itself |
| `login_activity` ★ | id, user_id, ip, user_agent, location, success, created_at | per-login audit, separate from Tyro's global audit trail |
| `password_changes` | id, user_id, changed_at | history only, never store old hashes |
| `csv_import_jobs` | id, workspace_id, entity_type, file_path, status, row_count, error_log (json) | queued import job tracking |

## Subscription status enum

`trialing` → `active` → `past_due` → `canceled` / `expired`
Also: `canceled_pending` (cancel at period end), `incomplete`, `paused` (optional).

## Invoice number sequence

Format: `INV-YYYY-NNNNNN` (zero-padded, per calendar year). Use a DB sequence or locked counter in `settings` — never rely on `MAX(id) + 1`.
