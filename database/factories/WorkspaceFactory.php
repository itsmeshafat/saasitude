<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => str($name)->slug()->append('-'.fake()->unique()->numberBetween(1, 9999)),
            'is_personal' => false,
        ];
    }

    public function personal(): static
    {
        return $this->state(fn () => ['is_personal' => true]);
    }
}
