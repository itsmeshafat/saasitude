<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanPrice;
use Database\Seeders\PlansAndFeaturesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlansAndFeaturesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_plans_features_and_prices_from_config(): void
    {
        $this->seed(PlansAndFeaturesSeeder::class);

        $this->assertSame(3, Plan::count());
        $this->assertSame(8, PlanPrice::count());

        $starterMonthly = PlanPrice::where('gateway', 'sslcommerz')
            ->where('interval', 'month')
            ->where('currency', 'BDT')
            ->firstOrFail();
        $this->assertSame(99000, $starterMonthly->amount_minor);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(PlansAndFeaturesSeeder::class);
        $this->seed(PlansAndFeaturesSeeder::class);

        $this->assertSame(3, Plan::count());
        $this->assertSame(8, PlanPrice::count());
    }

    public function test_seeder_updates_amount_when_config_changes(): void
    {
        config(['saas-plans.plans.1.prices.0.amount_minor' => 1500]);

        $this->seed(PlansAndFeaturesSeeder::class);

        $price = PlanPrice::where('gateway', 'stripe')
            ->where('interval', 'month')
            ->where('currency', 'USD')
            ->firstOrFail();

        $this->assertSame(1500, $price->amount_minor);
    }
}
