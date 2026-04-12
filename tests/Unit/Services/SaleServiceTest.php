<?php

use App\Models\Sale;
use App\Models\User;
use App\Services\Sale\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** @property SaleService $saleService */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->saleService = new SaleService;
});

it('can get paginated sales history', function () {
    // Create sales that are completed/not pending
    Sale::factory()->count(15)->create([
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    // Create one pending to ensure it is excluded
    Sale::factory()->create(['payment_method' => 'pending']);

    $sales = $this->saleService->getPaginatedSales([], 10);

    expect($sales->total())->toBe(15);
    expect($sales->count())->toBe(10);
});

it('can filter sales by search query', function () {
    Sale::factory()->create(['customer_name' => 'John Doe', 'payment_method' => 'cash', 'status' => 'completed']);
    Sale::factory()->create(['customer_name' => 'Jane Smith', 'payment_method' => 'cash', 'status' => 'completed']);

    $sales = $this->saleService->getPaginatedSales(['search' => 'John']);

    expect($sales->total())->toBe(1);
    expect($sales->first()->customer_name)->toBe('John Doe');
});

it('can filter sales by date range', function () {
    $sale1 = Sale::factory()->create(['updated_at' => now()->subDays(5), 'payment_method' => 'cash', 'status' => 'completed']);
    $sale2 = Sale::factory()->create(['updated_at' => now(), 'payment_method' => 'cash', 'status' => 'completed']);

    $filters = [
        'start' => now()->subDays(1)->toDateString(),
        'end' => now()->toDateString(),
    ];

    $sales = $this->saleService->getPaginatedSales($filters);

    expect($sales->total())->toBe(1);
    expect($sales->first()->id)->toBe($sale2->id);
});

it('can get sale details with relations', function () {
    $sale = Sale::factory()->create(['payment_method' => 'cash', 'status' => 'completed']);

    $details = $this->saleService->getSaleDetails($sale);

    expect($details->relationLoaded('user'))->toBeTrue();
    expect($details->relationLoaded('saleItems'))->toBeTrue();
});
