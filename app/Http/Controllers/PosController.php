<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->pluck('name');

        return Inertia::render('Pos/Index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = DB::transaction(function () use ($validated) {
            $channel = $validated['channel'];
            $discount = $validated['discount'] ?? 0;
            $paymentMethod = $validated['payment_method'];

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

            $total = $subtotal - $discount;
            $paymentAmount = $validated['payment_amount'] ?? null;
            $changeAmount = ($paymentMethod === 'Cash' && $paymentAmount) ? $paymentAmount - $total : null;

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'date' => now(),
                'channel' => $channel,
                'subtotal' => $subtotal,
                'discount' => $discount,
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

                // Update product stock
                $stockBefore = $data['product']->stock;
                $data['product']->decrement('stock', $data['quantity']);

                // Record stock movement
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

            return $transaction;
        });

        return redirect()->route('pos.receipt', $transaction->id)
            ->with('success', 'Transaksi berhasil!');
    }

    public function receipt(Transaction $transaction): Response
    {
        $transaction->load('items');

        return Inertia::render('Pos/Receipt', [
            'transaction' => $transaction,
        ]);
    }

    private function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Transaction::whereDate('date', today())->count() + 1;

        return "TRX-{$date}-" . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
