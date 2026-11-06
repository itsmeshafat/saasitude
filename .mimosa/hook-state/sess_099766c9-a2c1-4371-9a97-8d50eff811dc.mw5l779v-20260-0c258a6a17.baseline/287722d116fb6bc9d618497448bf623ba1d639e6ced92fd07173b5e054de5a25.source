<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Models\GatewayCustomer;
use App\Models\User;
use App\Services\GatewayCustomerResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayCustomerResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_gateway_customer_record_on_first_call(): void
    {
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme');

        $resolver = app(GatewayCustomerResolver::class);
        $customer = $resolver->getOrCreate($workspace, 'stripe');

        $this->assertInstanceOf(GatewayCustomer::class, $customer);
        $this->assertSame($workspace->id, $customer->workspace_id);
        $this->assertSame('stripe', $customer->gateway);
        $this->assertDatabaseHas('gateway_customers', ['workspace_id' => $workspace->id, 'gateway' => 'stripe']);
    }

    public function test_returns_existing_record_on_subsequent_calls(): void
    {
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme');
        $resolver = app(GatewayCustomerResolver::class);

        $first = $resolver->getOrCreate($workspace, 'stripe');
        $second = $resolver->getOrCreate($workspace, 'stripe');

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('gateway_customers', 1);
    }

    public function test_different_gateways_create_separate_records(): void
    {
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme');
        $resolver = app(GatewayCustomerResolver::class);

        $stripe = $resolver->getOrCreate($workspace, 'stripe');
        $paddle = $resolver->getOrCreate($workspace, 'paddle');

        $this->assertNotSame($stripe->id, $paddle->id);
        $this->assertDatabaseCount('gateway_customers', 2);
    }
}
