<?php

use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;


// Webhook notification midtrans
Route::post('/midtrans/notification', [PaymentController::class, 'midtransNotification'])->name('midtrans.notification');

// Alert/Notification Stock Route
Route::get('/low-stock', [StockAlertController::class, 'index'])->name('low-stock.index');
