<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Skincare', 'description' => 'Produk perawatan kulit wajah dan tubuh'],
            ['name' => 'Makeup', 'description' => 'Produk kosmetik dekoratif'],
            ['name' => 'Haircare', 'description' => 'Produk perawatan rambut'],
            ['name' => 'Aksesoris', 'description' => 'Aksesoris kecantikan dan fashion'],
            ['name' => 'Bodycare', 'description' => 'Produk perawatan tubuh'],
            ['name' => 'Parfum', 'description' => 'Produk wewangian'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
