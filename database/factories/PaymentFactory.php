<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'subscription_id' => Subscription::factory(),
            'gateway' => 'stripe',
            'gateway_payment_id' => 'pay_'.fake()->unique()->bothify('????????##'),
            'amount' => 1200,
            'currency' => 'USD',
            'status' => 'succeeded',
        ];
    }

    public function refunded(): static
    {
        return $this->state(fn () => ['status' => 'refunded']);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
