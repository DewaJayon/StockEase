<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLog;
use App\Models\User;
use App\Services\General\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** @property DashboardService $dashboardService */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dashboardService = new DashboardService;
});

it('can get admin dashboard data', function () {
    Sale::factory()->create(['total' => 1000, 'payment_method' => 'cash', 'status' => 'completed']);
    Product::factory()->create(['stock' => 5, 'alert_stock' => 10]);

    $data = $this->dashboardService->getDashboardData('admin');

    expect($data)->toHaveKeys(['salesSummary', 'lowStock', 'activities', 'weeklySalesChart']);
    expect((int) $data['salesSummary']['today'])->toBe(1000);
    expect($data['lowStock'])->toHaveCount(1);
});

it('can get cashier dashboard data', function () {
    Sale::factory()->create(['total' => 1000, 'payment_method' => 'cash', 'status' => 'completed']);

    $data = $this->dashboardService->getDashboardData('cashier');

    expect($data)->toHaveKeys(['cashierSalesSummary', 'recentTransaction', 'weeklySalesChart']);
    expect((int) $data['cashierSalesSummary']['todaysIncome'])->toBe(1000);
});

it('can get warehouse dashboard data', function () {
    Product::query()->delete();
    $products = Product::factory()->count(5)->create();
    StockLog::factory()->create(['product_id' => $products->first()->id, 'type' => 'in', 'qty' => 10]);

    $data = $this->dashboardService->getDashboardData('warehouse');

    expect($data)->toHaveKeys(['warehouseSummary', 'activityLogWarehouse', 'warehouseChart']);
    expect($data['warehouseSummary']['totalProduct'])->toBe(5);
});

it('unifies activity history from multiple sources', function () {
    Sale::factory()->create(['payment_method' => 'cash', 'status' => 'completed']);
    StockLog::factory()->create(['type' => 'in']);

    $activities = $this->dashboardService->getActivityHistory();

    expect($activities->count())->toBeGreaterThanOrEqual(2);
    expect($activities->first())->toHaveKeys(['type', 'desc', 'time']);
});

it('generates weekly sales chart data', function () {
    Sale::factory()->create(['total' => 1000, 'payment_method' => 'cash', 'status' => 'completed']);

    $chart = $this->dashboardService->getWeeklySalesChart();

    expect($chart)->toHaveKeys(['categories', 'data']);
    expect($chart['categories'])->toHaveCount(7);
});

it('generates warehouse chart data', function () {
    Product::factory()->create();
    StockLog::factory()->create();

    $chart = $this->dashboardService->getWarehouseChart();

    expect($chart)->toHaveKeys(['stockMovement', 'categoryDistribution']);
});
