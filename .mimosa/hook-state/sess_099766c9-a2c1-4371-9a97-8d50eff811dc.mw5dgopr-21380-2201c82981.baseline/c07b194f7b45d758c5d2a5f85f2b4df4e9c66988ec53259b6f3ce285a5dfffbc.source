<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Gateways\StripeGateway;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeGateway;
use Tests\TestCase;

class BillingScreensTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @var Workspace */
    private $workspace;

    private Plan $starter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = app(CreateWorkspace::class)->execute($this->user, 'Acme');

        $this->starter = Plan::firstOrCreate(
            ['slug' => 'starter'],
            ['name' => 'Starter', 'description' => 'Starter plan', 'is_public' => true],
        );
        PlanPrice::create([
            'plan_id' => $this->starter->id,
            'gateway' => 'stripe',
            'interval' => 'month',
            'currency' => 'USD',
            'amount_minor' => 2900,
            'gateway_price_id' => 'price_test_starter',
        ]);
    }

    private function subscribe(): Subscription
    {
        return Subscription::create([
            'workspace_id' => $this->workspace->id,
            'plan_id' => $this->starter->id,
            'gateway' => 'stripe',
            'status' => 'active',
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    public function test_billing_overview_shows_plan_and_status(): void
    {
        $this->subscribe();

        $this->actingAs($this->user)
            ->get(route('dashboard.billing'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Change plan');
    }

    public function test_billing_overview_shows_empty_state_without_subscription(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.billing'))
            ->assertOk()
            ->assertSee('No active plan');
    }

    public function test_plans_page_lists_public_plans(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.plans'))
            ->assertOk()
            ->assertSee('Starter');
    }

    public function test_plans_page_hides_archived_plans(): void
    {
        Plan::create(['name' => 'Secret', 'slug' => 'secret', 'is_public' => false]);

        $this->actingAs($this->user)
            ->get(route('dashboard.plans'))
            ->assertOk()
            ->assertDontSee('Secret');
    }

    public function test_payment_methods_shows_portal_link_for_portal_gateway(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $this->subscribe();

        $this->actingAs($this->user)
            ->get(route('dashboard.payment-methods'))
            ->assertOk()
            ->assertSee('billing portal');
    }

    public function test_cancel_page_requires_active_subscription(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.cancel-subscription'))
            ->assertRedirect(route('dashboard.billing'))
            ->assertSessionHas('error');
    }

    public function test_cancel_page_shows_reason_survey(): void
    {
        $this->subscribe();

        $this->actingAs($this->user)
            ->get(route('dashboard.cancel-subscription'))
            ->assertOk()
            ->assertSee('Why are you cancelling?');
    }

    public function test_cancel_requires_reason(): void
    {
        $this->subscribe();

        $this->actingAs($this->user)
            ->post(route('dashboard.cancel-subscription.cancel'), ['reason' => 'bogus'])
            ->assertSessionHasErrors('reason');
    }

    public function test_cancel_cancels_at_period_end_and_records_reason(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $subscription = $this->subscribe();

        $this->actingAs($this->user)
            ->post(route('dashboard.cancel-subscription.cancel'), ['reason' => 'too_expensive'])
            ->assertRedirect(route('dashboard.billing'))
            ->assertSessionHas('success');

        $subscription = $subscription->fresh();
        $this->assertTrue($subscription->cancel_at_period_end);
        $this->assertSame('too_expensive', $subscription->metadata['cancellation_reason']);
    }

    public function test_cancel_blocked_while_impersonating(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $this->subscribe();
        session(['impersonator_id' => 999]);

        $this->actingAs($this->user)
            ->post(route('dashboard.cancel-subscription.cancel'), ['reason' => 'too_expensive'])
            ->assertRedirect(route('dashboard.cancel-subscription'))
            ->assertSessionHas('error');

        $this->assertFalse($this->workspace->fresh()->activeSubscription()->cancel_at_period_end);
    }

    public function test_checkout_routes_bdt_to_sslcommerz(): void
    {
        PlanPrice::create([
            'plan_id' => $this->starter->id,
            'gateway' => 'sslcommerz',
            'interval' => 'month',
            'currency' => 'BDT',
            'amount_minor' => 290000,
            'gateway_price_id' => 'sslcz_starter',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.billing.checkout', ['currency' => 'BDT']));

        $response->assertOk();
        $this->assertSame('BDT', $response->viewData('currency'));
        $this->assertSame('sslcommerz', $response->viewData('gateway')->key());
    }

    public function test_start_checkout_rejects_invalid_coupon(): void
    {
        $price = PlanPrice::first();

        $this->actingAs($this->user)
            ->post(route('dashboard.billing.checkout.start'), [
                'plan_price_id' => $price->id,
                'coupon' => 'BOGUS',
            ])
            ->assertRedirect(route('dashboard.billing.checkout', ['currency' => 'USD']))
            ->assertSessionHas('error');
    }

    public function test_start_checkout_accepts_valid_coupon(): void
    {
        $this->app->instance(StripeGateway::class, $fake = new FakeGateway);
        Coupon::factory()->create(['code' => 'SAVE20']);
        $price = PlanPrice::first();

        $this->actingAs($this->user)
            ->post(route('dashboard.billing.checkout.start'), [
                'plan_price_id' => $price->id,
                'coupon' => 'save20',
            ])
            ->assertRedirect('https://checkout.example/fake');
    }
}
