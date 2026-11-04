<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlansAndFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = $this->seedFeatures();
        $this->seedPlans($features);
    }

    private function seedFeatures(): array
    {
        $definitions = [
            ['key' => 'max_seats', 'name' => 'Team Seats', 'type' => 'limit', 'unit' => 'seats'],
            ['key' => 'api_access', 'name' => 'API Access', 'type' => 'boolean', 'unit' => null],
            ['key' => 'api_calls_per_month', 'name' => 'API Calls / Month', 'type' => 'metered', 'unit' => 'calls'],
        ];

        $map = [];
        foreach ($definitions as $def) {
            $map[$def['key']] = Feature::updateOrCreate(['key' => $def['key']], $def);
        }

        return $map;
    }

    private function seedPlans(array $features): void
    {
        $plans = [
            [
                'plan' => ['name' => 'Free', 'slug' => 'free', 'description' => 'Get started for free.', 'is_public' => true],
                'features' => ['max_seats' => '1', 'api_access' => 'false', 'api_calls_per_month' => '0'],
            ],
            [
                'plan' => ['name' => 'Starter', 'slug' => 'starter', 'description' => 'For small teams.', 'is_public' => true],
                'features' => ['max_seats' => '5', 'api_access' => 'true', 'api_calls_per_month' => '1000'],
            ],
            [
                'plan' => ['name' => 'Pro', 'slug' => 'pro', 'description' => 'For growing teams.', 'is_public' => true],
                'features' => ['max_seats' => '-1', 'api_access' => 'true', 'api_calls_per_month' => '10000'],
            ],
        ];

        foreach ($plans as $entry) {
            $plan = Plan::updateOrCreate(['slug' => $entry['plan']['slug']], $entry['plan']);

            foreach ($entry['features'] as $key => $value) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_id' => $features[$key]->id],
                    ['value' => $value]
                );
            }
        }
    }
}
