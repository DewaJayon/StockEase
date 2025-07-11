<?php

use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\PosController;
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
Route::middleware(['auth', 'role:admin, warehouse',])->group(function () {
    Route::resource('supplier', SupplierController::class);
    Route::resource('product', ProductController::class);
});

// POS Route
Route::prefix('pos')->middleware('auth', 'role:admin, cashier')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
});

require __DIR__ . '/auth.php';
