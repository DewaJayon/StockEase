<?php

use App\Http\Controllers\Stock\LogStockController;
use App\Http\Controllers\Stock\StockAdjustmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'role:admin, warehouse')->group(function () {
    Route::get('/log-stock', [LogStockController::class, 'index'])->name('log-stock.index');

    Route::get('/stock-adjustment', [StockAdjustmentController::class, 'index'])->name('stock-adjustment.index');
    Route::post('/stock-adjustment', [StockAdjustmentController::class, 'store'])->name('stock-adjustment.store');
    Route::get('/stock-adjustment/search-product', [StockAdjustmentController::class, 'searchProduct'])->name('stock-adjustment.search-product');
});
