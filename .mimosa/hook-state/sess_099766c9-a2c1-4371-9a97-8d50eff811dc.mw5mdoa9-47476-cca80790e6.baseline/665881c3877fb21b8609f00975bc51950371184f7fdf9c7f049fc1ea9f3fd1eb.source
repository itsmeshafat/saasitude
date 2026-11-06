<?php

namespace App\Support;

use App\Services\SettingsService;
use HasinHayder\TyroDashboard\Support\DashboardColors;

/**
 * Brand values for transactional emails. Email clients require literal
 * inline styles (no CSS vars), so tokens are resolved server-side here.
 *
 * Colors come from the admin-editable theme (Appearance → Theme colors,
 * stored in dashboard-colors.json, light palette). Branding (logo, app
 * name) comes from the Tyro branding config — same source the sidebar
 * and auth emails use.
 */
class EmailBrand
{
    /** @return array<string, string> */
    public static function all(): array
    {
        $overrides = DashboardColors::load()['light'] ?? [];

        // Design-system light defaults (Hosting Growth); admin overrides win.
        $primary = $overrides['--primary']['hex'] ?? '#4d7c0f';
        $appName = (string) (config('tyro-dashboard.branding.app_name') ?: config('app.name'));

        $logo = (string) (config('tyro-dashboard.branding.logo') ?: '');
        if ($logo !== '' && ! str_starts_with($logo, 'http://') && ! str_starts_with($logo, 'https://')) {
            $logo = url($logo);
        }

        return [
            'app_name' => $appName,
            'logo' => $logo,
            'primary' => $primary,
            'primary_foreground' => '#ffffff',
            'accent_tint' => self::tint($primary, 0.88),
            'page_bg' => '#f4f6f4',
            'card_bg' => '#ffffff',
            'text' => '#0a1616',
            'muted_text' => '#52645c',
            'border' => '#e5e7eb',
            'footer_text' => '#9ca3af',
            'support_email' => (string) (app(SettingsService::class)->get('app.support_email') ?: config('mail.from.address')),
        ];
    }

    /** Lighten a hex color toward white by $amount (0–1). */
    private static function tint(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = (int) hexdec(substr($hex, 0, 2));
        $g = (int) hexdec(substr($hex, 2, 2));
        $b = (int) hexdec(substr($hex, 4, 2));

        $mix = fn (int $c): int => (int) round($c + (255 - $c) * $amount);

        return sprintf('#%02x%02x%02x', $mix($r), $mix($g), $mix($b));
    }
}
