<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin and warehouse to view purchases', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    Purchase::factory()->count(3)->create();

    $response = actingAs($user)->get(route('purchase.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 3)
    );
})->with(['admin', 'warehouse']);

it('filters purchases by date range', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    // Create purchases on different dates
    Purchase::factory()->create(['date' => '2024-04-01']);
    Purchase::factory()->create(['date' => '2024-04-15']);
    Purchase::factory()->create(['date' => '2024-04-30']);
    Purchase::factory()->create(['date' => '2024-05-01']);

    // Filter for April 2024
    $response = actingAs($admin)->get(route('purchase.index', [
        'start' => '2024-04-01',
        'end' => '2024-04-30',
    ]));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 3)
    );

    // Filter for middle of April
    $response = actingAs($admin)->get(route('purchase.index', [
        'start' => '2024-04-10',
        'end' => '2024-04-20',
    ]));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 1)
    );
});

it('searches purchases by supplier name', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $supplier1 = Supplier::factory()->create(['name' => 'Supplier ABC']);
    $supplier2 = Supplier::factory()->create(['name' => 'Vendor XYZ']);

    Purchase::factory()->create(['supplier_id' => $supplier1->id]);
    Purchase::factory()->create(['supplier_id' => $supplier2->id]);

    $response = actingAs($admin)->get(route('purchase.index', ['search' => 'ABC']));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 1)
            ->where('purchases.data.0.supplier.name', 'Supplier ABC')
    );
});

it('searches purchases by product name', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $product1 = Product::factory()->create(['name' => 'Laptop Pro']);
    $product2 = Product::factory()->create(['name' => 'Mouse Wireless']);

    $purchase1 = Purchase::factory()->create();
    $purchase1->purchaseItems()->create([
        'product_id' => $product1->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $purchase2 = Purchase::factory()->create();
    $purchase2->purchaseItems()->create([
        'product_id' => $product2->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $response = actingAs($admin)->get(route('purchase.index', ['search' => 'Laptop']));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 1)
            ->has('purchases.data.0.purchase_items', 1)
            ->where('purchases.data.0.purchase_items.0.product.name', 'Laptop Pro')
    );
});

it('handles pagination in purchase index', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    Purchase::factory()->count(15)->create();

    $response = actingAs($admin)->get(route('purchase.index', ['per_page' => 5]));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 5)
            ->where('purchases.total', 15)
            ->where('purchases.per_page', 5)
    );
});

it('allows admin to create a purchase and increments stock', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create(['stock' => 10]);
    $product2 = Product::factory()->create(['stock' => 5]);

    $response = actingAs($admin)
        ->post(route('purchase.store'), [
            'supplier_id' => $supplier->id,
            'date' => '2024-04-04',
            'product_items' => [
                [
                    'product_id' => $product1->id,
                    'qty' => 5,
                    'price' => 1000,
                    'selling_price' => 2000,
                ],
                [
                    'product_id' => $product2->id,
                    'qty' => 10,
                    'price' => 500,
                    'selling_price' => 1000,
                ],
            ],
        ]);

    $response->assertRedirect(route('purchase.index'));

    assertDatabaseHas('purchases', ['supplier_id' => $supplier->id]);

    $product1->refresh();
    $product2->refresh();

    expect($product1->stock)->toBe(15);
    expect($product2->stock)->toBe(15);

    assertDatabaseHas('stock_logs', [
        'product_id' => $product1->id,
        'qty' => 5,
        'type' => 'in',
    ]);
});

it('allows admin to delete a purchase and decrements stock', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $purchase = Purchase::factory()->create();
    $product = Product::factory()->create(['stock' => 20]);
    $purchase->purchaseItems()->create([
        'product_id' => $product->id,
        'qty' => 5,
        'price' => 1000,
    ]);

    $response = actingAs($admin)
        ->delete(route('purchase.destroy', $purchase));

    $response->assertRedirect(route('purchase.index'));
    assertDatabaseMissing('purchases', ['id' => $purchase->id]);

    $product->refresh();
    expect($product->stock)->toBe(15);

    assertDatabaseHas('stock_logs', [
        'product_id' => $product->id,
        'qty' => 5,
        'type' => 'out',
    ]);
});
