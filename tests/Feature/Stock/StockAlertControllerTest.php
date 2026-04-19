<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns unauthorized if not json request', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('low-stock.index'));

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

it('returns low stock products in json format', function () {
    $user = User::factory()->create();
    Product::factory()->create(['stock' => 2, 'alert_stock' => 5, 'name' => 'Low Item']);
    Product::factory()->create(['stock' => 10, 'alert_stock' => 5, 'name' => 'High Item']);

    $response = $this->actingAs($user)->getJson(route('low-stock.index'));

    $response->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Low Item');
});
