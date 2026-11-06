<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Per-theme sidebar branding. The vendor settings hub saves the sidebar
 * palette to .env (single-theme); with dark + light themes we need a
 * light palette too. Stored in storage/app/sidebar-colors.json:
 *
 *   { "light": { "--sidebar": "#...", ... } }
 *
 * Dark keeps using the env flow (vendor Appearance → Sidebar tab).
 * Values are hex colors; keys mirror the CSS variable names.
 */
class SidebarColors
{
    private const DISK = 'local';

    private const PATH = 'sidebar-colors.json';

    private const KEYS = [
        '--sidebar', '--sidebar-foreground', '--sidebar-primary',
        '--sidebar-primary-foreground', '--sidebar-accent',
        '--sidebar-accent-foreground', '--sidebar-header-border',
    ];

    /** @return array<string, array<string, string>> e.g. ['light' => ['--sidebar' => '#fff']] */
    public static function load(): array
    {
        if (! Storage::disk(self::DISK)->exists(self::PATH)) {
            return ['light' => []];
        }

        $data = json_decode((string) Storage::disk(self::DISK)->get(self::PATH), true);

        return [
            'light' => self::filter($data['light'] ?? []),
        ];
    }

    /** @param array<string, string> $values */
    public static function saveLight(array $values): void
    {
        $existing = self::load();
        $existing['light'] = self::filter($values);

        Storage::disk(self::DISK)->put(self::PATH, json_encode($existing, JSON_PRETTY_PRINT));
    }

    /** @param array<string, string> $values */
    private static function filter(array $values): array
    {
        $clean = [];
        foreach (self::KEYS as $key) {
            if (isset($values[$key]) && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $values[$key])) {
                $clean[$key] = (string) $values[$key];
            }
        }

        return $clean;
    }
}
