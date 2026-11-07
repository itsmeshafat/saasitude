<?php

namespace Tests\Feature;

use App\Actions\ApplyCoupon;
use App\Actions\CreateWorkspace;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyCouponTest extends TestCase
{
    use RefreshDatabase;

    private ApplyCoupon $action;

    /** @var Workspace */
    private $workspace;

    private PlanPrice $price;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ApplyCoupon::class);

        $user = User::factory()->create();
        $this->workspace = app(CreateWorkspace::class)->execute($user, 'Acme');

        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);
        $this->price = PlanPrice::create([
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'interval' => 'month',
            'currency' => 'USD',
            'amount_minor' => 2900,
        ]);
    }

    public function test_applies_valid_coupon(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'SAVE20']);

        $result = $this->action->execute($this->workspace, 'save20', $this->price);

        $this->assertTrue($coupon->is($result));
    }

    public function test_rejects_unknown_code(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not valid');

        $this->action->execute($this->workspace, 'NOPE');
    }

    public function test_rejects_inactive_coupon(): void
    {
        Coupon::factory()->inactive()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no longer active');

        $this->action->execute($this->workspace, Coupon::first()->code, $this->price);
    }

    public function test_rejects_expired_coupon(): void
    {
        Coupon::factory()->expired()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('expired');

        $this->action->execute($this->workspace, Coupon::first()->code, $this->price);
    }

    public function test_rejects_exhausted_coupon(): void
    {
        Coupon::factory()->exhausted()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('redemption limit');

        $this->action->execute($this->workspace, Coupon::first()->code, $this->price);
    }

    public function test_rejects_already_redeemed_by_workspace(): void
    {
        $coupon = Coupon::factory()->create();
        $this->action->record($coupon, $this->workspace, 500);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already been used');

        $this->action->execute($this->workspace, $coupon->code, $this->price);
    }

    public function test_rejects_currency_mismatch_for_fixed_coupon(): void
    {
        Coupon::factory()->fixed(500, 'EUR')->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('currency');

        $this->action->execute($this->workspace, Coupon::first()->code, $this->price);
    }

    public function test_rejects_coupon_restricted_to_other_plan(): void
    {
        $other = Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'description' => '', 'is_public' => true]);
        Coupon::factory()->create(['plan_ids' => [$other->id]]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not valid for the selected plan');

        $this->action->execute($this->workspace, Coupon::first()->code, $this->price);
    }

    public function test_percent_discount_is_integer_minor_units(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 33]);

        $this->assertSame(957, $coupon->discountFor(2900, 'USD')); // 2900*33/100 = 957 exactly
    }

    public function test_fixed_discount_never_exceeds_amount(): void
    {
        $coupon = Coupon::factory()->fixed(5000, 'USD')->create();

        $this->assertSame(2900, $coupon->discountFor(2900, 'USD'));
    }

    public function test_record_creates_redemption_and_increments_count(): void
    {
        $coupon = Coupon::factory()->create();

        $redemption = $this->action->record($coupon, $this->workspace, 580);

        $this->assertDatabaseHas('coupon_redemptions', [
            'id' => $redemption->id,
            'coupon_id' => $coupon->id,
            'workspace_id' => $this->workspace->id,
            'amount_off_minor' => 580,
        ]);
        $this->assertSame(1, $coupon->fresh()->redeemed_count);
    }
}
