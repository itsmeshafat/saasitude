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
    /* ============================================
       SHADCN UI THEME VARIABLES
       Customize these variables to match your brand
       Compatible with shadcn/ui theming
    ============================================ */

    :root {
        /* Base radius — Cobalt Pro: 8px */
        --radius: 0.5rem;

        /* Light mode colors — Cobalt Pro */
        --background: {{ $cssVal('--background', 'oklch(0.985 0.003 258)', $lightOverrides) }};
        --foreground: {{ $cssVal('--foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --card: {{ $cssVal('--card', 'oklch(1 0 0)', $lightOverrides) }};
        --card-foreground: {{ $cssVal('--card-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --popover: {{ $cssVal('--popover', 'oklch(1 0 0)', $lightOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --primary: {{ $cssVal('--primary', 'oklch(0.546 0.237 258)', $lightOverrides) }};
        --primary-foreground: {{ $cssVal('--primary-foreground', 'oklch(0.985 0 0)', $lightOverrides) }};
        --secondary: {{ $cssVal('--secondary', 'oklch(0.962 0.008 258)', $lightOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --muted: {{ $cssVal('--muted', 'oklch(0.962 0.008 258)', $lightOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', 'oklch(0.556 0.015 254)', $lightOverrides) }};
        --accent: {{ $cssVal('--accent', 'oklch(0.925 0.045 258)', $lightOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --destructive: {{ $cssVal('--destructive', 'oklch(0.577 0.245 27.325)', $lightOverrides) }};
        --destructive-foreground: {{ $cssVal('--destructive-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --border: {{ $cssVal('--border', 'oklch(0.898 0.010 258)', $lightOverrides) }};
        --input: {{ $cssVal('--input', 'oklch(0.898 0.010 258)', $lightOverrides) }};
        --ring: {{ $cssVal('--ring', 'oklch(0.546 0.237 258)', $lightOverrides) }};

        /* Chart colors — Cobalt Pro palette */
        --chart-1: oklch(0.546 0.237 258);
        --chart-2: oklch(0.627 0.194 149);
        --chart-3: oklch(0.769 0.188 70);
        --chart-4: oklch(0.577 0.245 27);
        --chart-5: oklch(0.620 0.148 310);

        /* Sidebar colors */
        --sidebar: oklch(0.978 0.006 258);
        --sidebar-foreground: oklch(0.144 0.022 254);
        --sidebar-primary: oklch(0.546 0.237 258);
        --sidebar-primary-foreground: oklch(0.985 0 0);
        --sidebar-accent: oklch(0.925 0.045 258);
        --sidebar-accent-foreground: oklch(0.144 0.022 254);
        --sidebar-border: oklch(0.898 0.010 258);
        --sidebar-ring: oklch(0.546 0.237 258);

        /* Extended semantic colors */
        --success: {{ $cssVal('--success', 'oklch(0.627 0.194 149)', $lightOverrides) }};
        --success-foreground: {{ $cssVal('--success-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --warning: {{ $cssVal('--warning', 'oklch(0.769 0.188 70)', $lightOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --info: {{ $cssVal('--info', 'oklch(0.546 0.237 258)', $lightOverrides) }};
        --info-foreground: {{ $cssVal('--info-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --danger: {{ $cssVal('--danger', 'oklch(0.577 0.245 27.325)', $lightOverrides) }};

        /* Card shadows */
        --card-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --card-shadow-hover: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }

    /* Dark mode colors - using .dark class */
    .dark {
        --background: {{ $cssVal('--background', 'oklch(0.144 0.022 254)', $darkOverrides) }};
        --foreground: {{ $cssVal('--foreground', 'oklch(0.980 0.005 258)', $darkOverrides) }};
        --card: {{ $cssVal('--card', 'oklch(0.198 0.020 254)', $darkOverrides) }};
        --card-foreground: {{ $cssVal('--card-foreground', 'oklch(0.980 0.005 258)', $darkOverrides) }};
        --popover: {{ $cssVal('--popover', 'oklch(0.198 0.020 254)', $darkOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', 'oklch(0.980 0.005 258)', $darkOverrides) }};
        --primary: {{ $cssVal('--primary', 'oklch(0.546 0.237 258)', $darkOverrides) }};
        --primary-foreground: {{ $cssVal('--primary-foreground', 'oklch(0.985 0 0)', $darkOverrides) }};
        --secondary: {{ $cssVal('--secondary', 'oklch(0.265 0.015 254)', $darkOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', 'oklch(0.980 0.005 258)', $darkOverrides) }};
        --muted: {{ $cssVal('--muted', 'oklch(0.265 0.015 254)', $darkOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', 'oklch(0.698 0.010 254)', $darkOverrides) }};
        --accent: {{ $cssVal('--accent', 'oklch(0.265 0.015 254)', $darkOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', 'oklch(0.980 0.005 258)', $darkOverrides) }};
        --destructive: {{ $cssVal('--destructive', 'oklch(0.704 0.191 22.216)', $darkOverrides) }};
        --destructive-foreground: {{ $cssVal('--destructive-foreground', 'oklch(0.985 0 0)', $darkOverrides) }};
        --border: {{ $cssVal('--border', 'oklch(1 0 0 / 10%)', $darkOverrides) }};
        --input: {{ $cssVal('--input', 'oklch(1 0 0 / 15%)', $darkOverrides) }};
        --ring: {{ $cssVal('--ring', 'oklch(0.546 0.237 258)', $darkOverrides) }};

        /* Chart colors (dark mode) — Cobalt Pro */
        --chart-1: oklch(0.620 0.237 258);
        --chart-2: oklch(0.696 0.170 162);
        --chart-3: oklch(0.769 0.188 70);
        --chart-4: oklch(0.645 0.246 16);
        --chart-5: oklch(0.627 0.265 304);

        /* Sidebar colors (dark mode) */
        --sidebar: oklch(0.108 0.018 254);
        --sidebar-foreground: oklch(0.980 0.005 258);
        --sidebar-primary: oklch(0.546 0.237 258);
        --sidebar-primary-foreground: oklch(0.985 0 0);
        --sidebar-accent: oklch(0.198 0.020 254);
        --sidebar-accent-foreground: oklch(0.980 0.005 258);
        --sidebar-border: oklch(1 0 0 / 10%);
        --sidebar-ring: oklch(0.546 0.237 258);

        --danger: {{ $cssVal('--danger', 'oklch(0.704 0.191 22.216)', $darkOverrides) }};

        /* Extended semantic colors (dark mode) */
        --success: {{ $cssVal('--success', 'oklch(0.696 0.170 162)', $darkOverrides) }};
        --success-foreground: {{ $cssVal('--success-foreground', 'oklch(0.144 0.022 254)', $darkOverrides) }};
        --warning: {{ $cssVal('--warning', 'oklch(0.769 0.188 70)', $darkOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', 'oklch(0.144 0.022 254)', $darkOverrides) }};
        --info: {{ $cssVal('--info', 'oklch(0.546 0.237 258)', $darkOverrides) }};
        --info-foreground: {{ $cssVal('--info-foreground', 'oklch(0.985 0 0)', $darkOverrides) }};

        /* Card shadows (dark mode) */
        --card-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.2);
        --card-shadow-hover: 0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.2);
    }
</style>
