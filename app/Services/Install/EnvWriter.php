<?php

namespace App\Services\Install;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;

/**
 * Step 2 — writes installer-supplied configuration into .env.
 * Generalizes the regex-upsert pattern from
 * TyroLoginExtraSettingsController::writeEnv(). The target path is
 * constructor-injected so tests can point at a scratch file.
 */
final class EnvWriter
{
    private string $envPath;

    public function __construct(string $envPath = '')
    {
        $this->envPath = $envPath !== '' ? $envPath : base_path('.env');
    }

    /**
     * Upsert key/value pairs; bootstraps from .env.example when the
     * file doesn't exist yet. Values are never logged.
     *
     * @param  array<string, string|int|null>  $values
     */
    public function write(array $values): void
    {
        $content = File::exists($this->envPath)
            ? File::get($this->envPath)
            : (File::exists(base_path('.env.example')) ? File::get(base_path('.env.example')) : '');

        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $content = $this->upsertLine($content, $key, (string) $value);
        }

        File::ensureDirectoryExists(dirname($this->envPath));
        File::put($this->envPath, $content);
    }

    /** Generate and persist APP_KEY (base64:... cipher AES-256-CBC). */
    public function ensureAppKey(): void
    {
        $content = File::exists($this->envPath) ? File::get($this->envPath) : '';
        if (preg_match('/^APP_KEY=base64:.+$/m', $content)) {
            return;
        }

        $key = 'base64:'.base64_encode(Encrypter::generateKey('AES-256-CBC'));
        $this->write(['APP_KEY' => $key]);
    }

    /** Absolute path this writer targets (exposed for the DB step reload). */
    public function path(): string
    {
        return $this->envPath;
    }

    private function upsertLine(string $content, string $key, string $value): string
    {
        $line = $key.'="'.str_replace('"', '\\"', $value).'"';

        if (preg_match('/^'.$key.'=.*$/m', $content)) {
            return (string) preg_replace('/^'.$key.'=.*$/m', $line, $content);
        }

        return $content.($content === '' ? '' : "\n").$line;
    }
}
