<?php

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->warehouse = User::factory()->create(['role' => 'warehouse']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
});

it('allows admin and warehouse to view stock adjustment list', function ($role) {
    $user = User::factory()->create(['role' => $role]);
    StockAdjustment::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('stock-adjustment.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('StockAdjustment/Index')
        ->has('adjustments.data', 3)
    );
})->with(['admin', 'warehouse']);

it('forbids cashier from viewing stock adjustment list', function () {
    $response = $this->actingAs($this->cashier)->get(route('stock-adjustment.index'));
    $response->assertForbidden();
});

it('can store a new stock adjustment via controller', function () {
    $product = Product::factory()->create(['stock' => 100]);

    $response = $this->actingAs($this->admin)->post(route('stock-adjustment.store'), [
        'product_id' => $product->id,
        'new_stock' => 120,
        'reason' => 'Stock opname bulanan',
        'date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('stock-adjustment.index'));
    $response->assertSessionHas('success');

    $product->refresh();
    expect($product->stock)->toBe(120);

    $this->assertDatabaseHas('stock_adjustments', [
        'product_id' => $product->id,
        'new_stock' => 120,
    ]);
});

it('can search products for adjustment', function () {
    $product = Product::factory()->create(['name' => 'Beras Organik']);

    $response = $this->actingAs($this->admin)
        ->getJson(route('stock-adjustment.search-product', ['search' => 'Beras']));

    $response->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonPath('0.label', 'Beras Organik');
});
