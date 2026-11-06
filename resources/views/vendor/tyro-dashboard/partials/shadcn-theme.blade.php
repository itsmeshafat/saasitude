@php
    use HasinHayder\TyroDashboard\Support\DashboardColors;

    $dc = DashboardColors::load();
    $lightOverrides = $dc['light'] ?? [];
    $darkOverrides = $dc['dark'] ?? [];

    $cssVal = function (string $var, string $fallback, array $overrides) {
        if (isset($overrides[$var])) {
            return DashboardColors::hexAlphaToRgba($overrides[$var]['hex'], $overrides[$var]['alpha']);
        }
        return $fallback;
    };
@endphp

<style>
    /* ============================================================
       DESIGN SYSTEM — "HOSTING GROWTH" (adopted 2026-11-06)
       Canonical token source. Mirrored in resources/css/app.css.
       Dark = brief values verbatim (default theme).
       Light = derived from the same hues (deviations logged in
       design-system/saas-boilerplate/MASTER.md).
       Components MUST consume tokens — never raw hex.
    ============================================================= */

    :root {
        /* Radius scale — radius.xs/sm/md/lg/xl */
        --radius-xs: 2px;
        --radius-sm: 8px;
        --radius: 12px;   /* radius.md — base */
        --radius-lg: 16px;
        --radius-xl: 9999px;

        /* Ring shadows (brief shadow.1/2/3 — rings, not elevation) */
        --shadow-1: 0 0 0 1px #082615;   /* surface.muted ring — cards on dark */
        --shadow-2: 0 0 0 1px #ffffff;   /* white ring — elevated surfaces */
        --shadow-3: 0 0 0 0px rgba(59, 130, 246, 0.5); /* blue focus ring base */

        /* Motion tokens — instant/fast/normal */
        --duration-instant: 150ms;
        --duration-fast: 200ms;
        --duration-normal: 300ms;

        /* Spacing scale — space.1..8 (3/4/5/6/8/10/12/16) */
        --space-1: 3px;
        --space-2: 4px;
        --space-3: 5px;
        --space-4: 6px;
        --space-5: 8px;
        --space-6: 10px;
        --space-7: 12px;
        --space-8: 16px;

        /* Typography scale — xs..4xl (base 16px/24px, weight 400) */
        --text-xs: 10px;
        --text-sm: 11px;
        --text-md: 13.01px;
        --text-lg: 14px;
        --text-xl: 15px;
        --text-2xl: 16px;
        --text-3xl: 18px;
        --text-4xl: 20px;
        /* Display steps — DERIVED (doubling pattern) for marketing heroes;
           scale caps at 4xl=20. See MASTER.md "Deviations". */
        --text-display-sm: 24px;
        --text-display-md: 32px;
        --text-display-lg: 48px;

        /* Light mode — DERIVED from the Hosting Growth hues */
        --background: {{ $cssVal('--background', '#ffffff', $lightOverrides) }};
        --foreground: {{ $cssVal('--foreground', '#0a1616', $lightOverrides) }}; /* text.secondary */
        --card: {{ $cssVal('--card', 'rgba(255, 255, 255, 0.6)', $lightOverrides) }};
        --card-foreground: {{ $cssVal('--card-foreground', '#0a1616', $lightOverrides) }};
        --popover: {{ $cssVal('--popover', 'rgba(255, 255, 255, 0.92)', $lightOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', '#0a1616', $lightOverrides) }};
        --primary: {{ $cssVal('--primary', '#4d7c0f', $lightOverrides) }}; /* DERIVED dark-lime (AA 4.7:1 on white); brief lime kept for dark */
        --primary-foreground: {{ $cssVal('--primary-foreground', '#ffffff', $lightOverrides) }};
        --secondary: {{ $cssVal('--secondary', 'rgba(8, 38, 21, 0.06)', $lightOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', '#0a1616', $lightOverrides) }};
        --muted: {{ $cssVal('--muted', 'rgba(8, 38, 21, 0.05)', $lightOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', '#52645c', $lightOverrides) }}; /* DERIVED: brief #9ba5a1 fails AA on white */
        --accent: {{ $cssVal('--accent', 'rgba(198, 251, 75, 0.16)', $lightOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', '#0a1616', $lightOverrides) }};
        --destructive: {{ $cssVal('--destructive', '#dc2626', $lightOverrides) }}; /* extension */
        --destructive-foreground: {{ $cssVal('--destructive-foreground', '#ffffff', $lightOverrides) }};
        --border: {{ $cssVal('--border', '#e5e7eb', $lightOverrides) }}; /* border.default */
        --input: {{ $cssVal('--input', '#e5e7eb', $lightOverrides) }};
        --ring: {{ $cssVal('--ring', '#2563eb', $lightOverrides) }}; /* DERIVED solid (50% alpha invisible on white) */

        /* Glass system — light: white frost over lime-tinted mesh */
        --glass: rgba(255, 255, 255, 0.6);
        --glass-strong: rgba(255, 255, 255, 0.78);
        --glass-border: rgba(229, 231, 235, 0.9);
        --glass-blur: blur(18px) saturate(1.4);
        --glass-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        --mesh:
            radial-gradient(600px 420px at 12% -6%, rgba(198, 251, 75, 0.16), transparent 65%),
            radial-gradient(760px 520px at 96% 12%, rgba(8, 38, 21, 0.07), transparent 62%),
            radial-gradient(680px 540px at 42% 108%, rgba(45, 90, 63, 0.10), transparent 60%);

        /* Chart colors — from the palette */
        --chart-1: #4d7c0f; /* DERIVED: lime stroke unreadable on white */
        --chart-2: #0a1616;
        --chart-3: #52645c;
        --chart-4: #2d5a3f;
        --chart-5: #3b82f6;

        /* Sidebar — light */
        --sidebar: rgba(255, 255, 255, 0.66);
        --sidebar-foreground: #0a1616;
        --sidebar-primary: #c6fb4b;
        --sidebar-primary-foreground: #0a1616;
        --sidebar-accent: rgba(198, 251, 75, 0.18);
        --sidebar-accent-foreground: #0a1616;
        --sidebar-border: rgba(10, 22, 22, 0.1);
        --sidebar-ring: rgba(59, 130, 246, 0.5);

        /* Extended semantic (brief-silent extensions, logged) */
        --success: {{ $cssVal('--success', '#4d7c0f', $lightOverrides) }}; /* DERIVED dark-lime for AA */
        --success-foreground: {{ $cssVal('--success-foreground', '#ffffff', $lightOverrides) }};
        --warning: {{ $cssVal('--warning', '#b45309', $lightOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', '#ffffff', $lightOverrides) }};
        --info: {{ $cssVal('--info', '#2563eb', $lightOverrides) }}; /* DERIVED: #3b82f6 is 3.68:1 on white — below AA */
        --info-foreground: {{ $cssVal('--info-foreground', '#ffffff', $lightOverrides) }};
        --danger: {{ $cssVal('--danger', '#dc2626', $lightOverrides) }};

        /* Card shadows — ring treatment */
        --card-shadow: 0 0 0 1px rgba(10, 22, 22, 0.08), var(--glass-highlight);
        --card-shadow-hover: 0 0 0 1px rgba(10, 22, 22, 0.14), var(--glass-highlight);
    }

    /* Dark mode — DEFAULT THEME, brief values verbatim */
    .dark {
        --background: {{ $cssVal('--background', '#000000', $darkOverrides) }}; /* surface.base */
        --foreground: {{ $cssVal('--foreground', '#ffffff', $darkOverrides) }}; /* text.tertiary */
        --card: {{ $cssVal('--card', 'rgba(8, 38, 21, 0.55)', $darkOverrides) }}; /* surface.muted */
        --card-foreground: {{ $cssVal('--card-foreground', '#ffffff', $darkOverrides) }};
        --popover: {{ $cssVal('--popover', 'rgba(8, 38, 21, 0.92)', $darkOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', '#ffffff', $darkOverrides) }};
        --primary: {{ $cssVal('--primary', '#c6fb4b', $darkOverrides) }}; /* surface.strong */
        --primary-foreground: {{ $cssVal('--primary-foreground', '#0a1616', $darkOverrides) }}; /* dark text on lime (AA ≈13:1) */
        --secondary: {{ $cssVal('--secondary', 'rgba(8, 38, 21, 0.6)', $darkOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', '#ffffff', $darkOverrides) }};
        --muted: {{ $cssVal('--muted', 'rgba(8, 38, 21, 0.5)', $darkOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', '#9ba5a1', $darkOverrides) }}; /* text.inverse (≈7:1 on black) */
        --accent: {{ $cssVal('--accent', 'rgba(198, 251, 75, 0.1)', $darkOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', '#ffffff', $darkOverrides) }};
        --destructive: {{ $cssVal('--destructive', '#f87171', $darkOverrides) }}; /* extension */
        --destructive-foreground: {{ $cssVal('--destructive-foreground', '#0a1616', $darkOverrides) }};
        --border: {{ $cssVal('--border', 'rgba(229, 231, 235, 0.15)', $darkOverrides) }}; /* border.default @15% */
        --input: {{ $cssVal('--input', 'rgba(229, 231, 235, 0.18)', $darkOverrides) }};
        --ring: {{ $cssVal('--ring', 'rgba(59, 130, 246, 0.5)', $darkOverrides) }}; /* shadow.3 */

        /* Glass system — dark: green frost over lime-tinted mesh on black */
        --glass: rgba(8, 38, 21, 0.55);
        --glass-strong: rgba(8, 38, 21, 0.78);
        --glass-border: rgba(229, 231, 235, 0.2);
        --glass-blur: blur(18px) saturate(1.3);
        --glass-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        --mesh:
            radial-gradient(600px 420px at 12% -6%, rgba(198, 251, 75, 0.1), transparent 65%),
            radial-gradient(760px 520px at 96% 12%, rgba(8, 38, 21, 0.55), transparent 62%),
            radial-gradient(680px 540px at 42% 108%, rgba(45, 90, 63, 0.35), transparent 60%);

        /* Chart colors (dark) — from the palette */
        --chart-1: #c6fb4b;
        --chart-2: #ffffff;
        --chart-3: #9ba5a1;
        --chart-4: #2d5a3f;
        --chart-5: #3b82f6;

        /* Sidebar — dark green frost */
        --sidebar: rgba(8, 38, 21, 0.6);
        --sidebar-foreground: #ffffff;
        --sidebar-primary: #c6fb4b;
        --sidebar-primary-foreground: #0a1616;
        --sidebar-accent: rgba(198, 251, 75, 0.12);
        --sidebar-accent-foreground: #ffffff;
        --sidebar-border: rgba(229, 231, 235, 0.12);
        --sidebar-ring: rgba(59, 130, 246, 0.5);

        /* Extensions */
        --danger: {{ $cssVal('--danger', '#f87171', $darkOverrides) }};
        --success: {{ $cssVal('--success', '#c6fb4b', $darkOverrides) }};
        --success-foreground: {{ $cssVal('--success-foreground', '#0a1616', $darkOverrides) }};
        --warning: {{ $cssVal('--warning', '#fbbf24', $darkOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', '#0a1616', $darkOverrides) }};
        --info: {{ $cssVal('--info', '#3b82f6', $darkOverrides) }};
        --info-foreground: {{ $cssVal('--info-foreground', '#ffffff', $darkOverrides) }};

        /* Card shadows — ring treatment (shadow.1) */
        --card-shadow: 0 0 0 1px rgba(8, 38, 21, 0.9), var(--glass-highlight);
        --card-shadow-hover: 0 0 0 1px rgba(229, 231, 235, 0.25), var(--glass-highlight);
    }
</style>
