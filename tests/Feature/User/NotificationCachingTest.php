<?php

namespace Tests\Feature\User;

use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Inertia\Testing\AssertableInertia as Assert;

describe('Notification Caching with Inertia', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create();
    });

    it('shares notifications prop via middleware', function () {
        $this->actingAs($this->user)
            ->get('/')
            ->assertInertia(
                fn (Assert $page) => $page->has('notifications')
            );
    });

    it('notifications prop contains formatted notifications', function () {
        $this->user->notify(new StockAlertNotification($this->product, 2, 5));

        $this->actingAs($this->user)->get('/')
            ->assertInertia(
                fn (Assert $page) => $page->has('notifications', 1)
            );
    });

    it('includes product slug in notification', function () {
        $this->user->notify(new StockAlertNotification($this->product, 2, 5));

        $this->actingAs($this->user)->get('/')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('notifications', 1)
                    ->where('notifications.0.slug', $this->product->slug)
            );
    });

    it('formats notification data correctly', function () {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $user->notify(new StockAlertNotification($product, 2, 5));

        $this->actingAs($user)->get('/')
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('notifications.0.product_id', $product->id)
                    ->has('notifications.0.alert_level')
                    ->has('notifications.0.current_stock')
            );
    });

    it('marks notifications as read in array', function () {
        $this->user->notify(new StockAlertNotification($this->product, 2, 5));

        $notification = $this->user->notifications()->first();
        $notification->markAsRead();

        $this->actingAs($this->user)->get('/')
            ->assertInertia(
                fn (Assert $page) => $page->has('notifications.0.read_at')
            );
    });

    it('limits notifications to most recent 50', function () {
        for ($i = 0; $i < 60; $i++) {
            $this->user->notify(new StockAlertNotification($this->product, 2, 5));
        }

        $this->actingAs($this->user)->get('/')
            ->assertInertia(
                fn (Assert $page) => $page->has('notifications', 50)
            );
    });
});

describe('Notification API Endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create();
    });

    it('provides API endpoint for manual refresh', function () {
        $this->user->notify(new StockAlertNotification($this->product, 2, 5));

        $this->actingAs($this->user)
            ->getJson(route('notifications.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('pagination works on API endpoint', function () {
        for ($i = 0; $i < 15; $i++) {
            $this->user->notify(new StockAlertNotification($this->product, 2, 5));
        }

        $this->actingAs($this->user)
            ->getJson(route('notifications.index'))
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('total', 15);
    });

    it('requires authentication', function () {
        $this->getJson(route('notifications.index'))
            ->assertUnauthorized();
    });
});
