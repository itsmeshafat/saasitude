<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlanPrice;
use Illuminate\Database\Seeder;

class PlansAndFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = $this->seedFeatures();
        $this->seedPlans($features);
    }

    /** @return array<string, Feature> */
    private function seedFeatures(): array
    {
        $map = [];

        foreach (config('saas-plans.features') as $def) {
            $map[$def['key']] = Feature::updateOrCreate(['key' => $def['key']], $def);
        }

        return $map;
    }

    /** @param  array<string, Feature>  $features */
    private function seedPlans(array $features): void
    {
        foreach (config('saas-plans.plans') as $entry) {
            $plan = Plan::updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'is_public' => $entry['is_public'],
                ]
            );

            foreach ($entry['features'] as $key => $value) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_id' => $features[$key]->id],
                    ['value' => $value]
                );
            }

            foreach ($entry['prices'] ?? [] as $price) {
                PlanPrice::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'gateway' => $price['gateway'],
                        'interval' => $price['interval'],
                        'currency' => $price['currency'],
                    ],
                    ['amount_minor' => $price['amount_minor']]
                );
            }
        }
    }
}
