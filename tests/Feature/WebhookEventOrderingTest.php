<?php

namespace Tests\Feature;

use App\Gateways\PaddleGateway;
use App\Gateways\StripeGateway;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Monotonic webhook guard (ADR-008): a replayed or out-of-order event
 * older than the last applied event must never rewind subscription
 * state — e.g. revive a canceled subscription with a stale "active"
 * snapshot.
 */
class WebhookEventOrderingTest extends TestCase
{
    use RefreshDatabase;

    private Subscription $subscription;

    private int $later;

    private int $earlier;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id, 'name' => 'Acme', 'slug' => 'acme', 'is_personal' => true,
        ]);
        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'is_public' => true]);

        $this->subscription = Subscription::create([
            'workspace_id' => $workspace->id,
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_order_1',
            'status' => 'active',
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->later = now()->timestamp;
        $this->earlier = now()->subHour()->timestamp;
    }

    /* ---- Stripe ---- */

    public function test_stripe_older_updated_event_cannot_revive_canceled_subscription(): void
    {
        // Newer event: subscription deleted at T2.
        app(StripeGateway::class)->handleEvent([
            'type' => 'customer.subscription.deleted',
            'created' => $this->later,
            'data' => ['object' => ['id' => 'sub_order_1']],
        ]);

        $this->assertSame('canceled', $this->subscription->fresh()->status);

        // Replayed/out-of-order older event: "active" snapshot at T1.
        app(StripeGateway::class)->handleEvent([
            'type' => 'customer.subscription.updated',
            'created' => $this->earlier,
            'data' => ['object' => [
                'id' => 'sub_order_1',
                'status' => 'active',
                'cancel_at_period_end' => false,
                'current_period_start' => now()->subMonth()->timestamp,
                'current_period_end' => now()->addMonth()->timestamp,
            ]],
        ]);

        $this->assertSame('canceled', $this->subscription->fresh()->status, 'Stale active snapshot revived a canceled subscription');
    }

    public function test_stripe_older_invoice_paid_cannot_reactivate_canceled_subscription(): void
    {
        app(StripeGateway::class)->handleEvent([
            'type' => 'customer.subscription.deleted',
            'created' => $this->later,
            'data' => ['object' => ['id' => 'sub_order_1']],
        ]);

        app(StripeGateway::class)->handleEvent([
            'type' => 'invoice.paid',
            'created' => $this->earlier,
            'data' => ['object' => [
                'subscription' => 'sub_order_1',
                'period_start' => now()->subMonth()->timestamp,
                'period_end' => now()->addMonth()->timestamp,
            ]],
        ]);

        $this->assertSame('canceled', $this->subscription->fresh()->status);
    }

    public function test_stripe_newer_event_still_applies(): void
    {
        app(StripeGateway::class)->handleEvent([
            'type' => 'customer.subscription.updated',
            'created' => $this->later,
            'data' => ['object' => [
                'id' => 'sub_order_1',
                'status' => 'past_due',
                'cancel_at_period_end' => false,
            ]],
        ]);

        $this->assertSame('past_due', $this->subscription->fresh()->status);
    }

    /* ---- Paddle ---- */

    public function test_paddle_older_activated_event_cannot_revive_canceled_subscription(): void
    {
        $sub = $this->subscription;
        $sub->update(['gateway' => 'paddle']);

        app(PaddleGateway::class)->handleEvent([
            'event_type' => 'subscription.canceled',
            'occurred_at' => now()->format('Y-m-d\TH:i:sP'),
            'data' => ['id' => 'sub_order_1'],
        ]);

        $this->assertSame('canceled', $sub->fresh()->status);

        app(PaddleGateway::class)->handleEvent([
            'event_type' => 'subscription.activated',
            'occurred_at' => now()->subHour()->format('Y-m-d\TH:i:sP'),
            'data' => [
                'id' => 'sub_order_1',
                'current_billing_period' => [
                    'starts_at' => now()->subMonth()->format('Y-m-d\TH:i:sP'),
                    'ends_at' => now()->addMonth()->format('Y-m-d\TH:i:sP'),
                ],
            ],
        ]);

        $this->assertSame('canceled', $sub->fresh()->status, 'Stale Paddle activated event revived a canceled subscription');
    }

    /* ---- model-level guard ---- */

    public function test_event_is_stale_and_high_water_mark_is_monotonic(): void
    {
        $sub = $this->subscription;

        $this->assertFalse($sub->eventIsStale(Carbon::createFromTimestamp($this->earlier)));
        $sub->markEventApplied(Carbon::createFromTimestamp($this->later));
        $this->assertTrue($sub->eventIsStale(Carbon::createFromTimestamp($this->earlier)));
        $this->assertFalse($sub->eventIsStale(Carbon::createFromTimestamp($this->later + 60)));

        // Mark never moves backwards.
        $sub->markEventApplied(Carbon::createFromTimestamp($this->earlier));
        $this->assertSame($this->later, $sub->fresh()->last_event_at->timestamp);
    }

    public function test_events_without_timestamp_are_always_applied(): void
    {
        $sub = $this->subscription;
        $sub->markEventApplied(Carbon::createFromTimestamp($this->later));

        $this->assertFalse($sub->eventIsStale(null));
    }
}
