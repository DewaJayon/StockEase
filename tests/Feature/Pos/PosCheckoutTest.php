<?php

use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('prevents checkout if paid amount is less than total', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'cashier']);
    actingAs($user);

    $product = Product::factory()->create(['selling_price' => 50000, 'stock' => 10]);

    // Setup Cart manually in draft state
    $sale = Sale::create([
        'user_id' => $user->id,
        'total' => 50000,
        'payment_method' => 'pending',
        'paid' => 0,
        'change' => 0,
        'status' => 'draft',
        'date' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 50000,
    ]);

    // Attack Payload: Try to pay only 1 rupiah
    $response = putJson(route('pos.checkout'), [
        'payment_method' => 'cash',
        'paid' => 1,
    ]);

    // Assert it fails (400 Bad Request)
    $response->assertStatus(400);
    $response->assertJson(['message' => 'Jumlah uang pembayaran kurang dari total belanja.']);

    // Assert status is still draft
    expect($sale->refresh()->status)->toBe('draft');
});

it('successfully completes cash checkout and reduces stock', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'cashier']);
    actingAs($user);

    $product = Product::factory()->create(['selling_price' => 50000, 'stock' => 10]);

    $sale = Sale::create([
        'user_id' => $user->id,
        'total' => 50000,
        'payment_method' => 'pending',
        'paid' => 0,
        'change' => 0,
        'status' => 'draft',
        'date' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 50000,
    ]);

    $sale->calculateTotal(); // total 100,000

    $response = putJson(route('pos.checkout'), [
        'payment_method' => 'cash',
        'paid' => 120000,
    ]);

    $response->assertSuccessful();

    $sale->refresh();
    expect($sale->status)->toBe('completed');
    expect($sale->paid)->toEqual(120000.0);
    expect($sale->change)->toEqual(20000.0);

    // Assert stock reduced: 10 - 2 = 8
    expect($product->refresh()->stock)->toBe(8);
});

it('sets QRIS checkout to pending and does not reduce stock immediately', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'cashier']);
    actingAs($user);

    $product = Product::factory()->create(['selling_price' => 50000, 'stock' => 10]);

    $sale = Sale::create([
        'user_id' => $user->id,
        'total' => 50000,
        'payment_method' => 'pending',
        'paid' => 0,
        'change' => 0,
        'status' => 'draft',
        'date' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 50000,
    ]);

    $response = putJson(route('pos.checkout'), [
        'payment_method' => 'qris',
        'order_id' => 'TRX-123',
    ]);

    $response->assertSuccessful();

    $sale->refresh();
    expect($sale->status)->toBe('pending');
    expect($product->refresh()->stock)->toBe(10); // Not reduced yet

    // Assert PaymentTransaction created
    $transaction = PaymentTransaction::where('external_id', 'TRX-123')->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe('pending');
});

it('completes sale via midtrans webhook', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['selling_price' => 50000, 'stock' => 10]);

    $sale = Sale::create([
        'user_id' => $user->id,
        'total' => 50000,
        'payment_method' => 'qris',
        'paid' => 0,
        'change' => 0,
        'status' => 'pending',
        'date' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 50000,
    ]);

    $paymentTransaction = PaymentTransaction::create([
        'sale_id' => $sale->id,
        'gateway' => 'midtrans',
        'external_id' => 'TRX-999',
        'status' => 'pending',
        'amount' => 50000,
        'payment_type' => 'qris',
    ]);

    // Mock Midtrans Webhook
    $serverKey = config('midtrans.server_key');
    $orderId = 'TRX-999';
    $statusCode = '200';
    $grossAmount = '50000.00';
    $validSignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $validSignatureKey,
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
    ];

    $response = postJson(route('midtrans.notification'), $payload);

    $response->assertStatus(200);

    // Verify Sale and Stock
    expect($sale->refresh()->status)->toBe('completed');
    expect($product->refresh()->stock)->toBe(9);
    expect($paymentTransaction->refresh()->status)->toBe('settlement');
});
