<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PointLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointLog>
 */
class PointLogFactory extends Factory
{
    protected $model = PointLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'transaction_id' => null,
            'type' => 'earn',
            'points' => fake()->numberBetween(1, 50),
            'balance_after' => fake()->numberBetween(1, 500),
            'notes' => fake()->optional(0.5)->sentence(),
            'expired_at' => null,
            'created_at' => now(),
        ];
    }

    /**
     * Point earn log.
     */
    public function earn(int $points = 10): static
    {
        return $this->state(fn (): array => [
            'type' => 'earn',
            'points' => $points,
        ]);
    }

    /**
     * Point redeem log.
     */
    public function redeem(int $points = 100): static
    {
        return $this->state(fn (): array => [
            'type' => 'redeem',
            'points' => -$points,
        ]);
    }
}
