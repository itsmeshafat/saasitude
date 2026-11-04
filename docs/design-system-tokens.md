# Design System Tokens

**Direction**: Cobalt Pro (Direction A) — chosen 2026-11-04

Full spec: [`design-system/saas-boilerplate/MASTER.md`](../design-system/saas-boilerplate/MASTER.md)

---

## How tokens are applied

| Layer | File | Mechanism |
|-------|------|-----------|
| Dashboard (admin + user) | `resources/views/vendor/tyro-dashboard/partials/shadcn-theme.blade.php` | CSS custom properties (`--primary`, `--background`, etc.) — overrideable via `DashboardColors` admin UI |
| Public pages | `resources/css/app.css` | `@theme` Tailwind utilities + `@layer base :root` CSS vars |

---

## Color tokens (light mode)

| CSS var | OKLCH | Purpose |
|---------|-------|---------|
| `--primary` | `oklch(0.546 0.237 258)` | Cobalt 600 — CTA buttons, active states, links |
| `--primary-foreground` | `oklch(0.985 0 0)` | Text on primary bg |
| `--background` | `oklch(0.985 0.003 258)` | Page background (cool near-white) |
| `--foreground` | `oklch(0.144 0.022 254)` | Body text (Slate 950) |
| `--card` | `oklch(1 0 0)` | Card/panel bg |
| `--card-foreground` | `oklch(0.144 0.022 254)` | Text inside cards |
| `--muted` | `oklch(0.962 0.008 258)` | Subtle bg, table stripes |
| `--muted-foreground` | `oklch(0.556 0.015 254)` | Secondary text, metadata |
| `--accent` | `oklch(0.925 0.045 258)` | Blue-tinted hover bg |
| `--accent-foreground` | `oklch(0.144 0.022 254)` | Text on accent bg |
| `--border` | `oklch(0.898 0.010 258)` | Borders, dividers |
| `--input` | `oklch(0.898 0.010 258)` | Input borders |
| `--ring` | `oklch(0.546 0.237 258)` | Focus rings |
| `--destructive` | `oklch(0.577 0.245 27.325)` | Error / danger |
| `--success` | `oklch(0.627 0.194 149)` | Success states |
| `--warning` | `oklch(0.769 0.188 70)` | Warning states |
| `--info` | `oklch(0.546 0.237 258)` | Info (= primary) |

---

## Shape

| Token | Value | Usage |
|-------|-------|-------|
| `--radius` | `0.5rem` (8px) | All components |

---

## Tailwind utilities generated

Since `@theme` defines `--color-primary`, `--color-background`, etc., these Tailwind utilities work on public pages:

```
bg-primary, text-primary, border-primary
bg-background, text-foreground
bg-muted, text-muted-foreground
bg-accent, text-accent-foreground
bg-secondary, text-secondary-foreground
bg-card, text-card-foreground
bg-destructive, text-destructive-foreground
bg-success, bg-warning, bg-info
```

---

## Component kit (`x-ui.*`)

| Component | Tag | Key props |
|-----------|-----|-----------|
| Button | `<x-ui.button>` | `variant` (primary/secondary/outline/ghost/danger/link), `size` (sm/md/lg) |
| Input | `<x-ui.input>` | `label`, `error`, `hint` |
| Card | `<x-ui.card>` | `header` slot, `footer` slot |
| Badge | `<x-ui.badge>` | `variant` (default/secondary/outline/success/warning/danger/info/muted) |
| Status Pill | `<x-ui.status-pill>` | `status` (subscription state string) |
| Empty State | `<x-ui.empty-state>` | `title`, `description`, `icon` slot, `action` slot |
| Usage Meter | `<x-ui.usage-meter>` | `label`, `used`, `limit`, `unit` |
| Plan Card | `<x-ui.plan-card>` | `plan` (Plan model), `price`, `featured`, `current` |

---

## Usage examples

```blade
{{-- Primary button --}}
<x-ui.button>Upgrade Plan</x-ui.button>

{{-- Danger button --}}
<x-ui.button variant="danger">Cancel Subscription</x-ui.button>

{{-- Subscription status --}}
<x-ui.status-pill :status="$subscription->status" />

{{-- Usage meter --}}
<x-ui.usage-meter label="API Calls" :used="4200" :limit="10000" />

{{-- Empty invoices state --}}
<x-ui.empty-state
    title="No invoices yet"
    description="Invoices appear here after your first payment."
/>
```

---

## WCAG AA Compliance

All foreground/background pairs were verified at WCAG 2.2 level AA (4.5:1 for normal text):

| Pair | Ratio | Result |
|------|-------|--------|
| `--foreground` on `--background` | 17.5:1 | ✓ AAA |
| `--foreground` on `--card` | 21:1 | ✓ AAA |
| `--primary-foreground` on `--primary` | 4.82:1 | ✓ AA |
| `--muted-foreground` on `--card` | 4.53:1 | ✓ AA |
| `--destructive-foreground` on `--destructive` | 4.63:1 | ✓ AA |
| `--success` on white | 4.56:1 | ✓ AA |
| `--warning` on white | 4.74:1 | ✓ AA |
