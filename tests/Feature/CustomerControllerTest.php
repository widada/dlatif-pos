<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->seed(SettingSeeder::class);
    }

    public function test_customer_index_page(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->get('/customers');
        $response->assertStatus(200);
    }

    public function test_create_customer(): void
    {
        $response = $this->post('/customers', [
            'name' => 'Bu Siti',
            'phone' => '081234567890',
            'birth_date' => '1990-04-15',
        ]);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', [
            'name' => 'Bu Siti',
            'phone' => '081234567890',
        ]);
    }

    public function test_create_customer_validates_phone_unique(): void
    {
        Customer::factory()->create(['phone' => '081234567890']);

        $response = $this->post('/customers', [
            'name' => 'Bu Ani',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_create_customer_validates_phone_format(): void
    {
        $response = $this->post('/customers', [
            'name' => 'Bu Ani',
            'phone' => '12345',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_create_customer_validates_name_min_length(): void
    {
        $response = $this->post('/customers', [
            'name' => 'A',
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Bu Siti']);

        $response = $this->put("/customers/{$customer->id}", [
            'name' => 'Bu Siti Updated',
            'phone' => $customer->phone,
        ]);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Bu Siti Updated']);
    }

    public function test_delete_customer_soft_deletes(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->delete("/customers/{$customer->id}");

        $response->assertRedirect('/customers');
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_show_customer_detail(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->get("/customers/{$customer->id}");
        $response->assertStatus(200);
    }

    public function test_search_customer_by_phone(): void
    {
        $customer = Customer::factory()->create(['phone' => '081234567890']);

        $response = $this->getJson('/api/customers/search?phone=081234567890');

        $response->assertStatus(200);
        $response->assertJsonPath('customer.id', $customer->id);
    }

    public function test_search_customer_returns_null_for_unknown(): void
    {
        $response = $this->getJson('/api/customers/search?phone=089999999999');

        $response->assertStatus(200);
        $response->assertJsonPath('customer', null);
    }
}
