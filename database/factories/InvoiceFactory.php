<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'payment_id' => Payment::factory(),
            'subscription_id' => Subscription::factory(),
            'number' => 'INV-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'amount' => 1200,
            'currency' => 'USD',
            'status' => 'paid',
            'line_items' => [['description' => 'Subscription', 'amount' => 1200, 'currency' => 'USD', 'quantity' => 1]],
            'issued_at' => now(),
        ];
    }

    /** Number sequence for a batch: ->numbered(1, 2, 3). */
    public function numbered(int ...$numbers): static
    {
        return $this->state(new Sequence(...array_map(
            fn (int $n) => ['number' => 'INV-'.now()->year.'-'.str_pad((string) $n, 6, '0', STR_PAD_LEFT)],
            $numbers,
        )));
    }
}
