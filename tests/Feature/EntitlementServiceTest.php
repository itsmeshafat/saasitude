<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Exceptions\EntitlementException;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Models\Workspace;
use App\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementServiceTest extends TestCase
{
    use RefreshDatabase;

    private EntitlementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EntitlementService::class);
    }

    private function makeWorkspace(): Workspace
    {
        $user = User::factory()->create();

        return app(CreateWorkspace::class)->execute($user, 'Acme');
    }

    private function seedFreePlan(): Plan
    {
        $plan = Plan::create(['name' => 'Free', 'slug' => 'free', 'description' => '', 'is_public' => true]);

        $bool = Feature::create(['key' => 'api_access', 'name' => 'API Access', 'type' => 'boolean']);
        $limit = Feature::create(['key' => 'max_seats', 'name' => 'Seats', 'type' => 'limit']);
        $metered = Feature::create(['key' => 'api_calls_per_month', 'name' => 'API Calls', 'type' => 'metered']);

        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $bool->id, 'value' => 'false']);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $limit->id, 'value' => '1']);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $metered->id, 'value' => '0']);

        return $plan;
    }

    public function test_falls_back_to_free_plan_when_no_subscription(): void
    {
        $this->seedFreePlan();
        $context = $this->service->for($this->makeWorkspace());

        $this->assertFalse($context->can('api_access'));
        $this->assertSame(1, $context->limit('max_seats'));
    }

    public function test_can_returns_false_for_unknown_key(): void
    {
        $this->seedFreePlan();
        $this->assertFalse($this->service->for($this->makeWorkspace())->can('nonexistent'));
    }

    public function test_limit_returns_zero_for_unknown_key(): void
    {
        $this->seedFreePlan();
        $this->assertSame(0, $this->service->for($this->makeWorkspace())->limit('nonexistent'));
    }

    public function test_metered_returns_limit_and_used_keys(): void
    {
        $this->seedFreePlan();
        $result = $this->service->for($this->makeWorkspace())->metered('api_calls_per_month');

        $this->assertSame(['limit' => 0, 'used' => 0], $result);
    }

    public function test_enforce_throws_for_blocked_boolean_feature(): void
    {
        $this->seedFreePlan();
        $this->expectException(EntitlementException::class);
        $this->service->for($this->makeWorkspace())->enforce('api_access');
    }

    public function test_enforce_throws_for_zero_limit(): void
    {
        $plan = Plan::create(['name' => 'Free', 'slug' => 'free', 'description' => '', 'is_public' => true]);
        $feature = Feature::create(['key' => 'max_seats', 'name' => 'Seats', 'type' => 'limit']);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'value' => '0']);

        $this->expectException(EntitlementException::class);
        $this->service->for($this->makeWorkspace())->enforce('max_seats');
    }

    public function test_enforce_passes_for_positive_limit(): void
    {
        $this->seedFreePlan();
        $this->service->for($this->makeWorkspace())->enforce('max_seats');
        $this->expectNotToPerformAssertions();
    }

    public function test_enforce_throws_for_missing_feature_key(): void
    {
        $this->seedFreePlan();
        $this->expectException(EntitlementException::class);
        $this->service->for($this->makeWorkspace())->enforce('nonexistent_feature');
    }

    public function test_exception_carries_feature_key(): void
    {
        $this->seedFreePlan();
        try {
            $this->service->for($this->makeWorkspace())->enforce('api_access');
        } catch (EntitlementException $e) {
            $this->assertSame('api_access', $e->getFeatureKey());

            return;
        }
        $this->fail('EntitlementException was not thrown');
    }
}
