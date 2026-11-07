<?php

namespace App\Services\Install;

use Illuminate\Support\Facades\Artisan;
use PDO;

/**
 * Step 2 — verifies the submitted database credentials with a raw PDO
 * connection BEFORE anything is written, then persists the config,
 * reloads the app's DB config and runs migrations.
 */
final class DatabaseTester
{
    public function __construct(private readonly EnvWriter $envWriter) {}

    /**
     * @param  array<string, mixed>  $credentials  Validated input.
     *
     * @throws \PDOException on connection failure (message surfaced to the form). (message surfaced to the form).
     */
    public function install(array $credentials): void
    {
        if ($credentials['connection'] === 'sqlite') {
            $path = $credentials['database'];
            if (! is_file($path)) {
                throw new \RuntimeException("SQLite file does not exist: {$path}");
            }
            new PDO('sqlite:'.$path);
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $credentials['host'],
                $credentials['port'],
                $credentials['database'],
            );
            new PDO($dsn, $credentials['username'], $credentials['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
        }

        $this->envWriter->write([
            'APP_URL' => $credentials['app_url'] ?? null,
            'DB_CONNECTION' => $credentials['connection'],
            'DB_HOST' => $credentials['connection'] === 'mysql' ? $credentials['host'] : null,
            'DB_PORT' => $credentials['connection'] === 'mysql' ? $credentials['port'] : null,
            'DB_DATABASE' => $credentials['database'],
            'DB_USERNAME' => $credentials['connection'] === 'mysql' ? $credentials['username'] : null,
            'DB_PASSWORD' => $credentials['connection'] === 'mysql' ? $credentials['password'] : null,
        ]);
        $this->envWriter->ensureAppKey();

        // Re-read the written .env so this process uses the new database.
        app()->loadEnvironmentFrom($this->envWriter->path());
        Artisan::call('config:clear');
        Artisan::call('migrate', ['--force' => true]);
    }
}
