# 06 — Mobile API (v1)

## Conventions

- Base path: `/api/v1`. JSON only.
- All responses use Laravel API Resources. One error envelope: { "message": "…", "code": "snake_case_stable_code", "errors": {} }
- Auth: Sanctum personal access tokens with abilities (e.g. read, billing:read, api-keys:manage) and a device_name. Tokens have a configurable TTL and are revocable per device.
- VERIFY first (Phase 0): how Tyro Login exposes auth over API (email verify enforcement, suspension, 2FA challenge, brute-force lockout). Reuse those endpoints — never fork the auth rules.
- Workspace context: X-Workspace-Id request header, validated against membership.
- Pagination: cursor-based. Lists support ?cursor= and return next_cursor.
- Caching: ETag / If-None-Match on cacheable list responses.
- Idempotency: Idempotency-Key header required on POSTs that create payments or subscriptions.
- Rate limits: per token (sliding window) and per IP. Document limits in OpenAPI.
- App config endpoint: GET /api/v1/app/config returns { min_app_version, maintenance, feature_flags, legal_urls } — used by mobile app to force-update old versions.
- OpenAPI: generate spec with Scramble or Scribe (VERIFY Laravel 13 support). Publish at /docs/api.

## Endpoint groups

| Group | Key endpoints | Priority |
|---|---|---|
| Auth | POST /auth/register, /auth/login, /auth/logout, /auth/logout-all, /auth/verify-email, /auth/forgot-password, /auth/reset-password, /auth/2fa/challenge | P0 |
| Me | GET/PATCH /me, PUT /me/avatar, PUT /me/password, GET/DELETE /me/sessions | P0 |
| Devices | POST /devices (register push token), DELETE /devices/{id} | P0 |
| Workspaces | GET /workspaces, POST /workspaces, GET/PATCH /workspaces/{id}, GET/POST/DELETE /workspaces/{id}/members, POST/DELETE /workspaces/{id}/invitations | P0 |
| Plans | GET /plans (public list with prices by currency) | P0 |
| Billing | GET /workspaces/{id}/subscription, GET /workspaces/{id}/usage, GET /workspaces/{id}/invoices, GET /workspaces/{id}/payments, POST /workspaces/{id}/checkout, POST /workspaces/{id}/subscription/cancel, /resume, /change-plan | P0 |
| Notifications | GET /notifications, PATCH /notifications/{id}/read, GET/PATCH /notifications/preferences | P1 |
| Support | POST /support/tickets, GET /support/tickets, POST /support/tickets/{id}/messages | P1 |
| Data rights | POST /me/export, DELETE /me | P1 |
| Feature flags | GET /workspaces/{id}/flags (resolved booleans for the app to branch on) | P2 |
| Webhooks | GET/POST /workspaces/{id}/webhooks, DELETE /workspaces/{id}/webhooks/{id}, GET /workspaces/{id}/webhooks/{id}/deliveries | P2 |
| Tags & custom fields | GET/POST /workspaces/{id}/tags, GET/PATCH /workspaces/{id}/custom-fields | P2 |
| Search | GET /workspaces/{id}/search?q= | P2 |

## Billing on mobile

Default approach (safe): mobile app shows plan info, usage and invoices, and opens web checkout in an in-app browser (SFSafariViewController / Chrome Custom Tab). No native card form.

Store billing (NOT included in this boilerplate): Apple App Store and Google Play have rules about selling digital subscriptions inside apps that vary by region and change over time. Before shipping any mobile paywall, read the current App Store Review Guidelines and Google Play Payments policy. If store billing is needed, add a fourth gateway adapter (RevenueCat or direct StoreKit/Billing Library). This is a separate ADR.

## Push notifications

- FCM for both Android and iOS (via APNs FCM bridge or direct APNs).
- devices table stores push_token, platform, app_version.
- Queued SendPushNotification job, interface-backed (swap provider without touching callsites).
- Deep links: universal links (iOS) / app links (Android) for invitations, magic login links and payment return URLs.
