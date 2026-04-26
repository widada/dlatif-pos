<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutWithMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->seed(SettingSeeder::class);
    }

    private function createProduct(float $price = 50000, int $stock = 10): Product
    {
        return Product::factory()->create([
            'price_offline' => $price,
            'price_shopee' => $price,
            'stock' => $stock,
        ]);
    }

    public function test_checkout_with_new_member(): void
    {
        $product = $this->createProduct(50000);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 50000,
            'customer_phone' => '081234567890',
            'customer_name' => 'Bu Siti',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['phone' => '081234567890', 'name' => 'Bu Siti']);
    }

    public function test_checkout_with_member_earns_points(): void
    {
        $product = $this->createProduct(100000);
        $customer = Customer::factory()->withoutPoints()->create(['phone' => '081234567890']);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
            'customer_phone' => '081234567890',
            'customer_name' => $customer->name,
        ]);

        $response->assertRedirect();
        $customer->refresh();
        $this->assertGreaterThan(0, $customer->points);
    }

    public function test_checkout_with_point_redemption(): void
    {
        $product = $this->createProduct(100000);
        $customer = Customer::factory()->withPoints(500)->create(['phone' => '081234567890']);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
            'customer_phone' => '081234567890',
            'customer_name' => $customer->name,
            'points_used' => 100,
        ]);

        $response->assertRedirect();
        // Point discount = 100 * 100 = 10000
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'points_used' => 100,
        ]);
    }

    public function test_walk_in_customer_no_points(): void
    {
        $product = $this->createProduct(50000);

        $response = $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 50000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'customer_id' => null,
            'points_earned' => 0,
        ]);
    }

    public function test_receipt_page_loads(): void
    {
        $product = $this->createProduct(50000);

        $this->post('/pos/checkout', [
            'channel' => 'Offline',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Cash',
            'payment_amount' => 50000,
        ]);

        $transaction = Transaction::latest('date')->first();
        $response = $this->get("/pos/receipt/{$transaction->id}");
        $response->assertStatus(200);
    }
}
