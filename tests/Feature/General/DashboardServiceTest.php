<?php

namespace Tests\Feature\General;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\User;
use App\Services\General\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DashboardService;
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('includes price history in activity history', function () {
    $product = Product::factory()->create(['name' => 'Product A']);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'user_id' => $this->admin->id,
        'created_at' => Carbon::now(),
    ]);

    $activities = $this->service->getActivityHistory();

    expect($activities->where('type', 'price')->first()['desc'])
        ->toContain('Harga Product A diperbarui');
});

it('provides price update chart data for the last 7 days', function () {
    $product = Product::factory()->create();

    // Create 3 updates today
    PriceHistory::factory()->count(3)->create([
        'product_id' => $product->id,
        'created_at' => Carbon::now(),
    ]);

    // Create 2 updates yesterday
    PriceHistory::factory()->count(2)->create([
        'product_id' => $product->id,
        'created_at' => Carbon::now()->subDay(),
    ]);

    $chartData = $this->service->getPriceUpdateChartData();

    expect($chartData['categories'])->toHaveCount(7);
    expect($chartData['data'])->toHaveCount(7);

    // Today is the last index (index 6)
    expect($chartData['data'][6])->toBe(3);
    // Yesterday is index 5
    expect($chartData['data'][5])->toBe(2);
});

it('returns full admin dashboard data including price chart', function () {
    $data = $this->service->getDashboardData('admin');

    expect($data)->toHaveKeys([
        'salesSummary',
        'lowStock',
        'activities',
        'weeklySalesChart',
        'priceUpdateChart',
    ]);
});
