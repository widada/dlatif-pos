<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * @return array{transaction_id: string, product_id: string, product_name: string, quantity: int, price: float, cost_price: float, subtotal: float}
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(1, 5);
        $price = $product->price_offline;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'price' => $price,
            'cost_price' => $product->cost_price,
            'subtotal' => $price * $quantity,
        ];
    }
}
