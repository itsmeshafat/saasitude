# Design System Master — Hosting Growth

**Direction: "Web Hosting Built to Grow With You" token system — adopted 2026-11-07.**
Dark default (black + lime), glassmorphism retained (recolored), derived light mode.
**Implementation rule: content never changed — tokens, components, docs did.**

---

## 1. Context and goals

Design intent: a **clean, functional, implementation-oriented** billing SaaS on a token-driven
foundation optimized for consistency, WCAG 2.2 AA accessibility, keyboard-first interaction,
and fast delivery across the dashboard and marketing site.

Goals: semantic-token-only styling; every component carries the 8 mandatory states
(default, hover, focus-visible, active, disabled, loading, error, + responsive); every
accessibility rule testable; zero one-off spacing/typography exceptions.

Component density in this codebase: links (83), cards (31), buttons (28), lists (20),
navigation (3 — sidebar, topbar, tabs), inputs (2 + `.form-*` family).

---

## 2. Design tokens and foundations

### 2.1 Color — dark (DEFAULT, brief values verbatim)

| Token | Value | Role |
|---|---|---|
| `--background` | `#000000` | surface.base |
| `--foreground` | `#ffffff` | text.tertiary |
| `--card` / `--glass` | `rgba(8,38,21,.55)` | surface.muted (frosted) |
| `--glass-strong` / `--popover` | `rgba(8,38,21,.78/.92)` | elevated surface.muted |
| `--primary` | `#c6fb4b` | surface.strong (accent) |
| `--primary-foreground` | `#0a1616` | dark text on lime |
| `--muted-foreground` | `#9ba5a1` | text.inverse |
| `--border` / `--input` | `rgba(229,231,235,.15/.18)` | border.default adapted |
| `--ring` | `rgba(59,130,246,.5)` | shadow.3 (focus) |
| `--chart-1..5` | `#c6fb4b #ffffff #9ba5a1 #2d5a3f #3b82f6` | palette-derived |

### 2.2 Color — light (DERIVED; deviations logged)

`--background #ffffff` · `--foreground #0a1616` (text.secondary) · glass = white 60–78% ·
`--border #e5e7eb` (border.default solid) · primary/lime unchanged.

**Logged deviations (brief is silent or fails AA):**
- Light `--primary`/`--success`: `#4d7c0f` (dark lime) with white foreground — the brief's lime `#c6fb4b` is 1.22:1 on white and unreadable anywhere it appears as text (links, eyebrows, stat-changes, badges). Dark lime keeps the brand hue at 4.99:1 AA. Dark mode keeps lime verbatim (fills + text both pass).
- Light `--ring`/`--info`: `#2563eb` solid — the brief's 50%-alpha blue ring and `#3b82f6` (3.68:1) are below AA/visibility on white.
- Light `--muted-foreground: #52645c` — derived from text.inverse; brief's `#9ba5a1` fails AA on white (measured 6.29:1 ✓).
- `--destructive` (red) / `--warning` (amber) retained — required for payment/health states; brief has no equivalents.
- Display type steps (24/32/48px) — derived by doubling the type scale; the brief's scale caps at 4xl=20 and cannot cover marketing heroes.

**Light-mode contrast measurements (computed, 2026-11-07):** body 18.43:1 · muted 6.29:1 · primary text 4.99:1 · primary button 4.99:1 · table body 18.43:1 · table head 6.29:1 · public eyebrow/logo 4.99:1 — all ≥ 4.5 AA.

### 2.3 Typography (font tokens verbatim)

- **Stack**: `Aeonik, 'Segoe UI', 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, system-ui`. Aeonik is commercial — if licensed, it activates automatically; otherwise Segoe UI leads the fallbacks. Inter/bunny links removed from all layouts.
- **Scale**: xs 10 · sm 11 · md 13.01 · lg 14 · xl 15 · 2xl 16 · 3xl 18 · 4xl 20 (base 16/24, weight 400). Tokens `--text-xs…4xl`.
- **Applied to** (enforced in `styles.blade.php` / `public.css`):

| Surface | Token |
|---|---|
| body | base 16/24 |
| `.page-title`, `.stat-value`, public h1 (display-lg 48, derived) | 4xl |
| `.card-title` | 3xl 18 |
| `.btn`, `.tab-link` | xl 15 / lg 14 |
| `.table` body, `.stat-change` | md 13.01 |
| `.form-label` (uppercase, +0.05em), `.filter-label`, `.form-hint` | sm 11 |
| `.badge`, timestamps, `.notif-item-time` | xs 10 |

### 2.4 Spacing scale (space.1..8 = 3/4/5/6/8/10/12/16px)

Mapped to the app: `.card-body` padding **16 (space-8)** · `.card-header` **12/16** ·
`.stats-grid` gap **12 (space-7)** · `.form-group` margin **16** · `.page-content` padding
**16** · card stacks **mb-4/16** · in-card groups **gap-3/12 or space-y-3**.
Tailwind normalization: card stacks `mb-4` (not mb-6), grids `gap-4`, in-card `gap-3`.
**Off-scale spacing is prohibited** in new code.

### 2.5 Radius / Shadows / Motion

- Radius: `xs 2` · `sm 8` · `md 12` (base `--radius`) · `lg 16` · `xl 9999` (pills/badges).
- Shadows are **rings**, not elevation: `--shadow-1` (`#082615` 1px — cards on dark), `--shadow-2` (white 1px — elevated), `--shadow-3` (blue 50% — focus). `.card` uses a soft ring variant of shadow-1; glass-highlight retained.
- Motion: instant 150ms (buttons/links/hover), fast 200ms, normal 300ms (sidebar/layout) — tokens `--duration-*` wired into `.btn`, `.card`, `.sidebar`, dropdowns; `prefers-reduced-motion` honored (spinners/transitions clamped).

### 2.6 Glassmorphism (retained, recolored)

`--glass/--glass-strong` = surface.muted frosted (dark: green 55–78%; light: white 60–78%) ·
`--glass-border` border.default @ 20% (dark) / solid (light) · `--mesh` lime `.10` + deep-green
blobs on black (light: lime `.16` + subtle green). Same blur (18px).

---

## 3. Component rules (anatomy · variants · states · responsive)

**Mandatory states for every interactive component**: default, hover, focus-visible, active,
disabled, loading, error (loading/error N/A → documented).

### Buttons (28 → `x-ui.button`, `.btn*` classes)
- Variants: primary (lime, the ONLY choice for the main action), secondary (bordered), outline, ghost, danger (destructive-only), link. Sizes: sm/md/lg (`btn-sm/btn-lg`).
- **States**: hover (token transition 150ms), focus-visible (2px ring, offset 2px — global), active (pressed via `.btn` CSS), disabled (50% + no pointer), **loading** (`loading` prop → spinner + `aria-busy` + disabled), error (`.btn-error` hook).
- Keyboard: Enter/Space submit; Tab reachable. Pointer/touch: min-height 36–44px.
- Long labels: `white-space:nowrap` inside flex; labels must stay ≤ 3 words (tone rule).
- MUST NOT: two primary buttons in one view section; buttons without variant.

### Links (83 → sidebar-link, tab-link, breadcrumbs, table links, btn-link-btn)
- Hover: color swap (muted→foreground) or accent bg; focus-visible ring; active = current route (`routeIs` wildcard); underline on hover only.
- Truncation: sidebar/table link labels truncate with ellipsis; full text via `title` attr.

### Cards (31 → `.card`, stat-card, plan-card, chart panels)
- Anatomy: `.card-header` (12/16) + `.card-body` (16). Ring shadow-1 + glass. Radius md.
- States: hover ring-2 (card-shadow-hover); loading = `.skeleton` shimmer blocks; empty = `x-ui.empty-state`; error = inline `.alert-error`.
- Overflow: long numbers use tabular figures; content scrolls in `.table-container` only.

### Lists/tables (20 → `.table` in `.table-container`)
- Header: xs 10, uppercase, tracked. Rows: md 13.01. Zebra optional (`table-striped`).
- Long content: truncate + `title`; empty: `x-ui.empty-state`; loading: 3× `.skeleton` rows; actions right-aligned (`.action-buttons`).

### Navigation (3 → sidebar, topbar+bell, tabs)
- Active: routeIs wildcard; hover: accent; focus-visible ring. Mobile: sidebar collapses, topbar wraps.
- Bell: polls 30s; Esc closes, ArrowUp/Down traverse items, Enter opens; badge caps at 99+.

### Inputs (2 → `x-ui.input`/`x-ui.select` + `.form-*` family)
- `.form-label` sm 11 uppercase; `.form-input/.form-select` glass; `.form-error` destructive; disabled 50%.
- All inputs: Tab reachable, labels associated (`for`/`id`), autocomplete where applicable.

### Extras (specified beyond the brief's count)
Badges (`.badge*` — xs 10, pill), status-pill (auto-maps subscription status), usage-meter (amber 70% / red 90% thresholds), charts (lime `--chart-1` stroke, donut legend space-between, bars per-cell value+label), pagination (`.pagination`), modals/`showDanger` (destructive-only, always confirm), toasts, `.alert-error` (never `alert-danger`).

---

## 4. Accessibility — testable acceptance criteria

| # | Criterion | Pass condition |
|---|---|---|
| A1 | Body contrast (dark) | `#fff`/`#000` = 21:1 ≥ 4.5 ✓ |
| A2 | Primary pair (dark) | `#0a1616`/`#c6fb4b` ≈ 13:1 ≥ 4.5 ✓ |
| A3 | Muted text (dark) | `#9ba5a1`/`#000` ≈ 7:1 ≥ 4.5 ✓ |
| A4 | Muted text (light) | `#52645c`/`#fff` ≈ 6.3:1 ≥ 4.5 ✓ |
| A5 | Focus visibility | 100% of interactive elements show ring on `:focus-visible` (keyboard tab pass) |
| A6 | Keyboard completeness | Every flow (billing, settings, support) operable with keyboard only |
| A7 | Reduced motion | `prefers-reduced-motion` clamps transitions/spinners |
| A8 | Label association | Every input has `for`/`id` or aria-label |

---

## 5. Content and tone standards

Content (pages, copy, features) is unchanged by this adoption. For future copy: concise,
confident, implementation-focused. Buttons are verb phrases ≤ 3 words ("Save Stripe",
"Retry checkout", "View plans"); empty states offer exactly one CTA; errors state the fix.

---

## 6. Anti-patterns and prohibited implementations

- MUST NOT use raw hex/oklch in component markup — tokens only.
- MUST NOT use off-scale spacing (e.g. `mb-6`) or one-off type sizes — the scale is exclusive.
- MUST NOT hide or remove focus indicators.
- MUST NOT ship a component state without default/hover/focus-visible/disabled at minimum.
- MUST NOT use `.alert-danger` (undefined) — `.alert-error`.
- MUST NOT introduce local visual exceptions over system consistency.

---

## 7. Migration notes (Cobalt Glass → Hosting Growth)

- Token NAMES unchanged (`--primary`, `--glass*`, `--mesh`…) — only values swapped; no markup churn.
- Glassmorphism retained: glass surfaces now surface.muted frost; mesh recolored lime/green.
- Dark became the default theme; the toggle still switches; light mode is derived (see deviations).
- Inter/bunny font links removed; Aeonik stack is system-first. If the Aeonik license is purchased, serve it via `@font-face` in `app.css` and the stack activates.
- Charts/badges/buttons auto-retheme via tokens — no per-component changes were needed for color.

---

## 8. QA checklist (run before shipping UI work)

- [ ] Dark default renders: bg `#000`, primary `#c6fb4b`, glass green-frosted
- [ ] Light mode derived correctly (toggle works, muted ≥4.5:1)
- [ ] All buttons single-line, correct variant, loading/disabled states where applicable
- [ ] Keyboard: tab through every flow; ring visible; bell Esc/arrow navigation
- [ ] Contrast: run A1–A4 ratios on any new token pair
- [ ] Spacing: no off-scale values; card stacks `mb-4`; grids `gap-4`
- [ ] Typography: no non-scale font sizes in new code
- [ ] Empty/loading/error states present on every data surface
- [ ] `prefers-reduced-motion` honored
- [ ] Suite green; `npm run build`; `view:cache`; Pint clean
