<?php

namespace App\Services\Install;

use Illuminate\Support\Facades\File;

/**
 * First-run installer state: the ordered wizard steps and the
 * "already installed" lock file marker.
 *
 * The lock is deliberately a file (not a DB row): the database can be
 * dropped while the app must stay locked out of the installer.
 */
final class InstallerState
{
    /** Ordered wizard steps: [session key => route]. */
    public const STEPS = [
        'requirements' => '/install/requirements',
        'database' => '/install/database',
        'admin' => '/install/admin',
        'gateway' => '/install/gateway',
        'demo' => '/install/demo',
    ];

    public static function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function installed(): bool
    {
        return File::exists(self::lockPath());
    }

    /** Write the lock file; safe to call multiple times. */
    public static function lock(): void
    {
        File::ensureDirectoryExists(dirname(self::lockPath()));

        if (! self::installed()) {
            File::put(self::lockPath(), json_encode([
                'installed_at' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0'),
            ]).PHP_EOL);
        }
    }
}
