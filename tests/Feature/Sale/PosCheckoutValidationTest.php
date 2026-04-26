<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('fails when paid amount is out of range', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);

    $sale = Sale::factory()->create([
        'user_id' => $user->id,
        'total' => 1000,
        'status' => 'draft',
    ]);

    // Simulating checkout with a very large number using PUT as defined in routes
    $response = actingAs($user)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'paid' => '9999999999999999999999999999999999', // Very large number
            'customer_name' => 'Test Customer',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['paid']);
});
