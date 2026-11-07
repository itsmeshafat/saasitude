<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Gateways\SSLCommerzGateway;
use App\Gateways\StripeGateway;
use App\Services\GatewayManager;
use InvalidArgumentException;
use Tests\TestCase;

class GatewayManagerTest extends TestCase
{
    private GatewayManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = app(GatewayManager::class);
    }

    public function test_resolves_gateway_by_key(): void
    {
        $this->assertInstanceOf(StripeGateway::class, $this->manager->resolve('stripe'));
        $this->assertInstanceOf(SSLCommerzGateway::class, $this->manager->resolve('sslcommerz'));
    }

    public function test_resolve_is_cached_per_key(): void
    {
        $this->assertSame($this->manager->resolve('stripe'), $this->manager->resolve('stripe'));
    }

    public function test_unknown_key_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->manager->resolve('paypal');
    }

    public function test_all_returns_gateways_keyed_by_key(): void
    {
        $all = $this->manager->all();

        $this->assertArrayHasKey('stripe', $all);
        $this->assertArrayHasKey('paddle', $all);
        $this->assertArrayHasKey('sslcommerz', $all);
    }

    public function test_default_matches_container_binding(): void
    {
        $default = $this->manager->default();

        $this->assertInstanceOf(PaymentGateway::class, $default);
        $this->assertSame(app(PaymentGateway::class)->key(), $default->key());
    }

    public function test_currency_routing_bdt_goes_to_sslcommerz(): void
    {
        $this->assertInstanceOf(
            SSLCommerzGateway::class,
            $this->manager->forCurrency('BDT')
        );
        $this->assertInstanceOf(
            StripeGateway::class,
            $this->manager->forCurrency('USD')
        );
    }
}
