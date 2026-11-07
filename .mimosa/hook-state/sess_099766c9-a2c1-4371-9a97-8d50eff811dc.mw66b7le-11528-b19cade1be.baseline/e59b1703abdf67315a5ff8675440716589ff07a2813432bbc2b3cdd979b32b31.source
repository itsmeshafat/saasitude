<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Gateways\StripeGateway;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Fakes\FakeGateway;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = User::factory()->create();

        $this->workspace = app(CreateWorkspace::class)->execute($this->member, 'Acme', true);

        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);
        PlanPrice::create([
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'interval' => 'month',
            'currency' => 'USD',
            'amount_minor' => 1200,
            'gateway_price_id' => 'price_test',
        ]);
    }

    private function withWorkspace(): array
    {
        return ['X-Workspace-Id' => (string) $this->workspace->id];
    }

    /** Authenticate as the workspace owner with a full-ability token. */
    private function authenticate(): void
    {
        Sanctum::actingAs($this->member, ['read', 'write', 'billing:read', 'billing:write']);
    }

    // ---------- Contract: auth / isolation / envelope ----------

    public function test_unauthenticated_gets_401_envelope(): void
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonStructure(['message', 'code', 'errors'])
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_missing_workspace_header_gets_422_envelope(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/workspaces/1/subscription')
            ->assertStatus(422)
            ->assertJsonPath('code', 'workspace_context_required');
    }

    public function test_non_member_gets_403_isolation(): void
    {
        $this->authenticate();
        $outsider = User::factory()->create();
        Sanctum::actingAs($outsider, ['*']);

        $this->getJson("/api/v1/workspaces/{$this->workspace->id}/subscription", $this->withWorkspace())
            ->assertStatus(403)
            ->assertJsonPath('code', 'workspace_forbidden');
    }

    public function test_invalid_input_gets_422_validation_envelope(): void
    {
        $this->authenticate();
        $this->patchJson('/api/v1/me', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonStructure(['message', 'code', 'errors' => ['email']]);
    }

    public function test_missing_token_ability_gets_403(): void
    {
        $this->authenticate();
        Sanctum::actingAs($this->member, ['read']); // no write

        $this->postJson('/api/v1/devices', [
            'push_token' => 'tok123',
            'platform' => 'ios',
        ])->assertStatus(403);
    }

    // ---------- Me ----------

    public function test_me_returns_profile_shape(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'email_verified', 'created_at']]);
    }

    public function test_password_change_with_wrong_current_password(): void
    {
        $this->authenticate();
        $this->putJson('/api/v1/me/password', [
            'current_password' => 'wrong',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422)
            ->assertJsonPath('code', 'invalid_current_password');
    }

    public function test_sessions_list_and_revoke(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/me/sessions')->assertOk()->assertJsonStructure(['data']);

        $tokenId = $this->member->createToken('mobile-test')->accessToken->id;
        $this->deleteJson("/api/v1/me/sessions/{$tokenId}")->assertOk();

        // Already revoked — 404 envelope
        $this->deleteJson("/api/v1/me/sessions/{$tokenId}")
            ->assertStatus(404)
            ->assertJsonPath('code', 'not_found');
    }

    // ---------- Devices ----------

    public function test_device_register_is_idempotent_per_token(): void
    {
        $this->authenticate();
        $payload = ['push_token' => 'fcm-token-1', 'platform' => 'android', 'app_version' => '1.2.0'];

        $this->postJson('/api/v1/devices', $payload)->assertStatus(201);
        $this->postJson('/api/v1/devices', $payload)->assertStatus(201);

        $this->assertDatabaseCount('devices', 1);
    }

    public function test_device_cannot_be_deleted_by_other_user(): void
    {
        $this->authenticate();
        $this->postJson('/api/v1/devices', ['push_token' => 'tok', 'platform' => 'ios']);

        $other = User::factory()->create();
        Sanctum::actingAs($other, ['*']);
        $this->deleteJson('/api/v1/devices/1')->assertStatus(404);
    }

    // ---------- Workspaces ----------

    public function test_workspace_list_and_create(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/workspaces')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);

        $this->postJson('/api/v1/workspaces', ['name' => 'Second WS'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Second WS');
    }

    public function test_member_cannot_update_workspace_name(): void
    {
        $this->authenticate();
        // actingAs user is the owner — add a plain member and use them
        $plain = User::factory()->create();
        $this->workspace->workspaceMembers()->create(['user_id' => $plain->id, 'role' => 'member']);
        Sanctum::actingAs($plain, ['read', 'write']);

        $this->patchJson("/api/v1/workspaces/{$this->workspace->id}", ['name' => 'Hacked'], $this->withWorkspace())
            ->assertStatus(403);
    }

    public function test_members_list_shape(): void
    {
        $this->authenticate();
        $this->getJson("/api/v1/workspaces/{$this->workspace->id}/members", $this->withWorkspace())
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'user_id', 'name', 'email', 'role']]]);
    }

    // ---------- Plans + billing ----------

    public function test_plans_list_with_prices_and_features(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'prices', 'features']]]);
    }

    public function test_subscription_returns_no_subscription_envelope(): void
    {
        $this->authenticate();
        $this->getJson("/api/v1/workspaces/{$this->workspace->id}/subscription", $this->withWorkspace())
            ->assertOk()
            ->assertJsonPath('code', 'no_subscription');
    }

    public function test_usage_shape(): void
    {
        $this->authenticate();
        $this->getJson("/api/v1/workspaces/{$this->workspace->id}/usage", $this->withWorkspace())
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_checkout_requires_idempotency_key(): void
    {
        $this->authenticate();
        $price = PlanPrice::first();

        $this->postJson("/api/v1/workspaces/{$this->workspace->id}/checkout", [
            'plan_price_id' => $price->id,
        ], $this->withWorkspace())
            ->assertStatus(422)
            ->assertJsonPath('code', 'idempotency_key_required');
    }

    public function test_checkout_returns_hosted_url(): void
    {
        $this->authenticate();
        $this->app->instance(StripeGateway::class, new FakeGateway);
        $price = PlanPrice::first();

        $this->postJson("/api/v1/workspaces/{$this->workspace->id}/checkout", [
            'plan_price_id' => $price->id,
        ], $this->withWorkspace() + ['Idempotency-Key' => 'test-key-1'])
            ->assertStatus(201)
            ->assertJsonPath('data.checkout_url', 'https://checkout.example/fake');
    }

    public function test_app_config_shape(): void
    {
        $this->authenticate();
        $this->getJson('/api/v1/app/config')
            ->assertOk()
            ->assertJsonStructure(['data' => ['min_app_version', 'maintenance', 'feature_flags', 'legal_urls']]);
    }
}
