# Security Policy

## Reporting a vulnerability

Found something that shouldn't be public? Please email **hello@itsmeshafat.com**
(replace with your real inbox before launch) with details and reproduction steps.
Don't open a public issue for vulnerabilities. We aim to respond within 72 hours.

## Security posture

How SaaSitude is built with security in mind:

- **Payment state is set only by verified webhooks/IPN.** Every webhook is
  signature- or hash-verified before processing, stored uniquely
  (`webhook_events`: gateway + event id), processed via queued jobs, and
  idempotent on replay. Return/success URLs are display-only.
- **SSLCommerz IPNs** additionally pass hash verification + the `val_id`
  validation API + amount/currency/transaction matching before any state change.
- **Gateway secrets are encrypted at rest** (`Crypt::encryptString`, `settings`
  table, `is_secret`) — never in `.env`, never logged, masked in the admin UI.
- **CSRF**: `/webhooks/*` routes are the only CSRF-exempt routes.
- **Auth**: Tyro Login's hardened surface — brute-force lockout, optional TOTP
  2FA, passkeys, captcha, suspension. We hook its events; we never fork its flows.
- **RBAC**: global staff roles + fine-grained Tyro privileges; workspace roles
  are separate (`workspace_members`) so billing staff never get global power.
- **Impersonation guard**: refunds and plan changes are blocked while
  impersonating; every impersonation start/end writes an audit entry.
- **Audit trail** on all admin-mutable models (`HasAuditTrail`).
- **API**: Sanctum token abilities, per-token rate limits, `Idempotency-Key`
  required on payment POSTs, workspace context validated against membership.
- **Money**: integer minor units end-to-end — no float-rounding attacks.
- **First-run installer**: gated by `storage/app/installed.lock`; once
  installed, `/install*` 404s permanently.

## Disclosure timeline

1. You report privately
2. We confirm and triage (≤ 72 h)
3. Fix developed and released (target: ≤ 14 days for critical)
4. Public advisory for significant issues (coordinated with reporter)
