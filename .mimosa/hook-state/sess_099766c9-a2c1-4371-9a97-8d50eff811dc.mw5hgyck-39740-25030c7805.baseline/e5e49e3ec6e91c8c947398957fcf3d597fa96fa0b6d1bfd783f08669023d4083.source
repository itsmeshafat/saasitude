<?php

namespace App\Exceptions;

use Exception;

class EntitlementException extends Exception
{
    public function __construct(private readonly string $featureKey)
    {
        parent::__construct("Your plan does not include access to: {$featureKey}");
    }

    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }
}
