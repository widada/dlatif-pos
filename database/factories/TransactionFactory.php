<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array{transaction_number: string, date: \DateTimeInterface, channel: string, subtotal: float, discount: float, total: float, payment_method: string, payment_amount: float|null, change_amount: float|null, notes: string|null}
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(25000, 500000);
        $discount = fake()->optional(0.3)->numberBetween(1000, 50000) ?? 0;
        $total = $subtotal - $discount;
        $paymentMethod = fake()->randomElement(['Cash', 'QRIS', 'Transfer']);
        $paymentAmount = $paymentMethod === 'Cash' ? ceil($total / 10000) * 10000 : null;
        $changeAmount = $paymentAmount ? $paymentAmount - $total : null;

        return [
            'transaction_number' => 'TRX-' . now()->format('Ymd') . '-' . strtoupper(fake()->bothify('##??')),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'channel' => fake()->randomElement(['Offline', 'Shopee']),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'payment_amount' => $paymentAmount,
            'change_amount' => $changeAmount,
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    /**
     * Offline channel transaction.
     */
    public function offline(): static
    {
        return $this->state(fn (): array => [
            'channel' => 'Offline',
        ]);
    }

    /**
     * Shopee channel transaction.
     */
    public function shopee(): static
    {
        return $this->state(fn (): array => [
            'channel' => 'Shopee',
        ]);
    }
}
