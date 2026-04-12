<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('loads notifications without N+1 queries when accessing inertia pages', function () {
    $user = User::factory()->create();

    // Create multiple notifications for different products without product_slug
    // This forces the HandleInertiaRequests middleware to load the products.
    $products = Product::factory()->count(5)->create();

    foreach ($products as $product) {
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\StockAlertNotification',
            'data' => [
                'product_id' => $product->id,
                // Purposely omitting product_slug to trigger the loading logic
            ],
            'read_at' => null,
        ]);
    }

    // Also create some notifications for the same product to test distinct loading
    for ($i = 0; $i < 3; $i++) {
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\StockAlertNotification',
            'data' => [
                'product_id' => $products->first()->id,
            ],
            'read_at' => null,
        ]);
    }

    actingAs($user);

    // Track queries
    DB::enableQueryLog();

    $response = $this->get('/'); // assuming dashboard route uses inertia

    $response->assertSuccessful();

    $queries = DB::getQueryLog();

    // The query log should contain ONE query for notifications
    // and ONE query to load all products via "where in"
    // So there shouldn't be 8 queries for products.
    $productQueries = collect($queries)->filter(function ($query) {
        return str_contains($query['query'], 'select * from `products` where `id` in');
    });

    $singleProductQueries = collect($queries)->filter(function ($query) {
        return str_contains($query['query'], 'select * from `products` where `products`.`id` =');
    });

    // We expect exactly 1 batch load query and 0 single load queries from the middleware.
    expect($productQueries)->toHaveCount(1)
        ->and($singleProductQueries)->toBeEmpty();

    // Verify inertia props
    $response->assertInertia(function (Assert $page) {
        $page->has('notifications', 8);
        $notifications = $page->toArray()['props']['notifications'];
        foreach ($notifications as $notification) {
            expect($notification['slug'])->not->toBeEmpty();
        }
    });
});
