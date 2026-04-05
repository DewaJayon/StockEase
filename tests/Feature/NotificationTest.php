<?php

use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
    $this->product = Product::factory()->create([
        'stock' => 5,
        'alert_stock' => 10,
    ]);
});

describe('Notification Creation', function () {
    it('creates stock alert notification', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));

        expect($this->admin->notifications)->toHaveCount(1);
        expect($this->admin->notifications[0]->read_at)->toBeNull();
    });

    it('has correct notification data', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));

        $notification = $this->admin->notifications[0];

        expect($notification->data['product_id'])->toBe($this->product->id);
        expect($notification->data['product_name'])->toBe($this->product->name);
        expect($notification->data['current_stock'])->toBe($this->product->stock);
        expect($notification->data['alert_level'])->toBe($this->product->alert_stock);
        expect($notification->data['message'])->toContain($this->product->name);
    });
});

describe('Notification API Endpoints', function () {
    it('returns all notifications with pagination', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $this->admin->notify(new StockAlertNotification($this->product));

        $response = $this->actingAs($this->admin)->getJson(route('notifications.index'));

        $response->assertSuccessful();
        $response->assertJsonCount(2, 'data');
    });

    it('requires authentication to get notifications', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $response = $this->getJson(route('notifications.index'));

        $response->assertUnauthorized();
    });
});

describe('Mark Notifications as Read', function () {
    it('marks single notification as read', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $notification = $this->admin->notifications[0];

        $response = $this->actingAs($this->admin)->postJson(
            route('notifications.read', ['id' => $notification->id])
        );

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);

        $notification->refresh();
        expect($notification->read_at)->not->toBeNull();
    });

    it('marks all notifications as read', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $this->admin->notify(new StockAlertNotification($this->product));

        $response = $this->actingAs($this->admin)->postJson(
            route('notifications.read-all')
        );

        $response->assertSuccessful();

        $unreadCount = $this->admin->notifications()->whereNull('read_at')->count();
        expect($unreadCount)->toBe(0);
    });

    it('prevents unauthorized user from marking as read', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $notification = $this->admin->notifications[0];

        $response = $this->actingAs($this->cashier)->postJson(
            route('notifications.read', ['id' => $notification->id])
        );

        $response->assertNotFound();
    });
});

describe('Delete Notifications', function () {
    it('deletes notification', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $notification = $this->admin->notifications[0];
        $notificationId = $notification->id;

        $response = $this->actingAs($this->admin)->deleteJson(
            route('notifications.destroy', ['id' => $notificationId])
        );

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);

        expect($this->admin->notifications()->get())->toHaveCount(0);
    });

    it('prevents unauthorized user from deleting', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        $notification = $this->admin->notifications[0];

        $response = $this->actingAs($this->cashier)->deleteJson(
            route('notifications.destroy', ['id' => $notification->id])
        );

        $response->assertNotFound();
    });
});

describe('Notification Pagination & Sorting', function () {
    it('paginates notifications with limit of 10', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        for ($i = 0; $i < 15; $i++) {
            $this->admin->notify(new StockAlertNotification($this->product));
        }

        $response = $this->actingAs($this->admin)->getJson(route('notifications.index'));

        $response->assertSuccessful();
        $response->assertJsonCount(10, 'data');
        expect($response->json('total'))->toBe(15);
    });

    it('sorts notifications by newest first', function () {
        /** @var TestCase&object{admin: User, cashier: User, product: Product} $this */
        $this->admin->notify(new StockAlertNotification($this->product));
        sleep(1);
        $this->admin->notify(new StockAlertNotification($this->product));

        $response = $this->actingAs($this->admin)->getJson(route('notifications.index'));

        $notifications = $response->json('data');
        $firstCreatedAt = strtotime($notifications[0]['created_at']);
        $secondCreatedAt = strtotime($notifications[1]['created_at']);

        expect($firstCreatedAt)->toBeGreaterThan($secondCreatedAt);
    });
});
