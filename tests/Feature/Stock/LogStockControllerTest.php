<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

it('allows admin and warehouse to view stock report', function ($role) {
    $user = User::factory()->create(['role' => $role]);

    $response = actingAs($user)->get(route('reports.stock.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Reports/Stock/Index'));
})->with(['admin', 'warehouse']);

it('denies cashier to view stock report', function () {
    $user = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($user)->get(route('reports.stock.index'));

    $response->assertForbidden();
});

it('can search for categories via api', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Category::factory()->create(['name' => 'Electronics']);

    $response = actingAs($user)->getJson(route('reports.stock.searchCategory', ['search' => 'Elect']));

    $response->assertOk();
    $response->assertJsonPath('message', 'success');
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Electronics');
});

it('can search for suppliers via api', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Supplier::factory()->create(['name' => 'Tech Supplier Inc']);

    $response = actingAs($user)->getJson(route('reports.stock.searchSupplier', ['search' => 'Tech']));

    $response->assertOk();
    $response->assertJsonPath('message', 'success');
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Tech Supplier Inc');
});

it('validates stock report export request', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = actingAs($user)->get(route('reports.stock.export-to-pdf'));
    $response->assertSessionHasErrors(['start_date', 'end_date']);
});

it('can export stock report to pdf', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();
    $purchase = Purchase::factory()->create(['supplier_id' => $supplier->id]);
    PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);

    $response = actingAs($user)->get(route('reports.stock.export-to-pdf', [
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'category' => 'semua-kategori',
        'supplier' => 'semua-supplier',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('can export stock report to excel', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();
    $purchase = Purchase::factory()->create(['supplier_id' => $supplier->id]);
    PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);

    $response = actingAs($user)->get(route('reports.stock.export-to-excel', [
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'category' => 'semua-kategori',
        'supplier' => 'semua-supplier',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
