<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::factory()->create(['name' => 'Skincare']);
    }

    public function testPosPageIsDisplayed(): void
    {
        $response = $this->get('/pos');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Pos/Index')
            ->has('products')
            ->has('categories')
        );
    }

    public function testCheckoutCreatesTransaction(): void
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'price_offline' => 50000,
            'price_shopee' => 60000,
            'cost_price' => 30000,
        ]);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'discount' => 0,
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'channel' => 'Offline',
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'Cash',
        ]);
        $this->assertDatabaseHas('transaction_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => -2,
            'stock_before' => 10,
            'stock_after' => 8,
        ]);
    }

    public function testCheckoutWithDiscount(): void
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'price_offline' => 50000,
        ]);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount' => 5000,
            'payment_method' => 'QRIS',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'subtotal' => 50000,
            'discount' => 5000,
            'total' => 45000,
        ]);
    }

    public function testCheckoutWithShopeeChannel(): void
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'price_offline' => 50000,
            'price_shopee' => 70000,
        ]);

        $this->post('/pos/checkout', [
            'channel' => 'Shopee',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount' => 0,
            'payment_method' => 'Transfer',
        ]);

        $this->assertDatabaseHas('transactions', [
            'channel' => 'Shopee',
            'subtotal' => 70000,
        ]);
    }

    public function testCheckoutValidatesEmptyCart(): void
    {
        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [],
            'payment_method' => 'Cash',
            'payment_amount' => 10000,
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function testCheckoutValidatesInvalidChannel(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Tokopedia',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
        ]);

        $response->assertSessionHasErrors('channel');
    }

    public function testReceiptPageIsDisplayed(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price_offline' => 50000, 'cost_price' => 30000]);
        $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'discount' => 0,
            'payment_method' => 'Cash',
            'payment_amount' => 50000,
        ]);

        $transaction = Transaction::first();
        $response = $this->get("/pos/receipt/{$transaction->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Pos/Receipt')
            ->has('transaction')
        );
    }
}
