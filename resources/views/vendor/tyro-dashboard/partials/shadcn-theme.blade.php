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
        /* Base radius — Cobalt Glass: 12px */
        --radius: 0.75rem;

        /* Light mode colors — Cobalt Glass (frosted surfaces over a gradient mesh) */
        --background: {{ $cssVal('--background', 'oklch(0.965 0.012 258)', $lightOverrides) }};
        --foreground: {{ $cssVal('--foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --card: {{ $cssVal('--card', 'oklch(1 0 0 / 0.55)', $lightOverrides) }};
        --card-foreground: {{ $cssVal('--card-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --popover: {{ $cssVal('--popover', 'oklch(1 0 0 / 0.85)', $lightOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --primary: {{ $cssVal('--primary', 'oklch(0.546 0.237 258)', $lightOverrides) }};
        --primary-foreground: {{ $cssVal('--primary-foreground', 'oklch(0.985 0 0)', $lightOverrides) }};
        --secondary: {{ $cssVal('--secondary', 'oklch(0.962 0.008 258 / 0.7)', $lightOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --muted: {{ $cssVal('--muted', 'oklch(0.962 0.008 258 / 0.6)', $lightOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', 'oklch(0.42 0.025 254)', $lightOverrides) }};
        --accent: {{ $cssVal('--accent', 'oklch(0.925 0.045 258 / 0.7)', $lightOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --destructive: {{ $cssVal('--destructive', 'oklch(0.577 0.245 27.325)', $lightOverrides) }};
        --destructive-foreground: {{ $cssVal('--destructive-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --border: {{ $cssVal('--border', 'oklch(0.546 0.237 258 / 0.18)', $lightOverrides) }};
        --input: {{ $cssVal('--input', 'oklch(0.546 0.237 258 / 0.22)', $lightOverrides) }};
        --ring: {{ $cssVal('--ring', 'oklch(0.546 0.237 258)', $lightOverrides) }};

        /* Glass system — Cobalt Glass */
        --glass: oklch(1 0 0 / 0.55);
        --glass-strong: oklch(1 0 0 / 0.72);
        --glass-border: oklch(1 0 0 / 0.65);
        --glass-blur: blur(18px) saturate(1.5);
        --glass-highlight: inset 0 1px 0 oklch(1 0 0 / 0.6);
        /* Mesh backdrop blobs (rendered behind frosted surfaces) */
        --mesh:
            radial-gradient(600px 420px at 12% -6%, oklch(0.72 0.16 250 / 0.42), transparent 65%),
            radial-gradient(760px 520px at 96% 12%, oklch(0.78 0.14 300 / 0.34), transparent 62%),
            radial-gradient(680px 540px at 42% 108%, oklch(0.8 0.13 190 / 0.32), transparent 60%);

        /* Chart colors — Cobalt Pro palette */
        --chart-1: oklch(0.546 0.237 258);
        --chart-2: oklch(0.627 0.194 149);
        --chart-3: oklch(0.769 0.188 70);
        --chart-4: oklch(0.577 0.245 27);
        --chart-5: oklch(0.620 0.148 310);

        /* Sidebar colors — frosted light glass */
        --sidebar: oklch(1 0 0 / 0.6);
        --sidebar-foreground: oklch(0.144 0.022 254);
        --sidebar-primary: oklch(0.546 0.237 258);
        --sidebar-primary-foreground: oklch(0.985 0 0);
        --sidebar-accent: oklch(0.925 0.045 258 / 0.7);
        --sidebar-accent-foreground: oklch(0.144 0.022 254);
        --sidebar-border: oklch(0.546 0.237 258 / 0.15);
        --sidebar-ring: oklch(0.546 0.237 258);

        /* Extended semantic colors */
        --success: {{ $cssVal('--success', 'oklch(0.627 0.194 149)', $lightOverrides) }};
        --success-foreground: {{ $cssVal('--success-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --warning: {{ $cssVal('--warning', 'oklch(0.769 0.188 70)', $lightOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', 'oklch(0.144 0.022 254)', $lightOverrides) }};
        --info: {{ $cssVal('--info', 'oklch(0.546 0.237 258)', $lightOverrides) }};
        --info-foreground: {{ $cssVal('--info-foreground', 'oklch(1 0 0)', $lightOverrides) }};
        --danger: {{ $cssVal('--danger', 'oklch(0.577 0.245 27.325)', $lightOverrides) }};

        /* Card shadows — glass depth */
        --card-shadow: 0 8px 32px -12px oklch(0.4 0.1 255 / 0.25), var(--glass-highlight);
        --card-shadow-hover: 0 16px 40px -12px oklch(0.4 0.1 255 / 0.35), var(--glass-highlight);
    }

    /* Dark mode colors - using .dark class — Cobalt Glass dark: smoked glass over deep mesh */
    .dark {
        --background: {{ $cssVal('--background', 'oklch(0.13 0.02 254)', $darkOverrides) }};
        --foreground: {{ $cssVal('--foreground', 'oklch(0.97 0.005 254)', $darkOverrides) }};
        --card: {{ $cssVal('--card', 'oklch(0.21 0.028 254 / 0.55)', $darkOverrides) }};
        --card-foreground: {{ $cssVal('--card-foreground', 'oklch(0.97 0.005 254)', $darkOverrides) }};
        --popover: {{ $cssVal('--popover', 'oklch(0.19 0.025 254 / 0.9)', $darkOverrides) }};
        --popover-foreground: {{ $cssVal('--popover-foreground', 'oklch(0.97 0.005 254)', $darkOverrides) }};
        --primary: {{ $cssVal('--primary', 'oklch(0.62 0.2 258)', $darkOverrides) }};
        --primary-foreground: {{ $cssVal('--primary-foreground', 'oklch(0.13 0.02 254)', $darkOverrides) }};
        --secondary: {{ $cssVal('--secondary', 'oklch(0.28 0.02 254 / 0.7)', $darkOverrides) }};
        --secondary-foreground: {{ $cssVal('--secondary-foreground', 'oklch(0.97 0.005 254)', $darkOverrides) }};
        --muted: {{ $cssVal('--muted', 'oklch(0.28 0.02 254 / 0.6)', $darkOverrides) }};
        --muted-foreground: {{ $cssVal('--muted-foreground', 'oklch(0.68 0.015 254)', $darkOverrides) }};
        --accent: {{ $cssVal('--accent', 'oklch(0.3 0.04 258 / 0.6)', $darkOverrides) }};
        --accent-foreground: {{ $cssVal('--accent-foreground', 'oklch(0.97 0.005 254)', $darkOverrides) }};
        --destructive: {{ $cssVal('--destructive', 'oklch(0.704 0.191 22.216)', $darkOverrides) }};
        --destructive-foreground: {{ $cssVal('--destructive-foreground', 'oklch(0.985 0 0)', $darkOverrides) }};
        --border: {{ $cssVal('--border', 'oklch(1 0 0 / 12%)', $darkOverrides) }};
        --input: {{ $cssVal('--input', 'oklch(1 0 0 / 16%)', $darkOverrides) }};
        --ring: {{ $cssVal('--ring', 'oklch(0.62 0.2 258)', $darkOverrides) }};

        /* Glass system — dark smoked glass */
        --glass: oklch(0.21 0.028 254 / 0.55);
        --glass-strong: oklch(0.24 0.03 254 / 0.75);
        --glass-border: oklch(1 0 0 / 0.14);
        --glass-blur: blur(18px) saturate(1.4);
        --glass-highlight: inset 0 1px 0 oklch(1 0 0 / 0.1);
        --mesh:
            radial-gradient(600px 420px at 12% -6%, oklch(0.35 0.14 258 / 0.5), transparent 65%),
            radial-gradient(760px 520px at 96% 12%, oklch(0.35 0.12 310 / 0.4), transparent 62%),
            radial-gradient(680px 540px at 42% 108%, oklch(0.33 0.1 200 / 0.4), transparent 60%);

        /* Chart colors (dark mode) — Cobalt Pro */
        --chart-1: oklch(0.620 0.237 258);
        --chart-2: oklch(0.696 0.170 162);
        --chart-3: oklch(0.769 0.188 70);
        --chart-4: oklch(0.645 0.246 16);
        --chart-5: oklch(0.627 0.265 304);

        /* Sidebar colors (dark mode) — smoked glass */
        --sidebar: oklch(0.16 0.022 254 / 0.6);
        --sidebar-foreground: oklch(0.97 0.005 254);
        --sidebar-primary: oklch(0.62 0.2 258);
        --sidebar-primary-foreground: oklch(0.13 0.02 254);
        --sidebar-accent: oklch(0.3 0.04 258 / 0.6);
        --sidebar-accent-foreground: oklch(0.97 0.005 254);
        --sidebar-border: oklch(1 0 0 / 10%);
        --sidebar-ring: oklch(0.62 0.2 258);

        --danger: {{ $cssVal('--danger', 'oklch(0.704 0.191 22.216)', $darkOverrides) }};

        /* Extended semantic colors (dark mode) */
        --success: {{ $cssVal('--success', 'oklch(0.696 0.170 162)', $darkOverrides) }};
        --success-foreground: {{ $cssVal('--success-foreground', 'oklch(0.144 0.022 254)', $darkOverrides) }};
        --warning: {{ $cssVal('--warning', 'oklch(0.769 0.188 70)', $darkOverrides) }};
        --warning-foreground: {{ $cssVal('--warning-foreground', 'oklch(0.144 0.022 254)', $darkOverrides) }};
        --info: {{ $cssVal('--info', 'oklch(0.62 0.2 258)', $darkOverrides) }};
        --info-foreground: {{ $cssVal('--info-foreground', 'oklch(0.985 0 0)', $darkOverrides) }};

        /* Card shadows (dark mode) — glass depth */
        --card-shadow: 0 8px 32px -12px oklch(0 0 0 / 0.5), var(--glass-highlight);
        --card-shadow-hover: 0 16px 40px -12px oklch(0 0 0 / 0.6), var(--glass-highlight);
    }
</style>
