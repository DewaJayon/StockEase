<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{admin: User} $this */
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('profit loss report correctly handles pagination while preserving date filters', function () {
    /** @var TestCase&object{admin: User} $this */

    // Create a sale last month (should be excluded)
    Sale::factory()->create([
        'date' => now()->subMonth(),
        'status' => 'completed',
        'total' => 1000,
        'total_cost' => 500,
        'user_id' => $this->admin->id,
    ]);

    // Create sales this month
    $products = Product::factory()->count(15)->create(['purchase_price' => 1000]);
    foreach ($products as $product) {
        $sale = Sale::factory()->create([
            'status' => 'completed',
            'date' => now(),
            'total' => 2000,
            'total_cost' => 1000,
            'user_id' => $this->admin->id,
            'payment_method' => 'cash',
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 2000,
            'cost_price' => 1000,
        ]);
    }

    $startDate = now()->startOfMonth()->format('Y-m-d');
    $endDate = now()->endOfMonth()->format('Y-m-d');

    // Initial request with date filters (using new 'start' and 'end' params)
    $response = $this->actingAs($this->admin)
        ->get(route('reports.profit-loss', [
            'start' => $startDate,
            'end' => $endDate,
            'per_page' => 10,
        ]));

    $response->assertStatus(200);

    // Verify initial data
    $response->assertInertia(fn (Assert $page) => $page
        ->where('summary.total_revenue', fn ($val) => (float) $val == 30000.0) // 15 * 2000
        ->has('productBreakdown.data', 10)
        ->where('productBreakdown.total', 15)
    );

    // Simulate custom date range 2 months ago
    $customStart = now()->subMonths(2)->startOfMonth()->format('Y-m-d');
    $customEnd = now()->subMonths(2)->endOfMonth()->format('Y-m-d');

    // Create sales 2 months ago
    $oldProduct = Product::factory()->create(['purchase_price' => 1000]);
    $oldSale = Sale::factory()->create([
        'status' => 'completed',
        'date' => now()->subMonths(2)->day(15),
        'total' => 5000,
        'total_cost' => 2000,
        'user_id' => $this->admin->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $oldSale->id,
        'product_id' => $oldProduct->id,
        'qty' => 1,
        'price' => 5000,
        'cost_price' => 2000,
    ]);

    // Request for 2 months ago (using new 'start' and 'end' params)
    $responseCustom = $this->actingAs($this->admin)
        ->get(route('reports.profit-loss', [
            'start' => $customStart,
            'end' => $customEnd,
        ]));

    $responseCustom->assertInertia(fn (Assert $page) => $page
        ->where('summary.total_revenue', fn ($val) => (float) $val == 5000.0)
    );

    // Simulate FIXED DataTable search or pagination where date filters are PRESERVED
    $responseFixed = $this->actingAs($this->admin)
        ->get(route('reports.profit-loss', [
            'page' => 1,
            'search' => '',
            'start' => $customStart,
            'end' => $customEnd,
        ]));

    // It should STILL show the data from 2 months ago (5000)
    $responseFixed->assertInertia(fn (Assert $page) => $page
        ->where('summary.total_revenue', fn ($val) => (float) $val == 5000.0)
    );
});
