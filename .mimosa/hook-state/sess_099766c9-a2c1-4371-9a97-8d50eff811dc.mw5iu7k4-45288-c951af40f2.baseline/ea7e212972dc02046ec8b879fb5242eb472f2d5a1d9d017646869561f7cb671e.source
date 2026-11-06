<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Jobs\ProcessDataExport;
use App\Mail\AccountDeletionConfirmed;
use App\Mail\DataExportReady;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DataPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithSubscription(): array
    {
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme', true);
        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);
        Subscription::create([
            'workspace_id' => $workspace->id, 'plan_id' => $plan->id, 'gateway' => 'stripe',
            'status' => 'active', 'current_period_start' => now(), 'current_period_end' => now()->addMonth(),
        ]);

        return [$user, $workspace];
    }

    // ---------- Web: export ----------

    public function test_export_queues_job_and_creates_pending_row(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.data-privacy.export'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(ProcessDataExport::class);
        $this->assertDatabaseHas('data_exports', ['user_id' => $user->id, 'status' => 'pending']);
    }

    public function test_process_data_export_creates_json_email_and_notification(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        app(CreateWorkspace::class)->execute($user, 'Acme', true);
        $export = $user->dataExports()->create([
            'status' => 'pending', 'expires_at' => now()->addDays(7),
        ]);

        (new ProcessDataExport($export->id))->handle(app(NotificationService::class));

        $export->refresh();
        $this->assertSame('ready', $export->status);
        Storage::assertExists($export->file_path);

        $payload = json_decode(Storage::get($export->file_path), true);
        $this->assertSame($user->email, $payload['account']['email']);
        $this->assertSame('Acme', $payload['workspaces'][0]['name']);

        Mail::assertQueued(DataExportReady::class, fn ($m) => $m->hasTo($user->email));
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'data.export']);
    }

    public function test_download_requires_ownership(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $export = $other->dataExports()->create([
            'file_path' => 'data-exports/x.json', 'status' => 'ready', 'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.data-privacy.download', $export))
            ->assertForbidden();
    }

    public function test_download_expired_export_is_404(): void
    {
        $user = User::factory()->create();
        Storage::put('data-exports/x.json', '{}');
        $export = $user->dataExports()->create([
            'file_path' => 'data-exports/x.json', 'status' => 'ready', 'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.data-privacy.download', $export))
            ->assertNotFound();
    }

    // ---------- Web: account deletion guards ----------

    public function test_delete_blocked_with_active_subscription(): void
    {
        [$user] = $this->makeUserWithSubscription();

        $this->actingAs($user)
            ->post(route('dashboard.data-privacy.delete'), ['password' => 'password'])
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_delete_blocked_when_workspace_has_other_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team', false);
        $workspace->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $this->actingAs($owner)
            ->post(route('dashboard.data-privacy.delete'), ['password' => 'password'])
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_delete_succeeds_for_clean_personal_account(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($user, 'Solo', true);

        $this->actingAs($user)
            ->post(route('dashboard.data-privacy.delete'), ['password' => 'password'])
            ->assertRedirect(route('public.home'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
        Mail::assertQueued(AccountDeletionConfirmed::class, fn ($m) => $m->hasTo($user->email));
    }

    // ---------- API (P1) ----------

    public function test_api_export_requires_idempotency_key(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['write']);

        $this->postJson('/api/v1/me/export')->assertStatus(422);
    }

    public function test_api_export_queues(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['write']);

        $this->postJson('/api/v1/me/export', [], ['Idempotency-Key' => 'exp-1'])
            ->assertStatus(202)
            ->assertJsonPath('code', 'export_queued');

        Queue::assertPushed(ProcessDataExport::class);
    }

    public function test_api_delete_requires_password_and_deletes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['write']);

        $this->deleteJson('/api/v1/me', ['password' => 'wrong'])
            ->assertStatus(422);

        $this->deleteJson('/api/v1/me', ['password' => 'password'])
            ->assertOk()
            ->assertJsonPath('code', 'account_deleted');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
