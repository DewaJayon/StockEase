<?php

namespace Tests\Feature\Stock;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
});

describe('Low Stock Products', function () {
    it('returns only low stock products', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);
        Product::factory()->create(['stock' => 15, 'alert_stock' => 10]);

        $response = $this->getJson(route('low-stock.index'));

        $response->assertSuccessful();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.stock', 5);
    });

    it('returns array of products', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);

        $response = $this->getJson(route('low-stock.index'));

        $response->assertSuccessful();
        expect(is_array($response->json()))->toBeTrue();
    });

    it('rejects non-json requests', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $response = $this->get(route('low-stock.index'));

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthorized']);
    });
});

describe('Stock Alert Notifications (Architectural Check)', function () {
    it('does not send notifications when fetching alerts', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $admin = User::factory()->create(['role' => 'admin']);
        Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);

        $this->getJson(route('low-stock.index'));

        expect($admin->notifications)->toHaveCount(0);
    });
});
