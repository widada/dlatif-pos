<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:adjustment_in,adjustment_out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $type = $validated['type'];
        $qty = $validated['quantity'];

        if ($type === 'adjustment_out' && $qty > $product->stock) {
            return back()->with('error', "Stok tidak cukup. Stok saat ini: {$product->stock}");
        }

        DB::transaction(function () use ($product, $type, $qty, $validated): void {
            $oldStock = $product->stock;
            $newStock = $type === 'adjustment_in'
                ? $oldStock + $qty
                : $oldStock - $qty;

            $product->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $type === 'adjustment_in' ? $qty : -$qty,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'reference' => 'Manual Adjustment',
                'notes' => $validated['reason'],
            ]);
        });

        $label = $type === 'adjustment_in' ? 'ditambah' : 'dikurangi';

        return back()->with('success', "Stok {$product->name} berhasil {$label} {$qty} unit.");
    }
}
