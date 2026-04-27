<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Transaction::with('items')->orderBy('date', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('transaction_number', 'like', "%{$search}%");
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $transactions = $query->paginate(20)->withQueryString();

        $summary = [
            'totalTransactions' => Transaction::count(),
            'totalRevenue' => Transaction::sum('total'),
            'todayTransactions' => Transaction::whereDate('date', today())->count(),
            'todayRevenue' => Transaction::whereDate('date', today())->sum('total'),
        ];

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'channel', 'payment_method', 'date_from', 'date_to']),
            'summary' => $summary,
        ]);
    }

    public function show(Transaction $transaction): Response
    {
        $transaction->load(['items', 'cashier:id,name']);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }
}
