<?php

use App\Http\Controllers\Dashboard\PaymentController;
use Illuminate\Support\Facades\Route;

// Webhook notification midtrans
Route::post('/midtrans/notification', [PaymentController::class, 'midtransNotification'])->name('midtrans.notification');
