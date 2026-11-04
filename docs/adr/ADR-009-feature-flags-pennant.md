# ADR-009: Feature flags via Laravel Pennant (P2, deferred)

**Status:** Deferred — implement in Phase 14  
**Date:** 2026-11-04

## Context

A SaaS boilerplate needs feature flags for: gradual rollout to workspaces, A/B testing, beta feature access control, and emergency kill switches. Building a custom flag system is unnecessary work when Laravel Pennant exists.

## Decision

Use `laravel/pennant` with its **database driver** for all feature flags. Per-workspace rollout is the primary use case (not per-user, which is Pennant's default).

```php
// Define with workspace scope
Feature::define('new_billing_ui', fn (Workspace $workspace) =>
    $workspace->hasEarlyAccess() || $workspace->plan_key === 'enterprise'
);

// Check
Feature::for($workspace)->active('new_billing_ui');
```

The `feature_flags` table (see `docs/03-data-model.md`) backs Pennant's database store. Per-workspace overrides live in `feature_flag_workspace` (enabled/disabled explicitly for specific workspaces, overriding the computed default).

## Rationale

Pennant is Laravel-native, requires zero external dependencies, and supports custom scope types. The workspace-as-scope pattern diverges from Pennant's User default but is explicitly supported — just pass a Workspace model to `Feature::for()`. The database driver is preferred over the array driver for production (flags persist across deployments and can be toggled from the admin UI).

## Open question at build time

VERIFY: Does Pennant's cached resolution work correctly when the scope is a Workspace (not a User)? Specifically: does `Feature::for($workspace)->flushCache()` scope correctly to the workspace, or does it flush all users? If there's a limitation, record the workaround as an ADR addendum.

## Consequences

- `laravel/pennant` must be installed when Phase 14 begins.
- An admin UI screen to toggle flags per workspace and globally (Phase 14, `docs/05-screens.md`).
- Flags are audited: `feature_flags` model uses `HasAuditTrail` to record who toggled what.
- MVP (Phases 0–13) uses no feature flags — entitlements and plans serve all gating needs.

## Alternatives considered

**Custom flag table** — rejected: Pennant already exists and is maintained by the Laravel team.

**LaunchDarkly / Flagsmith** — rejected: external dependency, cost, and operational overhead for a self-hosted boilerplate. Acceptable as a P2 enhancement if the client needs advanced targeting.
