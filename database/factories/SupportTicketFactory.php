<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'T-'.fake()->unique()->numberBetween(10000, 99999),
            'user_id' => User::factory(),
            'workspace_id' => Workspace::factory(),
            'subject' => fake()->sentence(6),
            'category' => 'billing',
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed', 'closed_at' => now()->subDay()]);
    }
}
