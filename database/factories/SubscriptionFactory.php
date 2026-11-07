<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'plan_id' => Plan::factory(),
            'gateway' => 'stripe',
            'status' => 'active',
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->addMonth(),
        ];
    }

    public function trialing(): static
    {
        return $this->state(fn () => [
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(7),
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn () => ['status' => 'past_due']);
    }

    public function canceled(): static
    {
        return $this->state(fn () => [
            'status' => 'canceled',
            'canceled_at' => now()->subDays(3),
        ]);
    }

    public function forPlanPrice(PlanPrice $price): static
    {
        return $this->state(fn () => [
            'plan_id' => $price->plan_id,
            'gateway' => $price->gateway,
        ]);
    }
}
