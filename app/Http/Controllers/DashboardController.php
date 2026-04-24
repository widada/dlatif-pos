<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Stats
        $totalProducts = Product::count();
        $totalStockValue = Product::selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0;

        // Today vs yesterday sales
        $salesToday = Transaction::whereDate('date', $today)->sum('total');
        $salesYesterday = Transaction::whereDate('date', $yesterday)->sum('total');
        $trxCountToday = Transaction::whereDate('date', $today)->count();

        // Today profit (revenue - COGS)
        $cogsToday = TransactionItem::join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transactions.date', $today)
            ->sum(DB::raw('transaction_items.cost_price * transaction_items.quantity'));
        $profitToday = $salesToday - $cogsToday;

        // Low stock products (list, not just count)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->select('id', 'name', 'stock', 'min_stock', 'category')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        // Recent activity feed (last 10 stock movements)
        $activityFeed = StockMovement::with('product:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'userName' => Auth::user()->name,
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalStockValue' => (float) $totalStockValue,
                'salesToday' => (float) $salesToday,
                'salesYesterday' => (float) $salesYesterday,
                'trxCountToday' => $trxCountToday,
                'profitToday' => (float) $profitToday,
            ],
            'lowStockProducts' => $lowStockProducts,
            'activityFeed' => $activityFeed,
        ]);
    }
}
