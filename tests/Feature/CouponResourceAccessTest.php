<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Models\Coupon;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard/resources/coupons')
            ->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_coupons_resource(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/resources/coupons');

        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_admin_can_list_coupons(): void
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $admin->roles()->attach($role);

        Coupon::factory()->create(['code' => 'ADMINONLY']);

        $this->actingAs($admin)
            ->get('/dashboard/resources/coupons')
            ->assertOk()
            ->assertSee('ADMINONLY');
    }

    public function test_admin_can_list_plan_prices(): void
    {
        // Regression: HasCrud guesses a "gatewayPrice" BelongsTo from
        // gateway_price_id and the index eager-load 500s unless the
        // override nulls the relationship.
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $admin->roles()->attach($role);

        $this->actingAs($admin)
            ->get('/dashboard/resources/plan_prices')
            ->assertOk();
    }

    public function test_coupon_with_redemptions_cannot_be_deleted(): void
    {
        $this->expectException(\RuntimeException::class);

        $coupon = Coupon::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute(User::factory()->create(), 'Acme');
        $coupon->redemptions()->create([
            'workspace_id' => $workspace->id,
            'amount_off_minor' => 100,
            'applied_at' => now(),
        ]);

        $coupon->delete();
    }
}
