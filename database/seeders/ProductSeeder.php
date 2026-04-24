<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Maybelline Fit Me Foundation',
                'category' => 'Makeup',
                'barcode' => '6902395580911',
                'price_offline' => 125000,
                'price_shopee' => 145000,
                'cost_price' => 85000,
                'stock' => 25,
                'min_stock' => 5,
            ],
            [
                'name' => 'Wardah Exclusive Matte Lip Cream',
                'category' => 'Makeup',
                'barcode' => '8993137696395',
                'price_offline' => 55000,
                'price_shopee' => 65000,
                'cost_price' => 35000,
                'stock' => 40,
                'min_stock' => 10,
            ],
            [
                'name' => 'Somethinc Niacinamide Serum',
                'category' => 'Skincare',
                'barcode' => '8997035880123',
                'price_offline' => 98000,
                'price_shopee' => 115000,
                'cost_price' => 65000,
                'stock' => 15,
                'min_stock' => 5,
            ],
            [
                'name' => 'Skintific 5X Ceramide Moisturizer',
                'category' => 'Skincare',
                'barcode' => '6971710350012',
                'price_offline' => 135000,
                'price_shopee' => 155000,
                'cost_price' => 90000,
                'stock' => 18,
                'min_stock' => 5,
            ],
            [
                'name' => 'Garnier Micellar Water',
                'category' => 'Skincare',
                'barcode' => '8992304055010',
                'price_offline' => 45000,
                'price_shopee' => 52000,
                'cost_price' => 28000,
                'stock' => 30,
                'min_stock' => 10,
            ],
            [
                'name' => 'Scarlett Whitening Body Lotion',
                'category' => 'Bodycare',
                'barcode' => '8996001440125',
                'price_offline' => 75000,
                'price_shopee' => 85000,
                'cost_price' => 48000,
                'stock' => 22,
                'min_stock' => 5,
            ],
            [
                'name' => 'Emina Cheeklit Blush',
                'category' => 'Makeup',
                'barcode' => '8993137697088',
                'price_offline' => 35000,
                'price_shopee' => 42000,
                'cost_price' => 22000,
                'stock' => 35,
                'min_stock' => 10,
            ],
            [
                'name' => 'Implora Urban Lip Cream',
                'category' => 'Makeup',
                'barcode' => null,
                'price_offline' => 25000,
                'price_shopee' => 30000,
                'cost_price' => 15000,
                'stock' => 50,
                'min_stock' => 10,
            ],
            [
                'name' => 'Hanasui Collagen Water Sunscreen',
                'category' => 'Skincare',
                'barcode' => '8993040940012',
                'price_offline' => 28000,
                'price_shopee' => 35000,
                'cost_price' => 18000,
                'stock' => 3,
                'min_stock' => 5,
            ],
            [
                'name' => 'Nature Republic Aloe Vera Gel',
                'category' => 'Skincare',
                'barcode' => '8806173406494',
                'price_offline' => 85000,
                'price_shopee' => 98000,
                'cost_price' => 55000,
                'stock' => 12,
                'min_stock' => 5,
            ],
            [
                'name' => 'Jepit Rambut Mutiara Set',
                'category' => 'Aksesoris',
                'barcode' => null,
                'price_offline' => 15000,
                'price_shopee' => 20000,
                'cost_price' => 8000,
                'stock' => 60,
                'min_stock' => 15,
            ],
            [
                'name' => 'Pantene Shampoo Hair Fall Control',
                'category' => 'Haircare',
                'barcode' => '4902430877084',
                'price_offline' => 38000,
                'price_shopee' => 45000,
                'cost_price' => 25000,
                'stock' => 20,
                'min_stock' => 5,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
