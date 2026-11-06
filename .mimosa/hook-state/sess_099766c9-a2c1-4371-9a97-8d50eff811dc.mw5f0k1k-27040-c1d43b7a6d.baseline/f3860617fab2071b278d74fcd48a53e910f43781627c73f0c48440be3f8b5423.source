<?php

namespace Tests\Feature;

use App\Gateways\StripeGateway;
use App\Jobs\ProcessStripeWebhookJob;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Models\Workspace;
use Database\Seeders\StaffRolesSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Fakes\FakeGateway;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $roleSlugs = ['admin']): User
    {
        // Roles must exist before StaffRolesSeeder attaches privileges to them.
        foreach (['admin', 'super-admin', 'finance', 'support'] as $slug) {
            Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst(str_replace('-', ' ', $slug))]);
        }

        $this->seed(StaffRolesSeeder::class);

        $admin = User::factory()->create();
        foreach ($roleSlugs as $slug) {
            $admin->roles()->attach(Role::where('slug', $slug)->firstOrFail());
        }

        return $admin;
    }

    private function makeWorkspaceWithSubscription(string $status = 'active'): Workspace
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id,
            'name' => 'Acme '.$owner->id,
            'slug' => 'acme-'.$owner->id,
            'is_personal' => true,
        ]);

        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);
        $plan->prices()->create([
            'gateway' => 'stripe', 'interval' => 'month', 'currency' => 'USD', 'amount_minor' => 1200,
        ]);

        Subscription::create([
            'workspace_id' => $workspace->id,
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'status' => $status,
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ]);

        return $workspace;
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/dashboard/overview')->assertRedirect('/login');
    }

    public function test_non_admin_is_redirected(): void
    {
        // EnsureIsAdmin middleware redirects non-admins to the dashboard with an error.
        $this->actingAs(User::factory()->create())
            ->get('/dashboard/overview')
            ->assertRedirect();
    }

    public function test_admin_can_view_overview_with_metrics(): void
    {
        $admin = $this->makeAdmin();
        $this->makeWorkspaceWithSubscription('active');

        $this->actingAs($admin)
            ->get('/dashboard/overview')
            ->assertOk()
            ->assertSee('MRR')
            ->assertSee('12.00');
    }

    public function test_admin_can_search_workspaces(): void
    {
        $admin = $this->makeAdmin();
        $workspace = $this->makeWorkspaceWithSubscription();

        $this->actingAs($admin)
            ->get('/dashboard/workspaces-admin?search='.urlencode('Acme'))
            ->assertOk()
            ->assertSee($workspace->name);
    }

    public function test_admin_can_suspend_and_restore_workspace(): void
    {
        $admin = $this->makeAdmin();
        $workspace = $this->makeWorkspaceWithSubscription();

        $this->actingAs($admin)
            ->post("/dashboard/workspaces-admin/{$workspace->id}/suspend")
            ->assertRedirect();

        $this->assertNotNull($workspace->fresh()->suspended_at);

        $this->actingAs($admin)
            ->post("/dashboard/workspaces-admin/{$workspace->id}/restore")
            ->assertRedirect();

        $this->assertNull($workspace->fresh()->suspended_at);
    }

    public function test_admin_can_force_plan(): void
    {
        $admin = $this->makeAdmin();
        $workspace = $this->makeWorkspaceWithSubscription();
        $pro = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'is_public' => true]);

        $this->actingAs($admin)
            ->post("/dashboard/workspaces-admin/{$workspace->id}/force-plan", ['plan_id' => $pro->id, 'trial_days' => 7])
            ->assertRedirect()
            ->assertSessionHas('success');

        $subscription = $workspace->fresh()->activeSubscription();
        $this->assertSame('pro', $subscription->plan->slug);
        $this->assertSame('trialing', $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at);
    }

    public function test_force_plan_blocked_while_impersonating(): void
    {
        $admin = $this->makeAdmin(['super-admin']);
        $workspace = $this->makeWorkspaceWithSubscription();
        $pro = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'is_public' => true]);
        session(['impersonator_id' => 999]);

        $this->actingAs($admin)
            ->post("/dashboard/workspaces-admin/{$workspace->id}/force-plan", ['plan_id' => $pro->id])
            ->assertForbidden();
    }

    public function test_subscriptions_filter_by_status(): void
    {
        $admin = $this->makeAdmin();
        $this->makeWorkspaceWithSubscription('trialing');

        $this->actingAs($admin)
            ->get('/dashboard/subscriptions-admin?status=trialing')
            ->assertOk()
            ->assertSee('Trialing');

        $this->actingAs($admin)
            ->get('/dashboard/subscriptions-admin?status=canceled')
            ->assertOk()
            ->assertSee('No subscriptions found');
    }

    public function test_refund_requires_privilege(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $admin = $this->makeAdmin(['admin']); // admin lacks billing.refund
        $workspace = $this->makeWorkspaceWithSubscription();
        $payment = Payment::create([
            'workspace_id' => $workspace->id,
            'gateway' => 'stripe',
            'gateway_payment_id' => 'pi_test_1',
            'amount' => 1200,
            'currency' => 'USD',
            'status' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->post("/dashboard/payments-admin/{$payment->id}/refund")
            ->assertForbidden();

        $this->assertSame('succeeded', $payment->fresh()->status);
    }

    public function test_refund_with_privilege_marks_refunded(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $finance = $this->makeAdmin(['finance']);
        $workspace = $this->makeWorkspaceWithSubscription();
        $payment = Payment::create([
            'workspace_id' => $workspace->id,
            'gateway' => 'stripe',
            'gateway_payment_id' => 'pi_test_1',
            'amount' => 1200,
            'currency' => 'USD',
            'status' => 'succeeded',
        ]);

        $this->actingAs($finance)
            ->post("/dashboard/payments-admin/{$payment->id}/refund")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('refunded', $payment->fresh()->status);
    }

    public function test_refund_blocked_while_impersonating(): void
    {
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $finance = $this->makeAdmin(['finance']);
        $workspace = $this->makeWorkspaceWithSubscription();
        $payment = Payment::create([
            'workspace_id' => $workspace->id,
            'gateway' => 'stripe',
            'gateway_payment_id' => 'pi_test_1',
            'amount' => 1200,
            'currency' => 'USD',
            'status' => 'succeeded',
        ]);
        session(['impersonator_id' => 999]);

        $this->actingAs($finance)
            ->post("/dashboard/payments-admin/{$payment->id}/refund")
            ->assertForbidden();
    }

    public function test_webhook_replay_requires_privilege_and_requeues(): void
    {
        Queue::fake();
        $support = $this->makeAdmin(['support']); // lacks webhooks.replay
        $event = WebhookEvent::create([
            'gateway' => 'stripe',
            'event_id' => 'evt_1',
            'payload' => ['type' => 'checkout.session.completed'],
            'status' => 'failed',
            'attempts' => 1,
            'error_message' => 'boom',
        ]);

        $this->actingAs($support)
            ->post("/dashboard/webhook-events/{$event->id}/replay")
            ->assertForbidden();

        $boss = $this->makeAdmin(['admin']);
        $this->actingAs($boss)
            ->post("/dashboard/webhook-events/{$event->id}/replay")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('pending', $event->fresh()->status);
        Queue::assertPushed(ProcessStripeWebhookJob::class);
    }

    public function test_gateway_settings_masks_secrets(): void
    {
        $superAdmin = $this->makeAdmin(['super-admin']);

        $this->actingAs($superAdmin)
            ->post('/dashboard/gateway-settings', [
                'gateway' => 'stripe',
                'mode' => 'test',
                'secrets' => ['secret_key' => 'sk_test_abc123xyz', 'webhook_secret' => ''],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $response = $this->actingAs($superAdmin)->get('/dashboard/gateway-settings');
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString('sk_test_abc123xyz', $content);
        $this->assertStringContainsString('xyz', $content); // masked suffix
    }

    public function test_gateway_settings_update_requires_privilege(): void
    {
        $plainAdmin = $this->makeAdmin(['admin']); // lacks settings.manage

        $this->actingAs($plainAdmin)
            ->post('/dashboard/gateway-settings', ['gateway' => 'stripe', 'mode' => 'test'])
            ->assertForbidden();
    }

    public function test_system_health_renders(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get('/dashboard/system-health')
            ->assertOk()
            ->assertSee('Queue depth');
    }
}
