<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_for_guest(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create(['username' => 'testadmin']);

        $response = $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->admin()->create(['username' => 'testadmin']);

        $response = $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_increases_failed_attempts(): void
    {
        $user = User::factory()->admin()->create(['username' => 'testadmin']);

        $this->post('/login', ['username' => 'testadmin', 'password' => 'wrong']);

        $user->refresh();
        $this->assertEquals(1, $user->failed_login_attempts);
    }

    public function test_account_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'testadmin',
            'failed_login_attempts' => 4,
        ]);

        $this->post('/login', ['username' => 'testadmin', 'password' => 'wrong']);

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
    }

    public function test_locked_account_cannot_login(): void
    {
        User::factory()->admin()->locked()->create(['username' => 'testadmin']);

        $response = $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->admin()->inactive()->create(['username' => 'testadmin']);

        $response = $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_successful_login_resets_failed_attempts(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'testadmin',
            'failed_login_attempts' => 3,
        ]);

        $this->post('/login', ['username' => 'testadmin', 'password' => 'password']);

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_logout(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
