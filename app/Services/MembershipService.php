<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PointLog;
use App\Models\Setting;
use App\Models\Transaction;

class MembershipService
{
    /**
     * Find existing customer by phone or register a new one.
     */
    public function findOrRegisterCustomer(string $phone, ?string $name = null, ?string $birthDate = null): Customer
    {
        $customer = Customer::where('phone', $phone)->first();

        if ($customer) {
            return $customer;
        }

        return Customer::create([
            'name' => $name ?? 'Customer',
            'phone' => $phone,
            'birth_date' => $birthDate,
            'points' => 0,
            'total_spent' => 0,
            'total_transactions' => 0,
        ]);
    }

    /**
     * Calculate the best discount between member discount and birthday discount.
     *
     * @return array{type: string|null, amount: float, percent: int}
     */
    public function determineBestDiscount(Customer $customer, float $subtotal, string $channel): array
    {
        $memberDiscount = $this->calculateMemberDiscount($customer, $subtotal, $channel);
        $birthdayDiscount = $this->calculateBirthdayDiscount($customer, $subtotal);

        // Choose the larger discount
        if ($birthdayDiscount['amount'] >= $memberDiscount['amount'] && $birthdayDiscount['amount'] > 0) {
            return $birthdayDiscount;
        }

        if ($memberDiscount['amount'] > 0) {
            return $memberDiscount;
        }

        return ['type' => null, 'amount' => 0, 'percent' => 0];
    }

    /**
     * Calculate member discount amount.
     *
     * @return array{type: string, amount: float, percent: int}
     */
    public function calculateMemberDiscount(Customer $customer, float $subtotal, string $channel): array
    {
        $result = ['type' => 'member', 'amount' => 0, 'percent' => 0];

        if (! Setting::getValue('member_discount_enabled', true)) {
            return $result;
        }

        $allowedChannels = Setting::getValue('member_discount_channels', ['offline']);
        if (! in_array(strtolower($channel), $allowedChannels)) {
            return $result;
        }

        $minPurchase = Setting::getValue('member_discount_min_purchase', 0);
        if ($subtotal < $minPurchase) {
            return $result;
        }

        $percent = Setting::getValue('member_discount_percent', 5);
        $amount = floor($subtotal * $percent / 100);

        $result['amount'] = $amount;
        $result['percent'] = $percent;

        return $result;
    }

    /**
     * Check if the customer is within their birthday period.
     */
    public function isBirthdayPeriod(Customer $customer): bool
    {
        if (! $customer->birth_date) {
            return false;
        }

        if (! Setting::getValue('birthday_enabled', true)) {
            return false;
        }

        // Check if already used this year
        $maxPerYear = Setting::getValue('birthday_max_per_year', 1);
        if ($customer->last_birthday_used_at) {
            $lastUsedYear = $customer->last_birthday_used_at->year;
            if ($lastUsedYear === now()->year) {
                return false;
            }
        }

        $validDays = Setting::getValue('birthday_valid_days', 7);
        $today = now();
        $birthday = $customer->birth_date->copy()->setYear($today->year);

        $start = $birthday->copy()->subDays($validDays);
        $end = $birthday->copy()->addDays($validDays);

        return $today->between($start, $end);
    }

    /**
     * Calculate birthday discount amount.
     *
     * @return array{type: string, amount: float, percent: int}
     */
    public function calculateBirthdayDiscount(Customer $customer, float $subtotal): array
    {
        $result = ['type' => 'birthday', 'amount' => 0, 'percent' => 0];

        if (! $this->isBirthdayPeriod($customer)) {
            return $result;
        }

        $percent = Setting::getValue('birthday_discount_percent', 20);
        $amount = floor($subtotal * $percent / 100);

        $result['amount'] = $amount;
        $result['percent'] = $percent;

        return $result;
    }

    /**
     * Calculate point discount amount from points used.
     */
    public function calculatePointDiscount(int $pointsUsed): float
    {
        $redeemValue = Setting::getValue('point_redeem_value', 100);

        return $pointsUsed * $redeemValue;
    }

    /**
     * Validate point redemption before processing.
     *
     * @throws \Exception
     */
    public function validateRedemption(Customer $customer, int $pointsUsed): void
    {
        $minRedeem = Setting::getValue('point_min_redeem', 100);

        if ($pointsUsed < $minRedeem) {
            throw new \Exception("Minimum redeem {$minRedeem} poin");
        }

        if ($pointsUsed > $customer->points) {
            throw new \Exception('Poin tidak cukup. Saldo: '.$customer->points.' poin');
        }
    }

    /**
     * Earn points for a transaction and log it.
     */
    public function earnPoints(Customer $customer, Transaction $transaction, string $channel): int
    {
        if (! Setting::getValue('member_enabled', true)) {
            return 0;
        }

        $allowedChannels = Setting::getValue('point_earn_channels', ['offline']);
        if (! in_array(strtolower($channel), $allowedChannels)) {
            return 0;
        }

        $earnAmount = Setting::getValue('point_earn_amount', 10000);
        $earnValue = Setting::getValue('point_earn_value', 1);

        if ($earnAmount <= 0) {
            return 0;
        }

        $earnedPoints = (int) floor($transaction->total / $earnAmount) * $earnValue;

        if ($earnedPoints <= 0) {
            return 0;
        }

        // Update customer points
        $customer->increment('points', $earnedPoints);

        // Calculate expiry date
        $expiredAt = null;
        if (Setting::getValue('point_expired_enabled', false)) {
            $months = Setting::getValue('point_expired_months', 12);
            $expiredAt = now()->addMonths($months);
        }

        // Log
        PointLog::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'type' => 'earn',
            'points' => $earnedPoints,
            'balance_after' => $customer->fresh()->points,
            'notes' => "Earn dari transaksi {$transaction->transaction_number}",
            'expired_at' => $expiredAt,
            'created_at' => now(),
        ]);

        return $earnedPoints;
    }

    /**
     * Redeem points and log it.
     */
    public function redeemPoints(Customer $customer, int $pointsUsed, Transaction $transaction): float
    {
        $this->validateRedemption($customer, $pointsUsed);

        $discount = $this->calculatePointDiscount($pointsUsed);

        // Deduct points
        $customer->decrement('points', $pointsUsed);

        // Log
        PointLog::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'type' => 'redeem',
            'points' => -$pointsUsed,
            'balance_after' => $customer->fresh()->points,
            'notes' => "Redeem {$pointsUsed} poin = Rp ".number_format($discount, 0, ',', '.'),
            'created_at' => now(),
        ]);

        return $discount;
    }

    /**
     * Update customer stats after a transaction.
     */
    public function updateCustomerStats(Customer $customer, float $totalSpent): void
    {
        $customer->increment('total_spent', $totalSpent);
        $customer->increment('total_transactions');
        $customer->update(['last_purchase_at' => now()]);
    }
}
