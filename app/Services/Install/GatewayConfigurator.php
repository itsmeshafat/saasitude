<?php

namespace App\Services\Install;

use App\Services\SettingsService;

/**
 * Step 4 (optional) — stores gateway credentials through the same
 * encrypted SettingsService keys the Admin → Gateway Settings screen
 * uses, so the installer never duplicates storage conventions.
 */
final class GatewayConfigurator
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * @param  array<string, string|null>  $input  Validated, possibly partial input.
     */
    public function save(array $input): void
    {
        $map = [
            'stripe_mode' => ['gateway.stripe.mode', false],
            'stripe_secret' => ['gateway.stripe.secret_key', true],
            'stripe_webhook_secret' => ['gateway.stripe.webhook_secret', true],
            'paddle_mode' => ['gateway.paddle.mode', false],
            'paddle_api_key' => ['gateway.paddle.api_key', true],
            'paddle_webhook_secret' => ['gateway.paddle.webhook_secret', true],
            'sslcommerz_mode' => ['gateway.sslcommerz.mode', false],
            'sslcommerz_store_id' => ['gateway.sslcommerz.store_id', false],
            'sslcommerz_store_password' => ['gateway.sslcommerz.store_password', true],
        ];

        foreach ($map as $field => [$key, $secret]) {
            if (($input[$field] ?? null) !== null && $input[$field] !== '') {
                $this->settings->set($key, $input[$field], $secret);
            }
        }
    }
}
