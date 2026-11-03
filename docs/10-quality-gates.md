# 10 — Quality Gates, Security & Go-live

## Per-ticket definition of done

- Failing test written first, then made to pass (Pest).
- ./vendor/bin/pint passes (zero style errors).
- Static analysis passes (Larastan level — choose and record in ADR).
- No N+1 queries on list pages.
- Authorization policy tested (correct user can, wrong user cannot).
- /code-review run and findings addressed.
- Audit/log events present where required.
- Docs updated (GLOSSARY.md, relevant ADR if decision changed).

## Billing test matrix (every gateway adapter must pass)

| Scenario | Must test |
|---|---|
| New subscription, no trial | Yes |
| New subscription, with trial | Yes |
| Duplicate webhook delivered twice | Idempotent — second delivery changes nothing |
| Webhook delivered out of order | Handled gracefully |
| Forged webhook (bad signature) | Rejected with 400, nothing processed |
| Amount tampering on IPN (SSLCommerz) | Rejected, payment not granted |
| Payment failure then recovery | State machine correct |
| Cancel at period end, then resume | Both transitions correct |
| Upgrade with proration | Prorated immediately |
| Downgrade blocked by current usage | Pre-check returns warning |
| Refund — full | Payment and invoice updated |
| Refund — partial | Correct amount |
| Coupon applied at checkout | Discount applied |
| Coupon expired | Rejected |
| Currency mismatch | Rejected |
| Workspace A cannot see workspace B billing | AuthorizationException thrown |

## Security checklist

- [ ] Webhook/IPN signature verified before any processing
- [ ] Idempotency replay window honoured (no double-charge)
- [ ] CSRF exclusions only for webhook routes — confirm current Laravel exclusion mechanism (VERIFY)
- [ ] All gateway secrets in .env, never in code or logs
- [ ] Admin 2FA required (enforce at middleware level)
- [ ] Rate limits on: login, register, forgot-password, /api/v1, webhooks, contact form
- [ ] Signed URLs for invoice PDF downloads
- [ ] File upload: extension + MIME validation, stored outside webroot
- [ ] Security headers: CSP, HSTS, X-Frame-Options, X-Content-Type-Options
- [ ] composer audit and npm audit in CI — zero critical/high
- [ ] Impersonation: audited, blocks billing-destructive actions
- [ ] Session and Sanctum token expiry configured
- [ ] Production backups tested by restore (not Tyro Checkpoint — dev only)
- [ ] Outbound webhook payloads signed (HMAC) and subscription secrets encrypted at rest (Phase 14)
- [ ] Feature flag changes audited (who flipped what, when) (Phase 14)

## Go-live checklist

- [ ] Gateway live keys in .env (not test keys)
- [ ] Webhook endpoints registered in each gateway dashboard
- [ ] Test events delivered and handled correctly in production
- [ ] Invoice number sequence, company name and address set in app settings
- [ ] Legal pages reviewed by a lawyer
- [ ] Tax approach confirmed with an accountant (Paddle MoR vs Stripe Tax vs local BDT invoicing)
- [ ] Queue workers supervised (Horizon or Supervisor)
- [ ] Scheduler cron running
- [ ] Error monitoring configured (Sentry, Flare, or similar)
- [ ] Backup job running and restore tested
- [ ] Status page live or linked
- [ ] Support email configured and tested
- [ ] Refund policy published

## Open decisions (answer in Phase 1)

1. Workspace-level billing from day one, or user-level?
2. Default USD gateway: Stripe or Paddle? (Check merchant eligibility for Bangladesh first.)
3. Is SSLCommerz recurring enabled on the merchant account, or ship app-driven renewals?
4. Mobile app: web checkout only, or store billing (separate ADR needed)?
5. Final plan names, prices and feature limits (numbers in docs/04-billing.md are placeholders).
6. Which Larastan level for static analysis?
7. Which OpenAPI generation tool: Scramble or Scribe?
