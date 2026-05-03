<?php

use App\Actions\Sale\RecalculateSaleTotal;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Sale\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

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

    $response->assertInertia(
        fn ($page) => $page
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

it('prevents adding the same product if the resulting qty exceeds stock', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['stock' => 5]);

    // First add 3 (ok)
    actingAs($cashier)->postJson(route('pos.add-to-cart'), [
        'product_id' => $product->id,
        'qty' => 3,
    ])->assertOk();

    // Second add 3 (should fail; would total 6 > 5)
    $response = actingAs($cashier)->postJson(route('pos.add-to-cart'), [
        'product_id' => $product->id,
        'qty' => 3,
    ]);

    $response->assertBadRequest();
    $response->assertJsonPath('message', 'Stok produk tidak mencukupi');

    assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'qty' => 3,
    ]);
});

it('returns error if product not found by barcode', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($cashier)
        ->postJson(route('pos.add-to-cart-barcode'), [
            'barcode' => '99999999',
        ]);

    $response->assertUnprocessable();
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

    $response->assertBadRequest();
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

    $response->assertBadRequest();
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

    $response->assertBadRequest();
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

    $response->assertBadRequest();
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

    $response->assertBadRequest();
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

    /** @var TestCase&object{cashier: User} $this */
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

it('does not execute duplicate queries when fetching the cart', function () {
    /** @var User $user */
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

    /** @var User $user */
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
    /** @var User $user */
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
    /** @var User $user */
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

it('prevents checkout if paid amount is less than total expect to be draft', function () {
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

    resolve(RecalculateSaleTotal::class)->execute($sale); // total 100,000

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
