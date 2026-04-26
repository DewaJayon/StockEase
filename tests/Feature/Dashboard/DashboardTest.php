<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);
    $this->warehouse = User::factory()->create(['role' => 'warehouse']);

    /** @var TestCase&object{category: Category, product: Product} $this */
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock' => 100,
    ]);
});

describe('Dashboard Access', function () {
    it('redirects unauthenticated users', function () {
        get(route('dashboard'))->assertRedirect(route('login'));
    });

    it('allows admin to access dashboard', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Dashboard/Index'));
    });

    it('allows cashier to access dashboard', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertSuccessful();
    });

    it('allows warehouse to access dashboard', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertSuccessful();
    });
});

describe('Admin Dashboard', function () {
    it('returns correct prop keys for admin', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.salesSummary')
                    ->has('data.lowStock.data')
                    ->has('data.activities.data')
                    ->has('data.weeklySalesChart')
                    ->has('data.priceUpdateChart')
            );
    });

    it('excludes draft sales from today summary', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
            'total' => 50000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.salesSummary.today', 10000)
            );
    });

    it('excludes draft sales from weekly chart', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
            'total' => 50000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $todayIndex = Carbon::today()->dayOfWeekIso - 1;

        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where("data.weeklySalesChart.data.{$todayIndex}", 10000)
            );
    });

    it('sums multiple completed sales for today', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 20000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.salesSummary.today', 30000)
            );
    });

    it('correctly maps sales across multiple days in weekly chart', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $monday = Carbon::now()->startOfWeek(); // Monday
        $wednesday = $monday->copy()->addDays(2);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 15000,
            'date' => $monday->toDateString(),
            'created_at' => $monday,
        ]);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 25000,
            'date' => $wednesday->toDateString(),
            'created_at' => $wednesday,
        ]);

        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.weeklySalesChart.data.0', 15000) // Monday
                    ->where('data.weeklySalesChart.data.1', 0)     // Tuesday
                    ->where('data.weeklySalesChart.data.2', 25000) // Wednesday
            );
    });

    it('returns zero for today when no completed sales exist', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.salesSummary.today', 0)
                    ->where('data.salesSummary.month', 0)
            );
    });

    it('flags products at or below alert stock', function () {

        /** @var TestCase&object{category: Category, product: Product} $this */
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 3,
            'alert_stock' => 5,
        ]);

        $okProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 20,
            'alert_stock' => 5,
        ]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has(
                        'data.lowStock.data',
                        fn ($items) => $items
                            ->where('0.name', $lowStockProduct->name)
                            ->etc()
                    )
            );
    });
});

describe('Cashier Dashboard', function () {
    it('returns correct prop keys for cashier', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.cashierSalesSummary')
                    ->has('data.recentTransaction')
                    ->has('data.weeklySalesChart')
            );
    });

    it('excludes draft sales from cashier today income', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'draft',
            'total' => 50000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.todaysIncome', 10000)
            );
    });

    it('shows correct best selling product excluding drafts', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $completedSale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        /** @var TestCase&object{category: Category, product: Product} $this */
        SaleItem::factory()->create([
            'sale_id' => $completedSale->id,
            'product_id' => $this->product->id,
            'qty' => 3,
            'price' => 10000,
        ]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $draftSale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'draft',
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        /** @var TestCase&object{category: Category, product: Product} $this */
        $otherProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        SaleItem::factory()->create([
            'sale_id' => $draftSale->id,
            'product_id' => $otherProduct->id,
            'qty' => 99,
            'price' => 5000,
        ]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.bestSellingProduct', $this->product->name)
            );
    });

    it('shows fallback message when no transactions today', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.bestSellingProduct', 'Tidak ada transaksi hari ini')
            );
    });

    it('counts only this week completed sales for total transaction per week', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        // This week
        Sale::factory()->count(3)->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'date' => Carbon::now()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        // Draft — should not count
        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'draft',
            'date' => Carbon::now()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        // Last week — should not count
        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'date' => Carbon::now()->subWeek()->toDateString(),
            'created_at' => Carbon::now()->subWeek(),
        ]);

        actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.totalTransactionPerWeek', 3)
            );
    });
});

describe('Warehouse Dashboard', function () {
    it('returns correct prop keys for warehouse', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.warehouseSummary')
                    ->has('data.activityLogWarehouse')
                    ->has('data.warehouseChart')
            );
    });

    it('counts total products correctly', function () {
        /** @var TestCase&object{category: Category, product: Product} $this */
        $initialCount = Product::count();

        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.warehouseSummary.totalProduct', $initialCount + 3)
            );
    });

    it('counts low stock products correctly', function () {

        /** @var TestCase&object{category: Category, product: Product} $this */
        Product::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'stock' => 2,
            'alert_stock' => 5,
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 10,
            'alert_stock' => 5,
        ]);

        $lowStockInBeforeEach = $this->product->stock <= $this->product->alert_stock ? 1 : 0;

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.warehouseSummary.lowStock', 2 + $lowStockInBeforeEach)
            );
    });
});

describe('Admin Dashboard — activity history', function () {
    it('includes completed sales in activity history', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $sale = Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 75000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        /** @var TestCase&object{category: Category, product: Product} $this */
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'price' => 37500,
        ]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.activities.data')
                    ->where('data.activities.data.0.type', 'sale')
            );
    });

    it('activity history contains at most 5 entries per page', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->count(12)->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.activities.data', fn ($items) => count($items) <= 5)
            );
    });

    it('excludes draft sales from activity history', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
            'total' => 99999,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.activities.data', fn ($items) => collect($items)->every(
                        fn ($a) => $a['type'] !== 'sale' || ! str_contains($a['desc'], '99.999')
                    ))
            );
    });
});

describe('Admin Dashboard — monthly summary', function () {
    it('excludes sales from previous month in month summary', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 50000,
            'date' => Carbon::now()->subMonth()->toDateString(),
            'created_at' => Carbon::now()->subMonth(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 20000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.salesSummary.month', 20000)
            );
    });
});

describe('Admin Dashboard — weekly chart structure', function () {
    it('weekly chart always has 7 entries', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.weeklySalesChart.data', 7)
                    ->has('data.weeklySalesChart.categories', 7)
            );
    });

    it('weekly chart categories start from Monday', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Carbon::setLocale('id');
        $monday = Carbon::now()->startOfWeek()->isoFormat('ddd');

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.weeklySalesChart.categories.0', $monday)
            );
    });
});

describe('Cashier Dashboard — average per customer', function () {
    it('calculates average correctly for multiple completed sales today', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'total' => 30000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.averagePerCustomer', 20000)
            );
    });

    it('returns null average when no completed sales today', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $this->actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.cashierSalesSummary.averagePerCustomer', null)
            );
    });
});

describe('Cashier Dashboard — recent transactions', function () {
    it('recent transactions only contain completed sales', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'total' => 15000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'draft',
            'total' => 99999,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('data.recentTransaction', fn ($items) => collect($items)->every(
                        fn ($t) => $t['total'] !== 99999
                    ))
            );
    });

    it('recent transactions are limited to 5', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        Sale::factory()->count(8)->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'total' => 10000,
            'date' => Carbon::today()->toDateString(),
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($this->cashier)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.recentTransaction', 5)
            );
    });
});

describe('Warehouse Dashboard — stock movement chart', function () {
    it('warehouse chart has correct structure', function () {
        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $this->actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('data.warehouseChart.stockMovement', 7)
                    ->has('data.warehouseChart.categoryDistribution')
            );
    });

    it('category distribution groups stock by category', function () {
        /** @var TestCase&object{category: Category, product: Product} $this */
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'stock' => 10,
        ]);

        /** @var TestCase&object{admin: User, cashier: User, warehouse: User} $this */
        $this->actingAs($this->warehouse)
            ->get(route('dashboard'))
            ->assertInertia(
                fn ($page) => $page
                    ->has(
                        'data.warehouseChart.categoryDistribution',
                        fn ($items) => $items
                            ->where('0.category', $this->category->name)
                            ->etc()
                    )
            );
    });
});
