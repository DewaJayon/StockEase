<?php

use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\MidtransTransactionController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\PosController;
use App\Http\Controllers\Dashboard\PurcaseController;
use App\Http\Controllers\Dashboard\SaleHistoryController;
use App\Http\Controllers\Dashboard\SaleRepotController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// dashboard & general route
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// admin route
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('category', CategoryController::class);
});

// master data route
Route::middleware(['auth', 'role:admin, warehouse'])->group(function () {
    Route::resource('supplier', SupplierController::class);
    Route::resource('product', ProductController::class);
});

// POS Route
Route::prefix('pos')->middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::patch('/change-qty', [PosController::class, 'changeQty'])->name('pos.change-qty');
    Route::post('/add-to-cart', [PosController::class, 'addToCart'])->name('pos.add-to-cart');
    Route::get('/get-cart', [PosController::class, 'getCartJson'])->name('pos.get-cart');
    Route::delete('/remove-from-cart', [PosController::class, 'removeFromCart'])->name('pos.remove-from-cart');
    Route::delete('/empty-cart', [PosController::class, 'emptyCart'])->name('pos.empty-cart');
    Route::put('/checkout/{sale}', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/qris-token', [PaymentController::class, 'createMidtransTransaction'])->name('pos.qris-token');
});

// Sale History || Data Transaksi Penjualan Route
Route::middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/sale', [SaleHistoryController::class, 'index'])->name('sale.index');
    Route::get('/sale/{sale}', [SaleHistoryController::class, 'show'])->name('sale.show');
    Route::get('/sale/{sale}/export-to-pdf', [SaleHistoryController::class, 'exportToPdf'])->name('sale.export-to-pdf');
});

// Purcase || Data Transaksi Pembelian Route
Route::middleware(['auth', 'role:admin, warehouse'])->group(function () {
    Route::get('/purcase/search-supplier', [PurcaseController::class, 'searchSupplier'])->name('purcase.search-supplier');
    Route::get('/purcase/search-product', [PurcaseController::class, 'searchProduct'])->name('purcase.search-product');
    Route::resource('purcase', PurcaseController::class);
});

// Data Transaksi Pembayaran Midtrans Route
Route::get('/payment/midtrans', [MidtransTransactionController::class, 'index'])->middleware(['auth', 'role:admin, cashier'])->name('midtrans.index');

// Laporan Route
Route::prefix('reports')->group(function () {

    // Laporan Penjualan Route
    Route::middleware('auth', 'role:admin, cashier')->group(function () {
        Route::get("/sale", [SaleRepotController::class, 'index'])->name('reports.sale.index');
        Route::get("/sale/search-cashier", [SaleRepotController::class, 'searchCashier'])->name('reports.sale.search-cashier');
        Route::get('/sale/print-pdf', [SaleRepotController::class, 'printPdf'])->name('reports.sale.print-pdf');
    });
});

require __DIR__ . '/auth.php';
