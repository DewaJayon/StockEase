<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->supplier = Supplier::factory()->create();
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock' => 100,
    ]);

    // Create a purchase to make product appear in stock report
    $purchase = Purchase::factory()->create([
        'supplier_id' => $this->supplier->id,
        'user_id' => $this->admin->id,
        'date' => Carbon::now(),
    ]);
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $this->product->id,
        'qty' => 100,
        'price' => 1000,
    ]);

    // Create a sale for sale report
    $sale = Sale::factory()->create([
        'user_id' => $this->admin->id,
        'date' => Carbon::now(),
        'status' => 'completed',
        'payment_method' => 'cash',
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $this->product->id,
        'qty' => 1,
        'price' => 2000,
    ]);
});

describe('Report Exports Validation', function () {
    it('validates stock report export request', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.stock.export-to-pdf', [
                'start_date' => '',
                'end_date' => '',
                'category' => '',
                'supplier' => '',
            ]));

        $response->assertSessionHasErrors(['start_date', 'end_date', 'category', 'supplier']);
    });

    it('validates sale report export request', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.sale.export-to-pdf', [
                'start_date' => '',
                'end_date' => '',
                'cashier' => '',
                'payment' => '',
            ]));

        $response->assertSessionHasErrors(['start_date', 'end_date', 'cashier', 'payment']);
    });

    it('validates purchase report export request', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.purchase.export-to-pdf', [
                'start_date' => '',
                'end_date' => '',
                'supplier' => '',
                'user' => '',
            ]));

        $response->assertSessionHasErrors(['start_date', 'end_date', 'supplier', 'user']);
    });
});

describe('Report Exports Success', function () {
    it('can export stock report to pdf', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.stock.export-to-pdf', [
                'start_date' => Carbon::now()->subDay()->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'category' => 'semua-kategori',
                'supplier' => 'semua-supplier',
            ]));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    });

    it('can export sale report to pdf', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.sale.export-to-pdf', [
                'start_date' => Carbon::now()->subDay()->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'cashier' => 'semua-cashier',
                'payment' => 'semua-metode',
            ]));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    });

    it('can export purchase report to pdf', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.purchase.export-to-pdf', [
                'start_date' => Carbon::now()->subDay()->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'supplier' => 'semua-supplier',
                'user' => 'semua-user',
            ]));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    });
});
