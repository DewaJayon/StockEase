<?php

use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\PromotionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::resource('category', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('promotions', PromotionController::class)->only(['index', 'store', 'update', 'destroy']);
});
