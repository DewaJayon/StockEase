<?php

use App\Http\Controllers\Dashboard\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/midtrans/notification', [PaymentController::class, 'midtransNotification'])->name('midtrans.notification');
