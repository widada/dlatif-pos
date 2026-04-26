<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'birth_date' => fake()->optional(0.7)->dateTimeBetween('-60 years', '-15 years'),
            'points' => fake()->numberBetween(0, 500),
            'total_spent' => fake()->numberBetween(0, 5000000),
            'total_transactions' => fake()->numberBetween(0, 50),
            'last_purchase_at' => fake()->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Customer with no points.
     */
    public function withoutPoints(): static
    {
        return $this->state(fn (): array => [
            'points' => 0,
            'total_spent' => 0,
            'total_transactions' => 0,
            'last_purchase_at' => null,
        ]);
    }

    /**
     * Customer whose birthday is today.
     */
    public function birthdayToday(): static
    {
        return $this->state(fn (): array => [
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
        ]);
    }

    /**
     * Customer with many points.
     */
    public function withPoints(int $points = 500): static
    {
        return $this->state(fn (): array => [
            'points' => $points,
        ]);
    }
}
