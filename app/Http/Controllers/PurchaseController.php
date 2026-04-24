<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Purchase::with('supplier');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search): void {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->string('supplier_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->string('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->string('end_date'));
        }

        $purchases = $query->orderByDesc('date')->orderByDesc('created_at')->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $summary = [
            'totalThisMonth' => Purchase::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total'),
            'countThisMonth' => Purchase::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ];

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'summary' => $summary,
            'filters' => $request->only(['search', 'supplier_id', 'start_date', 'end_date']),
        ]);
    }

    public function create(): Response
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $products = Product::orderBy('name')->get(['id', 'name', 'barcode', 'stock', 'cost_price']);

        return Inertia::render('Purchases/Create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost_price' => ['required', 'numeric', 'min:1'],
        ]);

        DB::transaction(function () use ($validated): void {
            $purchaseNumber = 'PO#'.str_pad((string) (Purchase::count() + 1), 4, '0', STR_PAD_LEFT);

            $total = 0;
            foreach ($validated['items'] as &$item) {
                $item['subtotal'] = $item['quantity'] * $item['cost_price'];
                $total += $item['subtotal'];
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'invoice_number' => $validated['invoice_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Weighted average HPP calculation
                $oldStock = $product->stock;
                $newQty = $item['quantity'];
                $newCost = $item['cost_price'];
                $newStock = $oldStock + $newQty;
                $newHpp = $oldStock === 0
                    ? $newCost
                    : (($oldStock * $product->cost_price) + ($newQty * $newCost)) / $newStock;

                // Update product stock & HPP
                $product->update([
                    'stock' => $newStock,
                    'cost_price' => round($newHpp),
                ]);

                // Stock movement log
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'quantity' => $newQty,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'reference' => $purchase->purchase_number,
                    'notes' => 'Pembelian dari '.Supplier::find($validated['supplier_id'])->name,
                ]);
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil disimpan.');
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'items']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }
}
