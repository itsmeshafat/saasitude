<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Events\PaymentFailed;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCanceled;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubscriptionStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubscription(string $status = 'trialing'): Subscription
    {
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme');
        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);

        return Subscription::create([
            'workspace_id' => $workspace->id,
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'status' => $status,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    public function test_activate_transitions_to_active_and_fires_event(): void
    {
        Event::fake([SubscriptionActivated::class]);

        $sub = $this->makeSubscription('trialing');
        $sub->activate();

        $this->assertSame('active', $sub->fresh()->status);
        Event::assertDispatched(SubscriptionActivated::class);
    }

    public function test_mark_past_due_fires_payment_failed_event(): void
    {
        Event::fake([PaymentFailed::class]);

        $sub = $this->makeSubscription('active');
        $sub->markPastDue();

        $this->assertSame('past_due', $sub->fresh()->status);
        Event::assertDispatched(PaymentFailed::class);
    }

    public function test_cancel_at_period_end_sets_flag(): void
    {
        Event::fake([SubscriptionCanceled::class]);

        $sub = $this->makeSubscription('active');
        $sub->cancel(immediately: false);

        $this->assertTrue($sub->fresh()->cancel_at_period_end);
        $this->assertSame('active', $sub->fresh()->status);
        Event::assertDispatched(SubscriptionCanceled::class);
    }

    public function test_cancel_immediately_sets_canceled_status(): void
    {
        Event::fake([SubscriptionCanceled::class]);

        $sub = $this->makeSubscription('active');
        $sub->cancel(immediately: true);

        $this->assertSame('canceled', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->canceled_at);
        Event::assertDispatched(SubscriptionCanceled::class);
    }

    public function test_resume_clears_cancel_at_period_end(): void
    {
        $sub = $this->makeSubscription('active');
        $sub->update(['cancel_at_period_end' => true]);

        $sub->resume();

        $this->assertFalse($sub->fresh()->cancel_at_period_end);
        $this->assertSame('active', $sub->fresh()->status);
    }

    public function test_is_active_returns_true_for_active_and_trialing(): void
    {
        $active = $this->makeSubscription('active');
        $trialing = $this->makeSubscription('trialing');
        $pastDue = $this->makeSubscription('past_due');

        $this->assertTrue($active->isActive());
        $this->assertTrue($trialing->isActive());
        $this->assertFalse($pastDue->isActive());
    }
}
