<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin and cashier to access POS', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);

    $response = actingAs($user)->get(route('pos.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Pos/Index'));
})->with(['admin', 'cashier']);

it('provides a new cart with loaded relations on first visit', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    // Ensure no draft cart exists
    Sale::where('user_id', $cashier->id)->where('status', 'draft')->delete();

    $response = actingAs($cashier)->get(route('pos.index'));

    $response->assertSuccessful();

    $response->assertInertia(fn ($page) => $page
        ->component('Pos/Index')
        ->has('cart')
        ->has('cart.sale_items')
        ->where('cart.sale_items', [])
    );
});

it('can add product to cart', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10]);

    $response = actingAs($cashier)
        ->postJson(route('pos.add-to-cart'), [
            'product_id' => $product->id,
        ]);

    $response->assertOk();
    assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'qty' => 1,
    ]);
});

it('can change product qty in cart', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10]);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft']);
    $saleItem = $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price' => $product->selling_price,
    ]);

    $response = actingAs($cashier)
        ->patchJson(route('pos.change-qty'), [
            'product_id' => $product->id,
            'qty' => 5,
        ]);

    $response->assertOk();
    assertDatabaseHas('sale_items', [
        'id' => $saleItem->id,
        'qty' => 5,
    ]);
});

it('can remove product from cart by setting qty to zero', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10]);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft']);
    $saleItem = $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price' => $product->selling_price,
    ]);

    $response = actingAs($cashier)
        ->patchJson(route('pos.change-qty'), [
            'product_id' => $product->id,
            'qty' => 0,
        ]);

    $response->assertOk();
    assertDatabaseMissing('sale_items', ['id' => $saleItem->id]);
});

it('can checkout with cash and reduces stock', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10, 'name' => 'Test Product']);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft', 'total' => 1000]);
    $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 500,
    ]);

    $response = actingAs($cashier)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'customer_name' => 'John Doe',
            'paid' => 1000,
            'change' => 0,
        ]);

    $response->assertOk();
    assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => 'completed',
        'payment_method' => 'cash',
    ]);

    $product->refresh();
    expect($product->stock)->toBe(8);
});

it('prevents checkout if stock is insufficient', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 1, 'name' => 'Low Stock Product']);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft', 'total' => 1000]);
    $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 500,
    ]);

    $response = actingAs($cashier)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'customer_name' => 'John Doe',
            'paid' => 1000,
            'change' => 0,
        ]);

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Stok produk Low Stock Product tidak mencukupi untuk checkout.');
});
