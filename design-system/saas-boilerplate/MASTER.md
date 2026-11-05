# Design System Master — Cobalt Glass

**Direction: A (Cobalt Classic SaaS) + glassmorphism — chosen 2026-11-06**

Cobalt Pro's palette on frosted surfaces: translucent oklch fills + `backdrop-blur(18px) saturate(1.5)`
over a fixed gradient mesh (blue/violet/teal blobs). Applied to dashboards and the public site.

## Glass tokens (defined in shadcn-theme.blade.php, light + dark)

| Token | Light | Dark |
|---|---|---|
| `--glass` | `oklch(1 0 0 / 0.55)` | `oklch(0.21 0.028 254 / 0.55)` |
| `--glass-strong` | `oklch(1 0 0 / 0.72)` | `oklch(0.24 0.03 254 / 0.75)` |
| `--glass-border` | `oklch(1 0 0 / 0.65)` | `oklch(1 0 0 / 0.14)` |
| `--glass-blur` | `blur(18px) saturate(1.5)` | `blur(18px) saturate(1.4)` |
| `--glass-highlight` | `inset 0 1px 0 oklch(1 0 0 / 0.6)` | `inset 0 1px 0 oklch(1 0 0 / 0.1)` |
| `--mesh` | 3 radial gradients (light) | 3 radial gradients (deep) |

## Rules

- Glass on large surfaces only (`.card`, `.topbar`, `.sidebar`, modals); small controls use
  translucent fills without blur for performance.
- `--card`/`--popover`/`--muted`/`--secondary` are translucent versions of the base colors —
  components that don't set backdrop-filter still read as glass-tinted.
- The mesh lives on `body` (`background-image: var(--mesh); background-attachment: fixed`);
  `.page-content` is transparent so it shows through.
- Sidebar branding env vars (`TYRO_DASHBOARD_SIDEBAR_*`) override the glass sidebar ONLY when
  explicitly set in .env.
- Radius: `0.75rem`. Contrast: foreground tokens unchanged from Cobalt Pro (WCAG-safe on the
  55%-white/55%-black surfaces).

---

## Palette (OKLCH)

### Light Mode

| Token                 | OKLCH                      | Approx hex | Contrast (on bg) |
|-----------------------|----------------------------|------------|-----------------|
| `--primary`           | oklch(0.546 0.237 258)     | #2563eb    | 4.82:1 on white ✓ AA |
| `--primary-foreground`| oklch(0.985 0 0)           | #fafafa    | on --primary |
| `--background`        | oklch(0.985 0.003 258)     | #f8f9fb    | — |
| `--foreground`        | oklch(0.144 0.022 254)     | #0f172a    | 17.5:1 ✓ AAA |
| `--card`              | oklch(1 0 0)               | #ffffff    | — |
| `--card-foreground`   | oklch(0.144 0.022 254)     | #0f172a    | 21:1 ✓ AAA |
| `--muted`             | oklch(0.962 0.008 258)     | #f1f5f9    | — |
| `--muted-foreground`  | oklch(0.556 0.015 254)     | #64748b    | 4.53:1 ✓ AA |
| `--accent`            | oklch(0.925 0.045 258)     | #dbeafe    | — |
| `--accent-foreground` | oklch(0.144 0.022 254)     | #0f172a    | ✓ |
| `--border`            | oklch(0.898 0.010 258)     | #e2e8f0    | — |
| `--input`             | oklch(0.898 0.010 258)     | #e2e8f0    | — |
| `--ring`              | oklch(0.546 0.237 258)     | #2563eb    | focus ring = primary |
| `--secondary`         | oklch(0.962 0.008 258)     | #f1f5f9    | — |
| `--secondary-foreground` | oklch(0.144 0.022 254) | #0f172a    | ✓ |
| `--destructive`       | oklch(0.577 0.245 27.325)  | #ef4444    | 4.63:1 ✓ AA |
| `--success`           | oklch(0.627 0.194 149)     | #16a34a    | 4.56:1 ✓ AA |
| `--warning`           | oklch(0.769 0.188 70)      | #ca8a04    | 4.74:1 ✓ AA |
| `--info`              | oklch(0.546 0.237 258)     | #2563eb    | same as primary |

### Dark Mode

| Token                 | OKLCH                      |
|-----------------------|----------------------------|
| `--background`        | oklch(0.144 0.022 254)     |
| `--foreground`        | oklch(0.980 0.005 258)     |
| `--card`              | oklch(0.198 0.020 254)     |
| `--card-foreground`   | oklch(0.980 0.005 258)     |
| `--primary`           | oklch(0.546 0.237 258)     |
| `--primary-foreground`| oklch(0.985 0 0)           |
| `--secondary`         | oklch(0.265 0.015 254)     |
| `--secondary-foreground` | oklch(0.980 0.005 258) |
| `--muted`             | oklch(0.265 0.015 254)     |
| `--muted-foreground`  | oklch(0.698 0.010 254)     |
| `--accent`            | oklch(0.265 0.015 254)     |
| `--accent-foreground` | oklch(0.980 0.005 258)     |
| `--border`            | oklch(1 0 0 / 10%)         |
| `--input`             | oklch(1 0 0 / 15%)         |
| `--ring`              | oklch(0.546 0.237 258)     |
| `--destructive`       | oklch(0.704 0.191 22.216)  |

---

## Spacing & Shape

| Property          | Value           |
|-------------------|-----------------|
| `--radius`        | 0.5rem (8px)    |
| Input height      | 2.75rem (44px)  |
| Base grid         | 4px (0.25rem)   |
| Card padding      | 1.5rem          |
| Table row min-h   | 3rem (48px)     |

## Typography

| Property     | Value                                     |
|--------------|-------------------------------------------|
| `--font-sans`| Inter, ui-sans-serif, system-ui           |
| Base size    | 1rem / 1.5 line-height                    |
| Heading      | 600 semi-bold                             |
| Body         | 400 regular                               |
| Small text   | 0.875rem                                  |

---

## Sidebar Tokens

### Light
| Token                        | OKLCH                      |
|------------------------------|----------------------------|
| `--sidebar`                  | oklch(0.978 0.006 258)     |
| `--sidebar-foreground`       | oklch(0.144 0.022 254)     |
| `--sidebar-primary`          | oklch(0.546 0.237 258)     |
| `--sidebar-primary-foreground` | oklch(0.985 0 0)         |
| `--sidebar-accent`           | oklch(0.925 0.045 258)     |
| `--sidebar-accent-foreground`| oklch(0.144 0.022 254)     |
| `--sidebar-border`           | oklch(0.898 0.010 258)     |
| `--sidebar-ring`             | oklch(0.546 0.237 258)     |

### Dark
| Token                        | OKLCH                      |
|------------------------------|----------------------------|
| `--sidebar`                  | oklch(0.108 0.018 254)     |
| `--sidebar-foreground`       | oklch(0.980 0.005 258)     |
| `--sidebar-primary`          | oklch(0.546 0.237 258)     |
| `--sidebar-primary-foreground` | oklch(0.985 0 0)         |
| `--sidebar-accent`           | oklch(0.198 0.020 254)     |
| `--sidebar-accent-foreground`| oklch(0.980 0.005 258)     |
| `--sidebar-border`           | oklch(1 0 0 / 10%)         |
| `--sidebar-ring`             | oklch(0.546 0.237 258)     |

---

## Chart Colors

### Light
- chart-1: oklch(0.546 0.237 258) — cobalt primary
- chart-2: oklch(0.627 0.194 149) — emerald
- chart-3: oklch(0.769 0.188 70)  — amber
- chart-4: oklch(0.577 0.245 27)  — red
- chart-5: oklch(0.620 0.148 310) — violet

### Dark
- chart-1: oklch(0.620 0.237 258) — lighter cobalt
- chart-2: oklch(0.696 0.170 162) — green
- chart-3: oklch(0.769 0.188 70)  — amber
- chart-4: oklch(0.645 0.246 16)  — red
- chart-5: oklch(0.627 0.265 304) — purple

---

## Page Overrides

### Pricing page
- Plan cards on `oklch(0.985 0.003 258)` background
- Featured plan: inverted — `--primary` bg, `--primary-foreground` text
- CTA button: `variant="primary"`

### Checkout
- Full-bleed white card, no sidebar chrome
- Subtle background: `oklch(0.985 0.003 258)`

### Admin overview
- Tighter row density (40px), chart tokens for metric widgets
- Tables use `--muted` alternating rows via `even:bg-[var(--muted)]`

---

## Component Density

| Component    | Height  | Padding    |
|--------------|---------|------------|
| Button (md)  | 44px    | px-4       |
| Button (sm)  | 32px    | px-3       |
| Input        | 44px    | px-3       |
| Select       | 44px    | px-3       |
| Table row    | 48px    | px-4       |
| Card body    | —       | p-6        |

---

## Status Pill Map

| Subscription status | Badge variant | Label     |
|---------------------|---------------|-----------|
| active              | success       | Active    |
| trialing            | info          | Trial     |
| past_due            | warning       | Past Due  |
| canceled            | danger        | Canceled  |
| paused              | muted         | Paused    |
| incomplete          | warning       | Incomplete|
