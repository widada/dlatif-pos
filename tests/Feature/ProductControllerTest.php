<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::factory()->create(['name' => 'Skincare']);
        Category::factory()->create(['name' => 'Makeup']);
    }

    public function testProductIndexPageIsDisplayed(): void
    {
        Product::factory()->create(['name' => 'Test Product', 'category' => 'Skincare']);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Products/Index')
            ->has('products')
            ->has('categories')
        );
    }

    public function testProductIndexCanSearchByName(): void
    {
        Product::factory()->create(['name' => 'Maybelline Foundation']);
        Product::factory()->create(['name' => 'Wardah Lip Cream']);

        $response = $this->get('/products?search=Maybelline');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Products/Index')
            ->where('products.data.0.name', 'Maybelline Foundation')
            ->where('products.total', 1)
        );
    }

    public function testProductIndexCanFilterByCategory(): void
    {
        Product::factory()->create(['name' => 'Product A', 'category' => 'Skincare']);
        Product::factory()->create(['name' => 'Product B', 'category' => 'Makeup']);

        $response = $this->get('/products?category=Skincare');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('products.total', 1)
        );
    }

    public function testProductCreatePageIsDisplayed(): void
    {
        $response = $this->get('/products/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Products/Create')
            ->has('categories')
        );
    }

    public function testProductCanBeCreated(): void
    {
        $response = $this->post('/products', [
            'name' => 'New Product',
            'category' => 'Skincare',
            'barcode' => '1234567890123',
            'price_offline' => 50000,
            'price_shopee' => 60000,
            'cost_price' => 30000,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function testProductCreationRequiresRequiredFields(): void
    {
        $response = $this->post('/products', []);

        $response->assertSessionHasErrors([
            'name', 'category', 'price_offline', 'price_shopee', 'cost_price', 'stock', 'min_stock',
        ]);
    }

    public function testProductCreationRejectsUniqueBarcode(): void
    {
        Product::factory()->create(['barcode' => '1234567890123']);

        $response = $this->post('/products', [
            'name' => 'Another Product',
            'category' => 'Skincare',
            'barcode' => '1234567890123',
            'price_offline' => 50000,
            'price_shopee' => 60000,
            'cost_price' => 30000,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        $response->assertSessionHasErrors('barcode');
    }

    public function testProductEditPageIsDisplayed(): void
    {
        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Products/Edit')
            ->has('product')
            ->has('categories')
        );
    }

    public function testProductCanBeUpdated(): void
    {
        $product = Product::factory()->create();

        $response = $this->put("/products/{$product->id}", [
            'name' => 'Updated Name',
            'category' => 'Skincare',
            'barcode' => null,
            'price_offline' => 70000,
            'price_shopee' => 80000,
            'cost_price' => 40000,
            'stock' => 20,
            'min_stock' => 5,
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price_offline' => 70000,
        ]);
    }

    public function testProductUpdateAllowsSameBarcodeForSameProduct(): void
    {
        $product = Product::factory()->create(['barcode' => '9999999999999']);

        $response = $this->put("/products/{$product->id}", [
            'name' => $product->name,
            'category' => $product->category,
            'barcode' => '9999999999999',
            'price_offline' => $product->price_offline,
            'price_shopee' => $product->price_shopee,
            'cost_price' => $product->cost_price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');
    }

    public function testProductCanBeDeleted(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
