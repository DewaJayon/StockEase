<?php

namespace Tests\Feature\Stock;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase&object{user1: User, user2: User, product: Product} $this */
    $this->user1 = User::factory()->create(['role' => 'admin']);
    $this->user2 = User::factory()->create(['role' => 'cashier']);
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'stock' => 10,
        'alert_stock' => 5,
    ]);
});

it('automatically triggers stock alert notification when stock falls below threshold', function () {
    /** @var TestCase&object{user1: User, user2: User, product: Product} $this */
    Notification::fake();

    $saleItems = collect([
        new SaleItem([
            'product_id' => $this->product->id,
            'qty' => 6,
            'sale_id' => 1,
        ]),
    ]);

    Product::reduceStockFromSaleItems($saleItems);

    $this->product->refresh();
    expect($this->product->stock)->toBe(4);

    Notification::assertSentTo(
        [$this->user1, $this->user2],
        StockAlertNotification::class,
        function ($notification) {
            return $notification->toArray($this->user1)['product_id'] === $this->product->id;
        }
    );
});

it('does not trigger notification if stock remains above threshold', function () {
    /** @var TestCase&object{user1: User, user2: User, product: Product} $this */
    Notification::fake();

    $saleItems = collect([
        new SaleItem([
            'product_id' => $this->product->id,
            'qty' => 2,
            'sale_id' => 1,
        ]),
    ]);

    Product::reduceStockFromSaleItems($saleItems);

    $this->product->refresh();
    expect($this->product->stock)->toBe(8);

    Notification::assertNotSentTo(
        [$this->user1, $this->user2],
        StockAlertNotification::class
    );
});

it('prevents duplicate unread notifications for the same product', function () {
    /** @var TestCase&object{user1: User, user2: User, product: Product} $this */

    // First reduction triggers notification
    $saleItems1 = collect([
        new SaleItem([
            'product_id' => $this->product->id,
            'qty' => 6,
            'sale_id' => 1,
        ]),
    ]);
    Product::reduceStockFromSaleItems($saleItems1);

    expect($this->user1->unreadNotifications)->toHaveCount(1);

    // Second reduction should NOT trigger another notification because there is an unread one
    $saleItems2 = collect([
        new SaleItem([
            'product_id' => $this->product->id,
            'qty' => 1,
            'sale_id' => 2,
        ]),
    ]);
    Product::reduceStockFromSaleItems($saleItems2);

    expect($this->user1->unreadNotifications()->count())->toBe(1);
});
