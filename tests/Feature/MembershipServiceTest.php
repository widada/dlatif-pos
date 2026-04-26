<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\MembershipService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipServiceTest extends TestCase
{
    use RefreshDatabase;

    private MembershipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        $this->service = new MembershipService;
    }

    public function test_find_existing_customer(): void
    {
        $customer = Customer::factory()->create(['phone' => '081234567890']);

        $found = $this->service->findOrRegisterCustomer('081234567890');

        $this->assertEquals($customer->id, $found->id);
    }

    public function test_register_new_customer(): void
    {
        $customer = $this->service->findOrRegisterCustomer('081234567890', 'Bu Siti', '1990-04-15');

        $this->assertDatabaseHas('customers', ['phone' => '081234567890', 'name' => 'Bu Siti']);
    }

    public function test_calculate_member_discount(): void
    {
        $customer = Customer::factory()->create();
        Setting::setValue('member_discount_percent', 5);
        Setting::clearCache();

        $result = $this->service->calculateMemberDiscount($customer, 100000, 'Offline');

        $this->assertEquals('member', $result['type']);
        $this->assertEquals(5000, $result['amount']);
        $this->assertEquals(5, $result['percent']);
    }

    public function test_member_discount_disabled_returns_zero(): void
    {
        $customer = Customer::factory()->create();
        Setting::setValue('member_discount_enabled', false);
        Setting::clearCache();

        $result = $this->service->calculateMemberDiscount($customer, 100000, 'Offline');

        $this->assertEquals(0, $result['amount']);
    }

    public function test_birthday_period_detection(): void
    {
        $customer = Customer::factory()->birthdayToday()->create();

        $this->assertTrue($this->service->isBirthdayPeriod($customer));
    }

    public function test_birthday_already_used_this_year(): void
    {
        $customer = Customer::factory()->birthdayToday()->create([
            'last_birthday_used_at' => now(),
        ]);

        $this->assertFalse($this->service->isBirthdayPeriod($customer));
    }

    public function test_no_birthdate_returns_false(): void
    {
        $customer = Customer::factory()->create(['birth_date' => null]);

        $this->assertFalse($this->service->isBirthdayPeriod($customer));
    }

    public function test_determine_best_discount_chooses_birthday(): void
    {
        $customer = Customer::factory()->birthdayToday()->create();
        Setting::clearCache();

        $result = $this->service->determineBestDiscount($customer, 100000, 'Offline');

        // Birthday 20% > Member 5%
        $this->assertEquals('birthday', $result['type']);
        $this->assertEquals(20000, $result['amount']);
    }

    public function test_earn_points(): void
    {
        $customer = Customer::factory()->withoutPoints()->create();
        $transaction = Transaction::factory()->create(['total' => 50000]);
        Setting::clearCache();

        $earned = $this->service->earnPoints($customer, $transaction, 'Offline');

        $this->assertEquals(5, $earned); // 50000 / 10000 = 5
        $this->assertEquals(5, $customer->fresh()->points);
        $this->assertDatabaseHas('point_logs', [
            'customer_id' => $customer->id,
            'type' => 'earn',
            'points' => 5,
        ]);
    }

    public function test_earn_points_floors(): void
    {
        $customer = Customer::factory()->withoutPoints()->create();
        $transaction = Transaction::factory()->create(['total' => 99500]);
        Setting::clearCache();

        $earned = $this->service->earnPoints($customer, $transaction, 'Offline');

        $this->assertEquals(9, $earned); // floor(99500/10000) = 9
    }

    public function test_redeem_points(): void
    {
        $customer = Customer::factory()->withPoints(500)->create();
        $transaction = Transaction::factory()->create();
        Setting::clearCache();

        $discount = $this->service->redeemPoints($customer, 100, $transaction);

        $this->assertEquals(10000, $discount); // 100 * 100
        $this->assertEquals(400, $customer->fresh()->points);
        $this->assertDatabaseHas('point_logs', [
            'customer_id' => $customer->id,
            'type' => 'redeem',
            'points' => -100,
        ]);
    }

    public function test_redeem_below_minimum_throws(): void
    {
        $customer = Customer::factory()->withPoints(500)->create();

        $this->expectException(\Exception::class);
        $this->service->validateRedemption($customer, 50); // min is 100
    }

    public function test_redeem_more_than_balance_throws(): void
    {
        $customer = Customer::factory()->withPoints(50)->create();

        $this->expectException(\Exception::class);
        $this->service->validateRedemption($customer, 100);
    }

    public function test_no_points_earned_on_shopee_channel(): void
    {
        $customer = Customer::factory()->withoutPoints()->create();
        $transaction = Transaction::factory()->create(['total' => 100000]);
        Setting::clearCache();

        $earned = $this->service->earnPoints($customer, $transaction, 'Shopee');

        $this->assertEquals(0, $earned);
    }
}
