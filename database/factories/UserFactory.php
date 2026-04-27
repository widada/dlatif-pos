<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Admin role state.
     */
    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => 'admin']);
    }

    /**
     * Kasir role state.
     */
    public function kasir(): static
    {
        return $this->state(fn (): array => ['role' => 'kasir']);
    }

    /**
     * Inactive user state.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * Locked user state.
     */
    public function locked(): static
    {
        return $this->state(fn (): array => [
            'locked_until' => now()->addMinutes(15),
            'failed_login_attempts' => 5,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
