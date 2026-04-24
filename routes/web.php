<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('products', ProductController::class)->except(['show']);

Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
Route::get('/pos/receipt/{transaction}', [PosController::class, 'receipt'])->name('pos.receipt');

Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

Route::get('/api/categories', [CategoryController::class, 'index'])->name('api.categories');
