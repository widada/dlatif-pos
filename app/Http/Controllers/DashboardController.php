<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalProducts = Product::count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')->count();
        $totalStockValue = Product::selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'lowStockProducts' => $lowStockProducts,
                'totalStockValue' => (float) $totalStockValue,
            ],
        ]);
    }
}
