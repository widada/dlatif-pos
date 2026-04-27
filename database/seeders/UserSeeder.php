<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'metikadevi@gmail.com'],
            [
                'name' => 'Metika Devi',
                'username' => 'metikadevi',
                'password' => 'metikacool321',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
