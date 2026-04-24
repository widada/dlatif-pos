<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    /**
     * JSON API: last 20 movements for a product (modal).
     */
    public function recent(Product $product): JsonResponse
    {
        $movements = StockMovement::where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
            ],
            'movements' => $movements,
            'hasMore' => StockMovement::where('product_id', $product->id)->count() > 20,
        ]);
    }

    /**
     * Full page: all movements for a product with pagination.
     */
    public function index(Request $request, Product $product): Response
    {
        $query = StockMovement::where('product_id', $product->id);

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        $movements = $query->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Products/StockMovements', [
            'product' => $product->only(['id', 'name', 'stock', 'min_stock']),
            'movements' => $movements,
            'filters' => $request->only(['type']),
        ]);
    }
}
