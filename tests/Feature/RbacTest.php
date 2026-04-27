<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_access_dashboard(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/')
            ->assertStatus(200);
    }

    public function test_kasir_can_access_pos(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/pos')
            ->assertStatus(200);
    }

    public function test_kasir_can_access_products(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/products')
            ->assertStatus(200);
    }

    public function test_kasir_can_access_customers(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/customers')
            ->assertStatus(200);
    }

    public function test_kasir_can_access_transactions(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/transactions')
            ->assertStatus(200);
    }

    public function test_kasir_cannot_access_reports(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/reports')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_settings(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/settings')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_users(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/users')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_activity_logs(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/activity-logs')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_purchases(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/purchases')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_suppliers(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/suppliers')
            ->assertStatus(403);
    }

    public function test_kasir_cannot_access_categories(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->get('/categories')
            ->assertStatus(403);
    }

    public function test_admin_can_access_all_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/')->assertStatus(200);
        $this->get('/pos')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/customers')->assertStatus(200);
        $this->get('/transactions')->assertStatus(200);
        $this->get('/reports')->assertStatus(200);
        $this->get('/settings')->assertStatus(200);
        $this->get('/users')->assertStatus(200);
        $this->get('/activity-logs')->assertStatus(200);
    }
}
