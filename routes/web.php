<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Media\FileManagerController;
use App\Http\Controllers\Payment\MidtransTransactionController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\PriceController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Purchase\PurchaseReportController;
use App\Http\Controllers\Purchase\SupplierController;
use App\Http\Controllers\Report\ProfitLossReportController;
use App\Http\Controllers\Sale\PosController;
use App\Http\Controllers\Sale\SaleHistoryController;
use App\Http\Controllers\Sale\SaleReportController;
use App\Http\Controllers\Stock\ExpiryReportController;
use App\Http\Controllers\Stock\LogStockController;
use App\Http\Controllers\Stock\StockAdjustmentController;
use App\Http\Controllers\Stock\StockReportController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

// Auth Route
require __DIR__.'/auth.php';

// dashboard & general route
Route::middleware('auth')->group(function () {

    // Dashboard route
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // profile route
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/photo-profile', [ProfileController::class, 'storePhotoProfile'])->name('profile.photo-profile');
    Route::delete('/profile/photo-profile', [ProfileController::class, 'destroyPhotoProfile'])->name('profile.destroy-photo-profile');

    // file manager route
    Route::get('/file-manager', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::get('/file-manager/download', [FileManagerController::class, 'download'])->name('file-manager.download');
    Route::delete('/file-manager', [FileManagerController::class, 'destroy'])->name('file-manager.destroy');
    Route::post('/file-manager/upload', [FileManagerController::class, 'store'])->name('file-manager.store');
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
    Route::get('product/{product}/price', [PriceController::class, 'edit'])->name('product.price.edit');
    Route::patch('product/{product}/price', [PriceController::class, 'update'])->name('product.price.update');
    Route::resource('product', ProductController::class);
    Route::resource('unit', UnitController::class);
});

// POS Route
Route::prefix('pos')->middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::patch('/change-qty', [PosController::class, 'changeQty'])->name('pos.change-qty');
    Route::post('/add-to-cart', [PosController::class, 'addToCart'])->name('pos.add-to-cart');
    Route::post('/add-to-cart-barcode', [PosController::class, 'addToCartByBarcode'])->name('pos.add-to-cart-barcode');
    Route::get('/get-cart', [PosController::class, 'getCartJson'])->name('pos.get-cart');
    Route::delete('/remove-from-cart', [PosController::class, 'removeFromCart'])->name('pos.remove-from-cart');
    Route::delete('/empty-cart', [PosController::class, 'emptyCart'])->name('pos.empty-cart');
    Route::put('/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/qris-token', [PaymentController::class, 'createMidtransTransaction'])->name('pos.qris-token');
});

// Sale History || Data Transaksi Penjualan Route
Route::middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/sale', [SaleHistoryController::class, 'index'])->name('sale.index');
    Route::get('/sale/{sale}', [SaleHistoryController::class, 'show'])->name('sale.show');
    Route::get('/sale/{sale}/export-to-pdf', [SaleHistoryController::class, 'exportToPdf'])->name('sale.export-to-pdf');
});

// Purchase || Data Transaksi Pembelian Route
Route::middleware(['auth', 'role:admin, warehouse'])->group(function () {
    Route::get('/purchase/search-supplier', [PurchaseController::class, 'searchSupplier'])->name('purchase.search-supplier');
    Route::get('/purchase/search-product', [PurchaseController::class, 'searchProduct'])->name('purchase.search-product');
    Route::resource('purchase', PurchaseController::class);
});

// Data Transaksi Pembayaran Midtrans Route
Route::get('/payment/midtrans', [MidtransTransactionController::class, 'index'])->middleware(['auth', 'role:admin, cashier'])->name('midtrans.index');

// Laporan Route
Route::prefix('reports')->group(function () {

    // Laporan Penjualan Route
    Route::middleware('auth', 'role:admin, cashier')->group(function () {
        Route::get('/sale', [SaleReportController::class, 'index'])->name('reports.sale.index');
        Route::get('/sale/search-cashier', [SaleReportController::class, 'searchCashier'])->name('reports.sale.search-cashier');
        Route::get('/sale/export-to-pdf', [SaleReportController::class, 'exportToPdf'])->name('reports.sale.export-to-pdf');
        Route::get('/sale/export-to-excel', [SaleReportController::class, 'exportToExcel'])->name('reports.sale.export-to-excel');
    });

    // Laporan Pembelian Route
    Route::middleware('auth', 'role:admin, warehouse')->group(function () {
        Route::get('/purchase', [PurchaseReportController::class, 'index'])->name('reports.purchase.index');
        Route::get('/purchase/search-supplier', [PurchaseReportController::class, 'searchSupplier'])->name('reports.purchase.search-supplier');
        Route::get('/purchase/search-user', [PurchaseReportController::class, 'searchUser'])->name('reports.purchase.search-user');
        Route::get('/purchase/export-to-pdf', [PurchaseReportController::class, 'exportToPdf'])->name('reports.purchase.export-to-pdf');
        Route::get('/purchase/export-to-excel', [PurchaseReportController::class, 'exportToExcel'])->name('reports.purchase.export-to-excel');
    });

    // Route Laporan Stock
    Route::middleware('auth', 'role:admin, warehouse')->group(function () {
        Route::get('/stock', [StockReportController::class, 'index'])->name('reports.stock.index');
        Route::get('/stock/searchCategory', [StockReportController::class, 'searchCategory'])->name('reports.stock.searchCategory');
        Route::get('/stock/searchSupplier', [StockReportController::class, 'searchSupplier'])->name('reports.stock.searchSupplier');
        Route::get('/stock/export-to-pdf', [StockReportController::class, 'exportToPdf'])->name('reports.stock.export-to-pdf');
        Route::get('/stock/export-to-excel', [StockReportController::class, 'exportToExcel'])->name('reports.stock.export-to-excel');
    });

    // Laporan Laba/Rugi Route
    Route::middleware('auth', 'role:admin')->group(function () {
        Route::get('/profit-loss', [ProfitLossReportController::class, 'index'])->name('reports.profit-loss');
    });
});

// Log Stock
Route::middleware('auth', 'role:admin, warehouse')->group(function () {
    Route::get('/log-stock', [LogStockController::class, 'index'])->name('log-stock.index');
});

// Stock Adjustment
Route::middleware(['auth', 'role:admin, warehouse'])->group(function () {
    Route::get('/stock-adjustment', [StockAdjustmentController::class, 'index'])->name('stock-adjustment.index');
    Route::post('/stock-adjustment', [StockAdjustmentController::class, 'store'])->name('stock-adjustment.store');
    Route::get('/stock-adjustment/search-product', [StockAdjustmentController::class, 'searchProduct'])->name('stock-adjustment.search-product');
});

// Laporan Kedaluwarsa Route
Route::middleware('auth', 'role:admin, warehouse')->group(function () {
    Route::get('/reports/expiry', [ExpiryReportController::class, 'index'])->name('reports.expiry.index');
});
