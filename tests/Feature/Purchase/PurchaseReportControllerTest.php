<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->warehouse = User::factory()->create(['role' => 'warehouse']);
    $this->supplier = Supplier::factory()->create();
    $this->product = Product::factory()->create();

    // Create a purchase
    $this->purchase = Purchase::factory()->create([
        'user_id' => $this->warehouse->id,
        'supplier_id' => $this->supplier->id,
        'date' => Carbon::now()->toDateString(),
        'total' => 5000,
    ]);

    PurchaseItem::factory()->create([
        'purchase_id' => $this->purchase->id,
        'product_id' => $this->product->id,
        'qty' => 5,
        'price' => 1000,
    ]);
});

it('allows admin to view purchase report index', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.purchase.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Reports/Purchase/Index'));
});

it('can filter purchase report', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.purchase.index', [
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'supplier' => $this->supplier->id,
            'user' => $this->warehouse->id,
        ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('filters.filters', 1)
        ->where('filters.sumTotalPurchase', 5000)
    );
});

it('can search supplier for report filters', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('reports.purchase.search-supplier', ['search' => $this->supplier->name]));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.value', $this->supplier->id);
});

it('can search user for report filters', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('reports.purchase.search-user', ['search' => $this->warehouse->name]));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.value', $this->warehouse->id);
});

it('can export purchase report to excel', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.purchase.export-to-excel', [
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'supplier' => 'semua-supplier',
            'user' => 'semua-user',
        ]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
