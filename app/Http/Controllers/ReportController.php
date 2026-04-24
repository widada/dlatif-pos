<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', '7d');
        $dateRange = $this->getDateRange($period, $request);

        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Overview stats
        $overview = [
            'totalRevenue' => Transaction::whereBetween('date', [$startDate, $endDate])->sum('total'),
            'totalTransactions' => Transaction::whereBetween('date', [$startDate, $endDate])->count(),
            'avgTransaction' => Transaction::whereBetween('date', [$startDate, $endDate])->avg('total') ?? 0,
            'totalDiscount' => Transaction::whereBetween('date', [$startDate, $endDate])->sum('discount'),
        ];

        // Daily revenue chart
        $dailyRevenue = Transaction::whereBetween('date', [$startDate, $endDate])
            ->select(DB::raw('DATE(date) as day'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Sales by channel
        $byChannel = Transaction::whereBetween('date', [$startDate, $endDate])
            ->select('channel', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->get();

        // Sales by payment method
        $byPayment = Transaction::whereBetween('date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Top products
        $topProducts = TransactionItem::join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->select(
                'transaction_items.product_name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('transaction_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Recent transactions
        $recentTransactions = Transaction::with('items')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        return Inertia::render('Reports/Index', [
            'overview' => $overview,
            'dailyRevenue' => $dailyRevenue,
            'byChannel' => $byChannel,
            'byPayment' => $byPayment,
            'topProducts' => $topProducts,
            'recentTransactions' => $recentTransactions,
            'period' => $period,
            'dateRange' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    private function getDateRange(string $period, Request $request): array
    {
        $end = Carbon::today()->endOfDay();

        return match ($period) {
            '7d' => ['start' => Carbon::today()->subDays(6)->startOfDay(), 'end' => $end],
            '30d' => ['start' => Carbon::today()->subDays(29)->startOfDay(), 'end' => $end],
            'this_month' => ['start' => Carbon::today()->startOfMonth(), 'end' => $end],
            'last_month' => [
                'start' => Carbon::today()->subMonth()->startOfMonth(),
                'end' => Carbon::today()->subMonth()->endOfMonth(),
            ],
            'custom' => [
                'start' => Carbon::parse($request->input('start_date', today()->subDays(6)))->startOfDay(),
                'end' => Carbon::parse($request->input('end_date', today()))->endOfDay(),
            ],
            default => ['start' => Carbon::today()->subDays(6)->startOfDay(), 'end' => $end],
        };
    }
}
