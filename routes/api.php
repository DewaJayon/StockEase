<?php

use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;

// Webhook notification midtrans
Route::post('/midtrans/notification', [PaymentController::class, 'midtransNotification'])->name('midtrans.notification');

// Alert/Notification Stock Route
Route::get('/low-stock', [StockAlertController::class, 'index'])->name('low-stock.index');

// Notification routes - requires authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
