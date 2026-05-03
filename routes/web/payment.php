<?php

use App\Http\Controllers\Payment\MidtransTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/payment/midtrans', [MidtransTransactionController::class, 'index'])
    ->middleware(['auth', 'role:admin, cashier'])
    ->name('midtrans.index');
