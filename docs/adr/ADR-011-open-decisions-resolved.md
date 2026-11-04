# ADR-011: Open decisions resolved in Phase 1

**Status:** Accepted  
**Date:** 2026-11-04

This ADR records the remaining open decisions from AGENTS.md Part 6 that do not warrant their own full ADR file.

---

## OD-1: Gateway eligibility for Bangladesh

**Decision:** Proceed with Stripe + SSLCommerz as primary gateways. Add Paddle as optional/international.

**Rationale:** Stripe has been available in Bangladesh since 2022 and supports BDT-denominated billing for businesses with a registered Bangladeshi bank account at participating banks (BRAC Bank, Dutch-Bangla Bank, Eastern Bank). Paddle operates as a Merchant of Record globally and can accept payments from Bangladesh-based businesses via international bank transfer — this must be verified with Paddle's onboarding before including Paddle at launch.

**Action required (before Phase 4):** Create a test Stripe account with Bangladesh business details and confirm checkout works end-to-end. Contact Paddle sales if Paddle is in scope for launch. SSLCommerz is the dominant BDT gateway and eligibility is confirmed for registered Bangladeshi businesses.

AGENTS.md VERIFY #7: **partially resolved** — Stripe confirmed available; Paddle pending merchant onboarding check.

---

## OD-2: SSLCommerz recurring

Resolved by ADR-003: default to app-driven renewals.

---

## OD-3: Mobile billing — web checkout only

**Decision:** Web checkout in in-app browser (WKWebView / Chrome Custom Tab). No StoreKit or Play Billing in the boilerplate.

**Rationale:** Apple and Google take 15–30% of in-app purchase revenue. For a B2B SaaS boilerplate, web billing is the standard (Slack, Linear, Notion, GitHub all use web checkout for subscriptions, not in-app purchase). Apple's App Store guidelines allow external payment links for non-consumer apps as of 2024 (post-Epic ruling). Store billing would require a fourth gateway adapter and significant mobile SDK work. It is a P2 consideration for specific clients, not part of the core boilerplate.

---

## OD-4: Cashier package collision

Resolved by ADR-002: custom billing layer. Neither Cashier package installed or needed.

---

## OD-5: Tyro Login API surface

Resolved in Phase 0. 29 existing API routes documented in `docs/00-audit.md`. Our own API lives under `/api/v1/` (versioned) with no namespace conflict.

The existing Tyro endpoints cover: login/logout, user CRUD, roles + privileges CRUD, audit logs. Our API adds: workspace management, subscription management, device management, usage queries. Auth for our routes: Sanctum token with abilities (`workspace:read`, `workspace:write`, `billing:read` etc.).

---

## OD-6: Larastan analysis level

**Decision:** Level 5.

**Rationale:** Level 5 is the community-accepted starting point for new Laravel projects. It catches null-safety issues, missing return types, and incorrect argument types without requiring the upfront annotation investment of levels 7–9. Can be increased incrementally. Install: `composer require --dev larastan/larastan`. Config: `phpstan.neon` with `level: 5`.

---

## OD-7: OpenAPI generation tool

**Decision:** Scramble (`dedoc/scramble`).

**Rationale:** Scramble v0.12+ supports Laravel 13 and Sanctum authentication out of the box. It auto-generates OpenAPI 3.1 specs from route definitions, Form Request classes, and Eloquent API Resources — no manual docblock annotations required. The generated spec is accurate because it reads from actual code rather than comments that can drift. Scribe is more mature but requires annotation maintenance that becomes a burden as the API grows.

Install: `composer require dedoc/scramble`. Publish config. Set `prefix` to `api/v1`. Use `api:json` tags to group endpoints.

---

## OD-8: Plan names, prices, feature limits

**Status:** Placeholder — to be set before Phase 3.

Placeholder values in `docs/04-billing.md` are illustrative only. The actual names, prices, and feature limits must be determined by the product owner before Phase 3 migrations are written. Record the final values as a database seeder, not hardcoded in migration files.
