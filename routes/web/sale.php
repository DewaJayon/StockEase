<?php

use App\Http\Controllers\Sale\SaleHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/sale', [SaleHistoryController::class, 'index'])->name('sale.index');
    Route::get('/sale/{sale}', [SaleHistoryController::class, 'show'])->name('sale.show');
    Route::get('/sale/{sale}/export-to-pdf', [SaleHistoryController::class, 'exportToPdf'])->name('sale.export-to-pdf');
});
