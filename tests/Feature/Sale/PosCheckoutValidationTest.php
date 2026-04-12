<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cashier']);
});

it('fails when paid amount is out of range', function () {
    $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);

    $sale = Sale::factory()->create([
        'user_id' => $this->user->id,
        'total' => 1000,
        'status' => 'draft',
    ]);

    // Simulating checkout with a very large number using PUT as defined in routes
    $response = $this->actingAs($this->user)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'paid' => '9999999999999999999999999999999999', // Very large number
            'customer_name' => 'Test Customer',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['paid']);
});
