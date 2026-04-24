<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var list<string>
     */
    private static array $cosmeticProducts = [
        'Maybelline Fit Me Foundation',
        'Wardah Exclusive Matte Lip Cream',
        'Emina Cheeklit Blush',
        'Make Over Intense Matte Lip Cream',
        'Somethinc Niacinamide Serum',
        'Skintific 5X Ceramide Moisturizer',
        'Implora Urban Lip Cream',
        'Y.O.U Rouge Power Lipstick',
        'Lacoco Hydrating Face Mist',
        'MS Glow Whitening Cream',
        'Scarlett Whitening Body Lotion',
        'Bioaqua Sheet Mask',
        'Hanasui Collagen Water Sunscreen',
        'Pixy UV Whitening BB Cream',
        'Garnier Micellar Water',
        'Nivea Sun Protect Moisture',
        'Pond\'s Age Miracle Day Cream',
        'Cetaphil Gentle Skin Cleanser',
        'Nature Republic Aloe Vera Gel',
        'The Originote Hyaluronic Acid Serum',
    ];

    /**
     * @return array{name: string, category: string, barcode: string|null, price_offline: float, price_shopee: float, cost_price: float, stock: int, min_stock: int}
     */
    public function definition(): array
    {
        $costPrice = fake()->numberBetween(10000, 150000);
        $priceOffline = $costPrice * fake()->randomFloat(2, 1.3, 2.0);
        $priceShopee = $priceOffline * fake()->randomFloat(2, 1.05, 1.25);

        $categories = ['Skincare', 'Makeup', 'Haircare', 'Aksesoris', 'Bodycare'];

        return [
            'name' => fake()->unique()->randomElement(self::$cosmeticProducts),
            'category' => fake()->randomElement($categories),
            'barcode' => fake()->optional(0.8)->ean13(),
            'price_offline' => round($priceOffline, -2),
            'price_shopee' => round($priceShopee, -2),
            'cost_price' => round($costPrice, -2),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => 5,
        ];
    }

    /**
     * Product with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (): array => [
            'stock' => fake()->numberBetween(0, 4),
        ]);
    }

    /**
     * Product with no barcode.
     */
    public function withoutBarcode(): static
    {
        return $this->state(fn (): array => [
            'barcode' => null,
        ]);
    }
}
