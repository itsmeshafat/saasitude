<?php

namespace App\Services\Install;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PDO;

/**
 * Step 2 — verifies the submitted database credentials with a raw PDO
 * connection BEFORE anything is written, then persists the config,
 * switches the running process to the new connection and migrates.
 */
final class DatabaseTester
{
    public function __construct(private readonly EnvWriter $envWriter) {}

    /**
     * Tyro packages register their migrations only when running in a real
     * console (PHP_SAPI check), which is false for Artisan::call() during
     * a web request — register them explicitly so the wizard migrates the
     * roles/privileges/audit tables too.
     */
    private function registerPackageMigrations(): void
    {
        $migrator = app('migrator');
        foreach (glob(base_path('vendor/hasinhayder/*/database/migrations')) ?: [] as $path) {
            if (! in_array($path, $migrator->paths(), true)) {
                $migrator->path($path);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $credentials  Validated input.
     *
     * @throws \PDOException on connection failure (message surfaced to the form). (message surfaced to the form).
     */
    public function install(array $credentials): void
    {
        $isMysql = $credentials['connection'] === 'mysql';

        if ($isMysql) {
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
        } else {
            $path = $credentials['database'];
            if (! is_file($path)) {
                throw new \RuntimeException("SQLite file does not exist: {$path}");
            }
            new PDO('sqlite:'.$path);
        }

        $this->envWriter->write([
            'APP_URL' => $credentials['app_url'] ?? null,
            'DB_CONNECTION' => $credentials['connection'],
            'DB_HOST' => $isMysql ? $credentials['host'] : null,
            'DB_PORT' => $isMysql ? $credentials['port'] : null,
            'DB_DATABASE' => $credentials['database'],
            'DB_USERNAME' => $isMysql ? $credentials['username'] : null,
            'DB_PASSWORD' => $isMysql ? $credentials['password'] : null,
            // The pre-install gate pinned file stores (no usable DB yet);
            // migrations create the tables, so switch back to DB stores.
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
        ]);
        $this->envWriter->ensureAppKey();

        // Point the RUNNING process at the new database (env/config were
        // read at boot; the .env write alone does not affect this request).
        Config::set('database.default', $credentials['connection']);
        Config::set("database.connections.{$credentials['connection']}", $isMysql ? [
            'driver' => 'mysql',
            'host' => $credentials['host'],
            'port' => $credentials['port'],
            'database' => $credentials['database'],
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'charset' => 'utf8mb4',
        ] : [
            'driver' => 'sqlite',
            'database' => $credentials['database'],
        ]);
        Config::set(['session.driver' => 'database', 'cache.default' => 'database']);

        $this->registerPackageMigrations();
        Artisan::call('migrate', ['--force' => true]);
    }
}
