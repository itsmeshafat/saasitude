<?php

namespace App\Services;

use App\Exceptions\EntitlementException;

/**
 * @phpstan-type FeatureRow array{type: string, value: string}
 * @phpstan-type FeatureMap array<string, FeatureRow>
 */
class EntitlementContext
{
    /** @param FeatureMap $features */
    public function __construct(private readonly array $features) {}

    public function can(string $key): bool
    {
        $feature = $this->features[$key] ?? null;

        if ($feature === null || $feature['type'] !== 'boolean') {
            return false;
        }

        return filter_var($feature['value'], FILTER_VALIDATE_BOOLEAN);
    }

    public function limit(string $key): int
    {
        $feature = $this->features[$key] ?? null;

        if ($feature === null || $feature['type'] !== 'limit') {
            return 0;
        }

        return (int) $feature['value'];
    }

    /** @return array{limit: int, used: int} */
    public function metered(string $key): array
    {
        $feature = $this->features[$key] ?? null;

        return [
            'limit' => $feature !== null ? (int) $feature['value'] : 0,
            'used' => 0, // ponytail: usage recording wired in T-029
        ];
    }

    public function enforce(string $key): void
    {
        $feature = $this->features[$key] ?? null;

        if ($feature === null) {
            throw new EntitlementException($key);
        }

        match ($feature['type']) {
            'boolean' => $this->can($key) ?: throw new EntitlementException($key),
            'limit' => $this->limit($key) > 0 ?: throw new EntitlementException($key),
            default => null,
        };
    }
}
