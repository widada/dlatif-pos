<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
        $user = Auth::user();
        $today = Carbon::today();

        if ($user->isKasir()) {
            return $this->kasirDashboard($user, $today);
        }

        return $this->adminDashboard($user, $today);
    }

    private function adminDashboard($user, Carbon $today): Response
    {
        $yesterday = Carbon::yesterday();

        $totalProducts = Product::count();
        $totalStockValue = Product::selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0;

        $salesToday = Transaction::whereDate('date', $today)->sum('total');
        $salesYesterday = Transaction::whereDate('date', $yesterday)->sum('total');
        $trxCountToday = Transaction::whereDate('date', $today)->count();

        $cogsToday = TransactionItem::join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transactions.date', $today)
            ->sum(DB::raw('transaction_items.cost_price * transaction_items.quantity'));
        $profitToday = $salesToday - $cogsToday;

        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->select('id', 'name', 'stock', 'min_stock', 'category')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        $activityFeed = StockMovement::with('product:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'userName' => $user->name,
            'userRole' => $user->role,
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

    private function kasirDashboard($user, Carbon $today): Response
    {
        $myTrxToday = Transaction::where('cashier_id', $user->id)
            ->whereDate('date', $today);

        $mySalesToday = (float) (clone $myTrxToday)->sum('total');
        $myTrxCount = (clone $myTrxToday)->count();

        $newMembersToday = Customer::whereDate('created_at', $today)->count();

        $recentTrx = Transaction::where('cashier_id', $user->id)
            ->whereDate('date', $today)
            ->orderByDesc('date')
            ->limit(5)
            ->get(['id', 'transaction_number', 'total', 'date', 'payment_method']);

        return Inertia::render('Dashboard', [
            'userName' => $user->name,
            'userRole' => $user->role,
            'stats' => [
                'mySalesToday' => $mySalesToday,
                'myTrxCount' => $myTrxCount,
                'newMembersToday' => $newMembersToday,
            ],
            'recentTrx' => $recentTrx,
            'lowStockProducts' => [],
            'activityFeed' => [],
        ]);
    }
}
