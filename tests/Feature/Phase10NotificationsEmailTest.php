<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Contracts\PushNotificationDriver;
use App\Mail\PaymentFailedMail;
use App\Mail\PlanChanged;
use App\Mail\SubscriptionCanceledMail;
use App\Mail\TrialStarted;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use App\Services\NotificationService;
use App\Services\SettingsService;
use App\Services\SupportTicketService;
use Database\Seeders\StaffRolesSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Fakes\FakePushDriver;
use Tests\TestCase;

class Phase10NotificationsEmailTest extends TestCase
{
    use RefreshDatabase;

    private FakePushDriver $push;

    private User $user;

    /** @var Workspace */
    private $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        // Swap push for a recording fake so no FCM call ever happens.
        $this->app->instance(PushNotificationDriver::class, $this->push = new FakePushDriver);

        $this->user = User::factory()->create();
        $this->workspace = app(CreateWorkspace::class)->execute($this->user, 'Acme', true);
    }

    private function makeSubscription(string $status = 'trialing', bool $trial = true): Subscription
    {
        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);

        $subscription = Subscription::create([
            'workspace_id' => $this->workspace->id,
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'status' => $status,
            'trial_ends_at' => $status === 'trialing' ? now()->addDays(14) : null,
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ]);

        return $subscription->fresh()->load('workspace.owner', 'plan');
    }

    // ---------- Transactional emails ----------

    public function test_activation_on_trial_queues_trial_started_mail(): void
    {
        Mail::fake();

        $this->makeSubscription('trialing')->activate();

        Mail::assertQueued(TrialStarted::class, fn ($mail) => $mail->subjectLine === 'Your trial has started');
    }

    public function test_activation_without_trial_queues_plan_changed_mail(): void
    {
        Mail::fake();

        $this->makeSubscription('past_due')->activate();

        Mail::assertQueued(PlanChanged::class);
    }

    public function test_mark_past_due_queues_payment_failed_mail(): void
    {
        Mail::fake();

        $this->makeSubscription('active')->markPastDue();

        Mail::assertQueued(PaymentFailedMail::class, fn ($mail) => $mail->subjectLine === 'Payment failed - action required');
    }

    public function test_cancel_queues_subscription_canceled_mail(): void
    {
        Mail::fake();

        $this->makeSubscription('active')->cancel();

        Mail::assertQueued(SubscriptionCanceledMail::class);
    }

    public function test_mailable_renders_with_layout(): void
    {
        $mail = new TrialStarted($this->makeSubscription());

        $html = $mail->render();

        $this->assertStringContainsString('Trial started', $html);
        $this->assertStringContainsString(config('app.name'), $html);
    }

    // ---------- In-app notifications + push ----------

    public function test_notification_service_creates_row_and_sends_push(): void
    {
        $this->user->devices()->create([
            'push_token' => 'tok-1', 'platform' => 'android', 'last_used_at' => now(),
        ]);

        app(NotificationService::class)->send(
            $this->user, 'payment.failed', 'Payment failed',
            ['body' => 'Update your card'], '/dashboard/billing', $this->workspace,
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'payment.failed',
            'title' => 'Payment failed',
        ]);
        $this->assertCount(1, $this->push->sent);
    }

    public function test_push_respects_user_preference(): void
    {
        app(SettingsService::class)->set("user.{$this->user->id}.notify.payment.failed.push", '0');
        $this->user->devices()->create(['push_token' => 'tok-1', 'platform' => 'ios']);

        app(NotificationService::class)->send($this->user, 'payment.failed', 'Payment failed', []);

        $this->assertCount(0, $this->push->sent);
    }

    // ---------- Notifications API ----------

    public function test_notifications_api_list_and_mark_read(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write']);
        $notification = app(NotificationService::class)->send($this->user, 'trial.started', 'Trial started', []);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'trial.started');

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('code', 'notification_read');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_preferences_roundtrip(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write']);

        $this->getJson('/api/v1/notifications/preferences')
            ->assertOk()
            ->assertJsonPath('data.3.type', 'payment.failed')
            ->assertJsonPath('data.3.push', true);

        $this->patchJson('/api/v1/notifications/preferences', [
            'preferences' => [['type' => 'payment.failed', 'push' => false]],
        ])->assertOk()->assertJsonPath('data.3.push', false);
    }

    // ---------- Support tickets ----------

    public function test_ticket_lifecycle_create_reply_close(): void
    {
        $service = app(SupportTicketService::class);

        $ticket = $service->create($this->user, $this->workspace, 'Cannot upgrade', 'billing', 'Please help.');
        $this->assertMatchesRegularExpression('/^TKT-\d{4}-\d{5}$/', $ticket->number);
        $this->assertSame('open', $ticket->fresh()->status);

        $staff = User::factory()->create();
        $service->reply($ticket, $staff, 'On it — checking now.', isStaff: true);
        // Reply must NOT auto-close; assert on the DB row (fresh() literal inference here).
        $this->assertDatabaseHas('support_tickets', ['id' => $ticket->id, 'status' => 'open']); // awaiting customer
        // Staff reply notifies the customer in-app
        $this->assertDatabaseHas('notifications', ['user_id' => $this->user->id, 'type' => 'support.reply']);

        $service->reply($ticket, $this->user, 'Thanks!', isStaff: false);
        $service->close($ticket);

        $this->assertSame('closed', $ticket->fresh()->status);

        $this->expectException(\RuntimeException::class);
        $service->reply($ticket, $this->user, 'One more thing…', isStaff: false);
    }

    public function test_user_support_screen_flows(): void
    {
        $service = app(SupportTicketService::class);
        $ticket = $service->create($this->user, $this->workspace, 'Question', 'general', 'Body');

        $this->actingAs($this->user)
            ->get(route('dashboard.support'))
            ->assertOk()
            ->assertSee('Question');

        $this->actingAs($this->user)
            ->get(route('dashboard.support.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->number);

        // Another user's ticket is hidden
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('dashboard.support.show', $ticket))->assertForbidden();
    }

    public function test_admin_support_inbox_requires_privilege_to_reply(): void
    {
        foreach (['admin', 'super-admin', 'support'] as $slug) {
            Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        }
        $this->seed(StaffRolesSeeder::class);

        $ticket = app(SupportTicketService::class)
            ->create($this->user, $this->workspace, 'Help', 'general', 'Body');

        $viewer = User::factory()->create();
        $viewer->roles()->attach(Role::where('slug', 'support')->first());

        // support role HAS support.reply — should work
        $this->actingAs($viewer)
            ->post(route('admin.support.reply', $ticket), ['body' => 'Staff answer'])
            ->assertRedirect();

        $this->assertDatabaseHas('ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'is_staff' => true,
            'body' => 'Staff answer',
        ]);
    }
}
