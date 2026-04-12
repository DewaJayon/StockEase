<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\Sale\PosService;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('does not execute duplicate queries when fetching the cart', function () {
    $user = User::factory()->create();
    actingAs($user);

    $service = new PosService;

    // The first call will create the cart, let's pre-create one to test fetching
    $cart = Sale::create([
        'user_id' => $user->id,
        'total' => 0,
        'payment_method' => 'pending',
        'paid' => 0,
        'change' => 0,
        'date' => now(),
        'status' => 'draft',
    ]);

    // Add an item manually
    $product = Product::factory()->create(['selling_price' => 100]);
    $cart->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 100,
    ]);

    DB::enableQueryLog();

    $fetchedCart = $service->getOrCreateCart();

    $queries = DB::getQueryLog();

    $saleItemQueries = collect($queries)->filter(function ($query) {
        return str_contains($query['query'], 'select * from `sale_items` where `sale_items`.`sale_id`');
    });

    // It should load sale_items exactly once via the eager load 'with()'
    expect($saleItemQueries)->toHaveCount(1);
    expect($fetchedCart->id)->toEqual($cart->id);
});

it('adds a product to the cart without redundant relation queries', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['selling_price' => 500, 'stock' => 10]);

    actingAs($user);

    $service = new PosService;

    // Step 1: Create an empty cart
    $service->getOrCreateCart();

    DB::enableQueryLog();

    // Step 2: Add product to cart
    $result = $service->addToCart($product->id, 2);

    $queries = DB::getQueryLog();

    $saleItemQueries = collect($queries)->filter(function ($query) {
        return str_contains($query['query'], 'select * from `sale_items` where');
    });

    // The eager load 'with' from getOrCreateCart triggers 1 query.
    // We optimized addToCart so it does not do any extra select * from sale_items
    expect($saleItemQueries)->toHaveCount(1)
        ->and($result['total'])->toEqual(1000);

    assertDatabaseHas('sales', [
        'id' => $result['cart']->id,
        'total' => 1000,
    ]);

    assertDatabaseHas('sale_items', [
        'sale_id' => $result['cart']->id,
        'product_id' => $product->id,
        'qty' => 2,
    ]);
});

it('updates product quantity and recalculates total correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['selling_price' => 300, 'stock' => 10]);

    actingAs($user);

    $service = new PosService;

    // Create cart and add product
    $service->addToCart($product->id, 1);

    DB::enableQueryLog();

    // Update quantity
    $result = $service->updateCartItemQty($product->id, 3);

    $queries = DB::getQueryLog();

    // Total should be 3 * 300 = 900
    expect($result['total'])->toEqual(900);

    assertDatabaseHas('sales', [
        'id' => $result['cart']->id,
        'total' => 900,
    ]);

    assertDatabaseHas('sale_items', [
        'sale_id' => $result['cart']->id,
        'product_id' => $product->id,
        'qty' => 3,
    ]);

    // Now remove the item completely using update to 0
    $result2 = $service->updateCartItemQty($product->id, 0);

    expect($result2['total'])->toEqual(0);

    assertDatabaseMissing('sale_items', [
        'sale_id' => $result2['cart']->id,
        'product_id' => $product->id,
    ]);
});

it('removes a product and empties the cart correctly', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['selling_price' => 200, 'stock' => 10]);
    $product2 = Product::factory()->create(['selling_price' => 400, 'stock' => 10]);

    actingAs($user);

    $service = new PosService;

    // Add two products
    $service->addToCart($product1->id, 1); // total 200
    $result = $service->addToCart($product2->id, 2); // total 200 + 800 = 1000

    expect($result['total'])->toEqual(1000);

    // Remove one product
    $result = $service->removeFromCart($product1->id);

    // Total should now be 800
    expect($result['total'])->toEqual(800);

    assertDatabaseMissing('sale_items', [
        'sale_id' => $result['cart']->id,
        'product_id' => $product1->id,
    ]);

    // Empty the cart
    $result = $service->emptyCart();

    expect($result['total'])->toEqual(0);
    expect($result['cart']->saleItems)->toBeEmpty();

    assertDatabaseMissing('sale_items', [
        'sale_id' => $result['cart']->id,
    ]);
});
