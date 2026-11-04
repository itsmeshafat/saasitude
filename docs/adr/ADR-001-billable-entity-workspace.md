# ADR-001: Billable entity is Workspace, not User

**Status:** Accepted  
**Date:** 2026-11-04

## Context

A SaaS product must decide what entity owns a subscription. The two common choices are User (simple, common for consumer tools) and Organisation/Workspace (standard for B2B/team tools). Our boilerplate must work for both solo users and teams without a rewrite.

Tyro provides global staff RBAC (`super-admin`, `admin`, `support`, `user`). We also need per-workspace member roles (`owner`, `admin`, `member`, `billing`).

## Decision

**Workspace is the billable entity.** Every user gets a personal workspace auto-created on registration. Plans, subscriptions, entitlements, usage records, API tokens, and all billing data are owned by the workspace, not the user.

Tyro's global roles remain unchanged — they govern platform staff access only. Workspace member roles are stored in our own `workspace_members` table with an enum column (`owner`, `admin`, `member`, `billing`) and do not touch Tyro's tables.

## Rationale (industry standard)

Every major B2B SaaS (Slack, Linear, Notion, GitHub, Vercel, Fly.io) bills at the organisation level. Per-user billing at the User model level causes cascading problems: switching teams breaks subscription state, usage metering becomes per-user instead of per-team, and moving to team billing later requires a migration of all billing records. Starting at the Workspace level costs nothing extra for solo users (personal workspace is transparent) and handles teams without any structural change.

Mixing Tyro's platform RBAC with per-workspace RBAC into one system would corrupt the platform role semantics and would require forking Tyro's migration files. Keeping them in a separate `workspace_members` table is the clean boundary.

## Consequences

- Every new user triggers a `WorkspaceCreated` event and an auto-created personal workspace (name = user display name, `is_personal = true`).
- Entitlement checks always receive a Workspace, never a User.
- Workspace deletion must cascade correctly (subscriptions, payments, members — cascaded at DB level via foreign keys + `ON DELETE CASCADE`).
- Ownership transfer is a privileged action: only the current owner can transfer to another workspace member.

## Alternatives considered

**User as billable entity** — rejected: breaks team billing without structural rewrite; entitlement checks require joining through team membership.

**Shared Tyro RBAC for workspace roles** — rejected: Tyro's role system is global; bending it into per-workspace roles would require forking migrations and creating per-workspace role rows, making the platform-staff boundary invisible.
