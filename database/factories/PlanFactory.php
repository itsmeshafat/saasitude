<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => str($name)->lower(),
            'description' => fake()->sentence(),
            'is_public' => true,
            'is_archived' => false,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['is_archived' => true, 'is_public' => false]);
    }
}
