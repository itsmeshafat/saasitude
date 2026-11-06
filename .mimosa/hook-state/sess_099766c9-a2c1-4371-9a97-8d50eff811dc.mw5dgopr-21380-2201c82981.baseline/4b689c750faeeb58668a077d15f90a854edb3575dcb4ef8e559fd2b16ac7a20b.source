<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Gateways\PaddleGateway;
use App\Gateways\SSLCommerzGateway;
use App\Gateways\StripeGateway;
use InvalidArgumentException;

class GatewayManager
{
    /** @var array<class-string<PaymentGateway>> */
    private const GATEWAYS = [
        StripeGateway::class,
        PaddleGateway::class,
        SSLCommerzGateway::class,
    ];

    /** @var array<string, PaymentGateway> */
    private array $resolved = [];

    /** @return PaymentGateway[] keyed by gateway key */
    public function all(): array
    {
        $gateways = [];

        foreach (self::GATEWAYS as $gateway) {
            $instance = app($gateway);
            $gateways[$instance->key()] = $instance;
        }

        return $gateways;
    }

    public function resolve(string $key): PaymentGateway
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        foreach (self::GATEWAYS as $gateway) {
            $instance = app($gateway);

            if ($instance->key() === $key) {
                return $this->resolved[$key] = $instance;
            }
        }

        throw new InvalidArgumentException("Unknown payment gateway [{$key}].");
    }

    /** Default gateway (container binding target). */
    public function default(): PaymentGateway
    {
        return app(PaymentGateway::class);
    }

    /** Resolve the gateway for a checkout currency: USD → default, BDT → SSLCommerz. */
    public function forCurrency(string $currency): PaymentGateway
    {
        return match (strtoupper($currency)) {
            'BDT' => $this->resolve('sslcommerz'),
            default => $this->default(),
        };
    }
}
