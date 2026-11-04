# ADR-004: UI stack = Blade + Tailwind + Alpine via Tyro Dashboard

**Status:** Accepted  
**Date:** 2026-11-04

## Context

The project already has Tyro Dashboard installed (v1.60.0), which provides a full admin/user dashboard shell, layouts, CRUD scaffolding (`HasCrud`, `make-resource`), shadcn CSS variable theming, and 115 built-in routes. Choosing a different UI stack would abandon this investment.

## Decision

All dashboard and billing pages are built in **Blade using Tyro Dashboard layouts** (`tyro-dashboard::layouts.user`, `tyro-dashboard::layouts.admin`, `tyro-dashboard::layouts.app`). Pages are scaffolded with `tyro-dashboard:create-admin-page`, `create-user-page`, `create-common-page`. Simple resources use `HasCrud`. Complex flows (checkout, billing portal) use custom Blade components.

Custom colours and typography land exclusively in `shadcn-theme.blade.php` (published via `tyro-dashboard:publish-style --theme-only`). No hard-coded hex values in Blade views.

**No Inertia, React, or Livewire** without a new ADR. Alpine.js (already bundled with Tyro Dashboard) is acceptable for lightweight interactivity (tabs, toggles, dropdowns). Heavy client-side state goes to a dedicated `/api/v1` call + Alpine fetch.

## Rationale (industry standard)

For server-rendered SaaS dashboards (Plausible, Fathom, older Basecamp, most Laravel indie products), Blade + Alpine is significantly lower complexity than a React SPA while still achieving full WCAG compliance and fast TTFB. Tyro Dashboard provides ~80% of the admin scaffold work for free. Introducing React or Livewire adds build complexity, component library decisions, hydration edge cases, and test surface without commensurate benefit for a dashboard-heavy product.

## Consequences

- Design tokens must be managed through the shadcn variable system — see `docs/07-design-system.md`.
- Phase 6.5 (design system) must run before Phase 7 (admin pages) to get tokens into `shadcn-theme.blade.php`.
- For public/marketing pages (landing, pricing, blog): same Blade stack. Tailwind utility classes for layout. No separate framework.
- Mobile API (`/api/v1`) is JSON-only — no Blade involvement.

## Alternatives considered

**Livewire** — rejected at this time: adds Livewire-specific testing patterns, the team is not currently using it, and Tyro Dashboard does not build on Livewire. Could be revisited if real-time UI requirements emerge.

**Inertia + React** — rejected: the reference SaaS repo uses this stack but it's a different architectural choice. Our stack is Blade-first. The complexity budget should go to billing correctness, not frontend framework management.

**HTMX** — not considered: too experimental for a boilerplate meant to serve as a production foundation.
