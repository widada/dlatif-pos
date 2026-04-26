<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->seed(SettingSeeder::class);
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->get('/settings');
        $response->assertStatus(200);
    }

    public function test_update_settings(): void
    {
        $response = $this->put('/settings', [
            'settings' => [
                'member_discount_percent' => 10,
                'point_earn_amount' => 20000,
            ],
        ]);

        $response->assertRedirect('/settings');
        Setting::clearCache();
        $this->assertEquals(10, Setting::getValue('member_discount_percent'));
        $this->assertEquals(20000, Setting::getValue('point_earn_amount'));
    }

    public function test_reset_settings(): void
    {
        Setting::setValue('member_discount_percent', 99);
        Setting::clearCache();

        $response = $this->post('/settings/reset');

        $response->assertRedirect('/settings');
        Setting::clearCache();
        $this->assertEquals(5, Setting::getValue('member_discount_percent'));
    }
}
