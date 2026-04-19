<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);

    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock' => 100,
    ]);
});

describe('Dashboard Sales Chart and Summary', function () {
    it('does not count draft sales in weekly sales chart and summary', function () {
        // Create a completed sale today
        $completedSale = Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 10000,
            'payment_method' => 'cash',
            'created_at' => Carbon::now(),
            'date' => Carbon::today(),
        ]);
        SaleItem::factory()->create([
            'sale_id' => $completedSale->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 10000,
        ]);

        // Create a draft sale today (cart item)
        $draftSale = Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
            'total' => 50000,
            'payment_method' => 'cash',
            'created_at' => Carbon::now(),
            'date' => Carbon::today(),
        ]);
        SaleItem::factory()->create([
            'sale_id' => $draftSale->id,
            'product_id' => $this->product->id,
            'qty' => 5,
            'price' => 10000,
        ]);

        // Test admin dashboard
        $responseAdmin = $this->actingAs($this->admin)->get(route('dashboard'));
        $responseAdmin->assertSuccessful();

        $adminData = $responseAdmin->original->getData()['page']['props']['data'];

        // Assert today's sales summary only counts the completed sale
        expect($adminData['salesSummary']['today'])->toEqual(10000);

        // Find today's index in the weekly chart
        $todayIndex = Carbon::now()->dayOfWeekIso - 1; // 0 for Monday, 6 for Sunday
        expect($adminData['weeklySalesChart']['data'][$todayIndex])->toEqual(10000);

        // Test cashier dashboard
        $responseCashier = $this->actingAs($this->cashier)->get(route('dashboard'));
        $responseCashier->assertSuccessful();

        $cashierData = $responseCashier->original->getData()['page']['props']['data'];

        // Assert today's income only counts the completed sale
        expect($cashierData['cashierSalesSummary']['todaysIncome'])->toEqual(10000);

        // Assert best selling product doesn't include draft quantities
        expect($cashierData['cashierSalesSummary']['bestSellingProduct'])->toEqual($this->product->name);
    });

    it('correctly displays weekly sales data across multiple days', function () {
        $startOfWeek = Carbon::now()->startOfWeek();

        // Sale on Monday
        $saleMon = Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 15000,
            'date' => $startOfWeek->copy()->toDateString(),
            'created_at' => $startOfWeek->copy(),
        ]);

        // Sale on Wednesday
        $saleWed = Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 25000,
            'date' => $startOfWeek->copy()->addDays(2)->toDateString(),
            'created_at' => $startOfWeek->copy()->addDays(2),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertSuccessful();

        $data = $response->original->getData()['page']['props']['data'];
        $chartData = $data['weeklySalesChart']['data'];

        expect($chartData[0])->toEqual(15000); // Monday
        expect($chartData[1])->toEqual(0);     // Tuesday
        expect($chartData[2])->toEqual(25000); // Wednesday
    });
});
