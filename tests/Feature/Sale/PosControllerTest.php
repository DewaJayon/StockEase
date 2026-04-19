<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Access Tests
|--------------------------------------------------------------------------
*/

it('allows admin and cashier to access POS', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);

    $response = actingAs($user)->get(route('pos.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Pos/Index'));
})->with(['admin', 'cashier']);

it('denies warehouse role from accessing POS', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'warehouse']);

    $response = actingAs($user)->get(route('pos.index'));

    $response->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Cart Operations Tests
|--------------------------------------------------------------------------
*/

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

it('can get cart data as JSON', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft']);

    $response = actingAs($cashier)->getJson(route('pos.get-cart'));

    $response->assertOk();
    $response->assertJsonPath('cart.id', $sale->id);
});

it('can add product to cart normally', function () {
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

it('can add product to cart by barcode', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10, 'barcode' => '88888888']);

    $response = actingAs($cashier)
        ->postJson(route('pos.add-to-cart-barcode'), [
            'barcode' => '88888888',
        ]);

    $response->assertOk();
    assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'qty' => 1,
    ]);
});

it('increases quantity when adding the same product multiple times', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10]);

    // Add first time
    actingAs($cashier)->postJson(route('pos.add-to-cart'), ['product_id' => $product->id]);

    // Add second time
    $response = actingAs($cashier)->postJson(route('pos.add-to-cart'), ['product_id' => $product->id]);

    $response->assertOk();
    assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
});

it('returns error if product not found by barcode', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($cashier)
        ->postJson(route('pos.add-to-cart-barcode'), [
            'barcode' => '99999999',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['barcode']);
});

it('prevents adding product with zero stock', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 0]);

    $response = actingAs($cashier)
        ->postJson(route('pos.add-to-cart'), [
            'product_id' => $product->id,
        ]);

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Stok produk habis');
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

it('prevents setting qty higher than available stock', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 5]);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft']);
    $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price' => $product->selling_price,
    ]);

    $response = actingAs($cashier)
        ->patchJson(route('pos.change-qty'), [
            'product_id' => $product->id,
            'qty' => 10,
        ]);

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Stok produk tidak mencukupi');
});

it('can remove product from cart using remove-from-cart route', function () {
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
        ->deleteJson(route('pos.remove-from-cart'), [
            'product_id' => $product->id,
        ]);

    $response->assertOk();
    assertDatabaseMissing('sale_items', ['id' => $saleItem->id]);
});

it('can remove product from cart by setting qty to zero via change-qty', function () {
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

it('can empty the entire cart', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product1 = Product::factory()->create(['stock' => 10]);
    $product2 = Product::factory()->create(['stock' => 10]);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft']);

    $sale->saleItems()->createMany([
        ['product_id' => $product1->id, 'qty' => 1, 'price' => 100],
        ['product_id' => $product2->id, 'qty' => 1, 'price' => 100],
    ]);

    $response = actingAs($cashier)->deleteJson(route('pos.empty-cart'));

    $response->assertOk();
    assertDatabaseMissing('sale_items', ['sale_id' => $sale->id]);
});

/*
|--------------------------------------------------------------------------
| Checkout Tests
|--------------------------------------------------------------------------
*/

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

it('can checkout with QRIS and creates a payment transaction', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 10]);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft', 'total' => 5000]);
    $sale->saleItems()->create([
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 5000,
    ]);

    $response = actingAs($cashier)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'qris',
            'customer_name' => 'QRIS Customer',
            'order_id' => 'TRX-12345',
        ]);

    $response->assertOk();
    assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => 'pending',
        'payment_method' => 'qris',
    ]);

    assertDatabaseHas('payment_transactions', [
        'sale_id' => $sale->id,
        'external_id' => 'TRX-12345',
        'status' => 'pending',
        'payment_type' => 'qris',
    ]);
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

it('prevents checkout if paid amount is less than total', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft', 'total' => 1000]);
    $sale->saleItems()->create([
        'product_id' => Product::factory()->create(['stock' => 10])->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $response = actingAs($cashier)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'paid' => 500, // Less than 1000
        ]);

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Jumlah uang pembayaran kurang dari total belanja.');
});

it('prevents checkout with an empty cart', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    // Ensure cart is empty
    $sale = Sale::factory()->create(['user_id' => $cashier->id, 'status' => 'draft', 'total' => 0]);

    $response = actingAs($cashier)
        ->putJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'paid' => 0,
        ]);

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Keranjang kosong, tidak bisa checkout');
});

/*
|--------------------------------------------------------------------------
| End-to-End Workflow Tests
|--------------------------------------------------------------------------
*/

it('can complete a full end-to-end POS cash transaction workflow', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    // Create some initial products
    $productA = Product::factory()->create(['stock' => 50, 'selling_price' => 10000, 'barcode' => '111111']);
    $productB = Product::factory()->create(['stock' => 20, 'selling_price' => 50000]);

    // 1. User visits POS page
    $this->actingAs($cashier)->get(route('pos.index'))->assertSuccessful();

    // 2. Add product A via barcode
    $this->postJson(route('pos.add-to-cart-barcode'), ['barcode' => '111111'])->assertOk();

    // 3. Add product B normally
    $this->postJson(route('pos.add-to-cart'), ['product_id' => $productB->id])->assertOk();

    // 4. Update quantity of product A
    $this->patchJson(route('pos.change-qty'), [
        'product_id' => $productA->id,
        'qty' => 3,
    ])->assertOk();

    // 5. Verify cart totals (3 * 10000) + (1 * 50000) = 80000
    $cartResponse = $this->getJson(route('pos.get-cart'))->assertOk();
    $cartId = $cartResponse->json('cart.id');
    expect($cartResponse->json('cart.total'))->toEqual(80000);

    // 6. Proceed to Checkout
    $this->putJson(route('pos.checkout'), [
        'payment_method' => 'cash',
        'customer_name' => 'E2E Customer',
        'paid' => 100000,
        'change' => 20000,
    ])->assertOk();

    // 7. Verify Database State
    assertDatabaseHas('sales', [
        'id' => $cartId,
        'status' => 'completed',
        'total' => 80000,
        'paid' => 100000,
        'change' => 20000,
        'payment_method' => 'cash',
        'customer_name' => 'E2E Customer',
    ]);

    // Verify stock is reduced
    $productA->refresh();
    $productB->refresh();
    expect($productA->stock)->toBe(47); // 50 - 3
    expect($productB->stock)->toBe(19); // 20 - 1
});
