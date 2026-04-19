<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
    $this->product = Product::factory()->create();

    // Create a completed sale
    $this->sale = Sale::factory()->create([
        'user_id' => $this->cashier->id,
        'date' => Carbon::now()->toDateString(),
        'status' => 'completed',
        'payment_method' => 'cash',
        'total' => 1000,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $this->sale->id,
        'product_id' => $this->product->id,
        'qty' => 1,
        'price' => 1000,
    ]);
});

it('allows admin to view sale report index', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.sale.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Reports/Sale/Index'));
});

it('can filter sale report', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.sale.index', [
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'cashier' => $this->cashier->id,
            'payment' => 'cash',
        ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('sales.sales', 1)
        ->where('sales.sumTotalSale', 1000)
    );
});

it('can search cashier for report filters', function () {
    $response = $this->actingAs($this->admin)
        ->getJson(route('reports.sale.search-cashier', ['search' => $this->cashier->name]));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.value', $this->cashier->id);
});

it('can export sale report to excel', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('reports.sale.export-to-excel', [
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
            'cashier' => 'semua-cashier',
            'payment' => 'semua-metode',
        ]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
