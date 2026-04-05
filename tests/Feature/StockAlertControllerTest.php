<?php

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

describe('Stock Alert Notifications', function () {
    it('sends notifications only to admin users', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'cashier']);

        Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);

        $this->getJson(route('low-stock.index'));

        expect($admin1->notifications)->toHaveCount(1);
        expect($admin2->notifications)->toHaveCount(1);
        expect($cashier->notifications)->toHaveCount(0);
    });

    it('sends multiple notifications for multiple low stock products', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $admin = User::factory()->create(['role' => 'admin']);

        Product::factory(3)->create(['stock' => 5, 'alert_stock' => 10]);

        $this->getJson(route('low-stock.index'));

        expect($admin->notifications)->toHaveCount(3);
    });

    it('does not send notifications for normal stock products', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $admin = User::factory()->create(['role' => 'admin']);

        Product::factory()->create(['stock' => 15, 'alert_stock' => 10]);

        $this->getJson(route('low-stock.index'));

        expect($admin->notifications)->toHaveCount(0);
    });

    it('includes product information in notification', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);

        $this->getJson(route('low-stock.index'));

        $notification = $admin->notifications[0];

        expect($notification->data['product_id'])->toBe($product->id);
        expect($notification->data['product_name'])->toBe($product->name);
        expect($notification->data['current_stock'])->toBe(5);
        expect($notification->data['alert_level'])->toBe(10);
    });
});
