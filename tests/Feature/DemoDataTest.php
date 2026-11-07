<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Install\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_core_data_only_when_demo_disabled(): void
    {
        app(DemoDataSeeder::class)->seed(withDemo: false);

        $this->assertSame(0, User::where('email', DemoDataSeeder::DEMO_OWNER_EMAIL)->count());
        $this->assertSame(3, Plan::count()); // from config/saas-plans.php
        $this->assertDatabaseHas('settings', ['key' => 'trial_length_days']);
    }

    public function test_seeds_rich_demo_dataset(): void
    {
        app(DemoDataSeeder::class)->seed(withDemo: true);

        $owner = User::where('email', DemoDataSeeder::DEMO_OWNER_EMAIL)->firstOrFail();
        $workspace = Workspace::where('owner_id', $owner->id)->firstOrFail();

        // Active Stripe subscription with payments and invoices.
        $subscription = Subscription::where('workspace_id', $workspace->id)->where('status', 'active')->firstOrFail();
        $this->assertSame('stripe', $subscription->gateway);
        $this->assertSame('pro', $subscription->plan->slug);
        $this->assertSame(3, Payment::where('subscription_id', $subscription->id)->count());
        $this->assertSame(2, Invoice::where('workspace_id', $workspace->id)->count());

        // Subscription states for admin filters.
        foreach (['trialing', 'past_due', 'canceled'] as $status) {
            $this->assertSame(1, Subscription::where('status', $status)->count(), "Missing {$status} subscription");
        }
        $this->assertSame('paddle', Subscription::where('status', 'trialing')->firstOrFail()->gateway);
        $this->assertSame('sslcommerz', Subscription::where('status', 'past_due')->firstOrFail()->gateway);

        // Coupons (one expired) and support tickets.
        $this->assertSame(2, Coupon::whereIn('code', ['WELCOME10', 'EXPIRED20'])->count());
        $this->assertSame(1, Coupon::where('code', 'EXPIRED20')->where('is_active', true)->count());
        $this->assertSame(2, SupportTicket::where('user_id', $owner->id)->count());

        // Extra member user on the demo workspace.
        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => User::where('email', DemoDataSeeder::DEMO_MEMBER_EMAIL)->firstOrFail()->id,
            'role' => 'member',
        ]);
    }

    public function test_demo_seeder_is_idempotent(): void
    {
        $seeder = app(DemoDataSeeder::class);
        $seeder->seed(withDemo: true);
        $seeder->seed(withDemo: true);

        $this->assertSame(1, User::where('email', DemoDataSeeder::DEMO_OWNER_EMAIL)->count());
        $this->assertSame(3, Plan::count());
    }

    public function test_reset_demo_command_removes_only_demo_rows(): void
    {
        app(DemoDataSeeder::class)->seed(withDemo: true);
        $keepUser = User::factory()->create();
        $keepWorkspace = Workspace::create([
            'owner_id' => $keepUser->id, 'name' => 'Keep', 'slug' => 'keep', 'is_personal' => true,
        ]);

        $this->artisan('saas:reset-demo')->assertExitCode(0);

        $this->assertSame(0, User::where('email', DemoDataSeeder::DEMO_OWNER_EMAIL)->count());
        $this->assertSame(0, User::where('email', DemoDataSeeder::DEMO_MEMBER_EMAIL)->count());
        $this->assertSame(0, Subscription::where('gateway_customer_id', 'demo')->count());
        $this->assertSame(0, Coupon::whereIn('code', ['WELCOME10', 'EXPIRED20'])->count());
        $this->assertSame(0, SupportTicket::whereIn('number', ['DEMO-1', 'DEMO-2'])->count());
        // Non-demo rows survive; plans stay.
        $this->assertTrue($keepUser->exists());
        $this->assertSame(1, Workspace::where('id', $keepWorkspace->id)->count());
        $this->assertSame(3, Plan::count());
    }
}
