<?php

namespace App\Http\Controllers;

use App\Helpers\PhoneHelper;
use App\Http\Requests\CheckoutRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function __construct(
        private MembershipService $membershipService,
    ) {}

    public function index(): Response
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->pluck('name');

        // Pass membership settings to frontend
        $memberSettings = [
            'member_enabled' => Setting::getValue('member_enabled', true),
            'member_discount_enabled' => Setting::getValue('member_discount_enabled', true),
            'member_discount_percent' => Setting::getValue('member_discount_percent', 5),
            'birthday_enabled' => Setting::getValue('birthday_enabled', true),
            'birthday_discount_percent' => Setting::getValue('birthday_discount_percent', 20),
            'point_earn_amount' => Setting::getValue('point_earn_amount', 10000),
            'point_earn_value' => Setting::getValue('point_earn_value', 1),
            'point_redeem_value' => Setting::getValue('point_redeem_value', 100),
            'point_min_redeem' => Setting::getValue('point_min_redeem', 100),
            'auto_register_on_phone' => Setting::getValue('auto_register_on_phone', true),
        ];

        return Inertia::render('Pos/Index', [
            'products' => $products,
            'categories' => $categories,
            'memberSettings' => $memberSettings,
        ]);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = DB::transaction(function () use ($validated) {
            $channel = $validated['channel'];
            $discount = $validated['discount'] ?? 0;
            $paymentMethod = $validated['payment_method'];
            $customerPhone = $validated['customer_phone'] ?? null;
            $pointsUsed = $validated['points_used'] ?? 0;

            $subtotal = 0;
            $itemsData = [];

            // Calculate items and validate stock
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}");
                }

                $price = $channel === 'Shopee' ? $product->price_shopee : $product->price_offline;
                $itemSubtotal = $price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Handle customer/member
            $customer = null;
            $memberDiscount = 0;
            $birthdayDiscount = 0;
            $pointDiscount = 0;

            if ($customerPhone && Setting::getValue('member_enabled', true)) {
                $phone = PhoneHelper::normalize($customerPhone);
                $customer = $this->membershipService->findOrRegisterCustomer(
                    $phone,
                    $validated['customer_name'] ?? null,
                    $validated['customer_birth_date'] ?? null,
                );

                // Calculate best discount (member vs birthday)
                $bestDiscount = $this->membershipService->determineBestDiscount($customer, $subtotal, $channel);

                if ($bestDiscount['type'] === 'member') {
                    $memberDiscount = $bestDiscount['amount'];
                } elseif ($bestDiscount['type'] === 'birthday') {
                    $birthdayDiscount = $bestDiscount['amount'];
                    // Mark birthday as used
                    $customer->update(['last_birthday_used_at' => now()]);
                }

                // Point redemption
                if ($pointsUsed > 0) {
                    $this->membershipService->validateRedemption($customer, $pointsUsed);
                    $pointDiscount = $this->membershipService->calculatePointDiscount($pointsUsed);

                    // Ensure total doesn't go negative
                    $maxPointDiscount = $subtotal - $memberDiscount - $birthdayDiscount - $discount;
                    if ($pointDiscount > $maxPointDiscount) {
                        $pointDiscount = max(0, $maxPointDiscount);
                    }
                }
            }

            $total = max(0, $subtotal - $discount - $memberDiscount - $birthdayDiscount - $pointDiscount);
            $paymentAmount = $validated['payment_amount'] ?? null;
            $changeAmount = ($paymentMethod === 'Cash' && $paymentAmount) ? $paymentAmount - $total : null;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'date' => now(),
                'channel' => $channel,
                'cashier_id' => auth()->id(),
                'customer_id' => $customer?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'member_discount' => $memberDiscount,
                'birthday_discount' => $birthdayDiscount,
                'points_used' => $pointsUsed,
                'point_discount' => $pointDiscount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create transaction items and update stock
            foreach ($itemsData as $data) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $data['product']->id,
                    'product_name' => $data['product']->name,
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'cost_price' => $data['cost_price'],
                    'subtotal' => $data['subtotal'],
                ]);

                $stockBefore = $data['product']->stock;
                $data['product']->decrement('stock', $data['quantity']);

                StockMovement::create([
                    'product_id' => $data['product']->id,
                    'transaction_id' => $transaction->id,
                    'type' => 'sale',
                    'quantity' => -$data['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $data['quantity'],
                    'notes' => "Penjualan {$transaction->channel} - {$transaction->transaction_number}",
                ]);
            }

            // Handle point operations for member
            if ($customer) {
                // Redeem points
                if ($pointsUsed > 0) {
                    $this->membershipService->redeemPoints($customer, $pointsUsed, $transaction);
                }

                // Earn points from final total
                $earnedPoints = $this->membershipService->earnPoints($customer, $transaction, $channel);

                // Update transaction with earned points
                $transaction->update(['points_earned' => $earnedPoints]);

                // Update customer stats
                $this->membershipService->updateCustomerStats($customer, $total);
            }

            return $transaction;
        });

        return redirect()->route('pos.receipt', $transaction->id)
            ->with('success', 'Transaksi berhasil!');
    }

    public function receipt(Transaction $transaction): Response
    {
        $transaction->load(['items', 'customer', 'cashier:id,name']);

        // Get receipt settings
        $receiptSettings = [
            'show_member_info' => Setting::getValue('receipt_show_member_info', true),
            'show_point_info' => Setting::getValue('receipt_show_point_info', true),
            'show_savings' => Setting::getValue('receipt_show_savings', true),
            'show_promo_footer' => Setting::getValue('receipt_show_promo_footer', true),
            'header_text' => Setting::getValue('receipt_header_text', 'Dlatif Store'),
            'address' => Setting::getValue('receipt_address', ''),
            'phone' => Setting::getValue('receipt_phone', ''),
            'social_media' => Setting::getValue('receipt_social_media', ''),
            'footer_text' => Setting::getValue('receipt_footer_text', 'Terima kasih sudah belanja!'),
            'promo_text' => Setting::getValue('receipt_promo_text', ''),
        ];

        $maskedPhone = null;
        $isBirthday = false;
        $pointsBefore = 0;

        if ($transaction->customer) {
            $maskedPhone = PhoneHelper::mask($transaction->customer->phone);
            $isBirthday = $transaction->birthday_discount > 0;
            // Calculate points before this transaction
            $pointsBefore = $transaction->customer->points
                - $transaction->points_earned
                + $transaction->points_used;
        }

        $totalSavings = $transaction->member_discount
            + $transaction->birthday_discount
            + $transaction->point_discount
            + $transaction->discount;

        return Inertia::render('Pos/Receipt', [
            'transaction' => $transaction,
            'receiptSettings' => $receiptSettings,
            'maskedPhone' => $maskedPhone,
            'isBirthday' => $isBirthday,
            'pointsBefore' => $pointsBefore,
            'totalSavings' => $totalSavings,
        ]);
    }

    private function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Transaction::whereDate('date', today())->count() + 1;

        return "TRX-{$date}-".str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
