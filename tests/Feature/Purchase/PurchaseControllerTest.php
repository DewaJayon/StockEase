<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        fn ($page) => $page
            ->component('Purchase/Index')
            ->has('purchases.data', 3)
    );
})->with(['admin', 'warehouse']);

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
