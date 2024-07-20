<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('transactions/compare', [TransactionController::class, 'compare'])->name('transactions.compare');
Route::resource('products', ProductController::class);
Route::resource('transactions', TransactionController::class);
Route::resource('stocks', StockController::class);
Route::resource('product-types', ProductTypeController::class);
