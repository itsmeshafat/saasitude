<?php

namespace App\Services\Install;

use Illuminate\Support\Facades\File;

/**
 * Step 1 — server requirements: PHP version, required extensions,
 * writable paths. Warnings don't block; failures do.
 */
final class RequirementsChecker
{
    /** @return array<int, array{label: string, ok: bool, blocking: bool}> */
    public function check(): array
    {
        $minimumPhp = '8.2.0'; // variable so static analysis doesn't fold the check

        return [
            ['label' => 'PHP >= '.$minimumPhp.' (running '.PHP_VERSION.')', 'ok' => version_compare(PHP_VERSION, $minimumPhp, '>='), 'blocking' => true],
            ['label' => 'Extension: pdo', 'ok' => extension_loaded('pdo'), 'blocking' => true],
            ['label' => 'Extension: openssl', 'ok' => extension_loaded('openssl'), 'blocking' => true],
            ['label' => 'Extension: mbstring', 'ok' => extension_loaded('mbstring'), 'blocking' => true],
            ['label' => 'Extension: fileinfo', 'ok' => extension_loaded('fileinfo'), 'blocking' => true],
            ['label' => 'Extension: gd (invoices & avatars)', 'ok' => extension_loaded('gd'), 'blocking' => false],
            ['label' => 'storage/ directory writable', 'ok' => is_writable(storage_path()), 'blocking' => true],
            ['label' => 'bootstrap/cache/ directory writable', 'ok' => is_writable(base_path('bootstrap/cache')), 'blocking' => true],
            ['label' => '.env writable (or creatable)', 'ok' => $this->envWritable(), 'blocking' => true],
            ['label' => 'Frontend assets built (npm run build)', 'ok' => File::exists(public_path('build/manifest.json')), 'blocking' => false],
        ];
    }

    public function passes(): bool
    {
        return collect($this->check())->where('blocking', true)->every(fn ($r) => $r['ok']);
    }

    private function envWritable(): bool
    {
        $path = base_path('.env');
        if (File::exists($path)) {
            return is_writable($path);
        }

        return is_writable(base_path());
    }
}
