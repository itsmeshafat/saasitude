# Tyro Login API surface (Phase 9 audit — 2026-11-05)

Result of `php artisan route:list --path=api`. This is what already exists and is
REUSED — nothing below is rebuilt in `/api/v1`.

## Authentication (reused)

| Route | Notes |
|---|---|
| `POST /api/login` | Token login (Tyro) — the only auth entry point over API |
| `GET /api/me` | Tyro's own me endpoint (kept; our richer `GET /api/v1/me` is separate) |

## Admin RBAC (Tyro, admin-gated — not part of the mobile surface)

`/api/audit-logs`, `/api/privileges*`, `/api/roles*`, `/api/users*`,
`/api/users/{user}/roles*`, `/api/users/{user}/suspend`,
`/api/tyro`, `/api/tyro/version`

## Gaps — NOT exposed over API by Tyro Login

- register, forgot/reset password, email verification, 2FA challenge,
  logout/logout-all, social login
- These are web-only today. Mobile auth flow: use `POST /api/login` for token
  exchange; password reset etc. happen via deep link to the web app.
- Per AGENTS.md: never fork Tyro Login's auth rules — if an API flow is needed,
  it must come from the package (upstream) not from us.

## Conventions for /api/v1

- Auth: Sanctum tokens from `POST /api/login`, abilities `read`, `write`,
  `billing:read`, `billing:write` (plus `*` for admin tooling).
- Workspace context: `X-Workspace-Id` header, validated against
  `workspace_members` (403 `workspace_forbidden` for non-members).
- Errors: one envelope `{message, code, errors}` (see `bootstrap/app.php`).
- Idempotency-Key required on `POST .../checkout`, `/subscription/cancel`,
  `/subscription/change-plan`.
