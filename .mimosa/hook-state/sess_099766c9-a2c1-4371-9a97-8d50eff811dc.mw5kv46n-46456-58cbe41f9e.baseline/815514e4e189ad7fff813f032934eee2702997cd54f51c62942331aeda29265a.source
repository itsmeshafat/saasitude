<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('??##??')),
            'type' => 'percent',
            'value' => 20,
            'currency' => null,
            'plan_ids' => null,
            'max_redemptions' => null,
            'redeemed_count' => 0,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function fixed(int $minorAmount, string $currency = 'USD'): static
    {
        return $this->state(fn () => [
            'type' => 'fixed',
            'value' => $minorAmount,
            'currency' => $currency,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function exhausted(): static
    {
        return $this->state(fn () => ['max_redemptions' => 1, 'redeemed_count' => 1]);
    }
}
