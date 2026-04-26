<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    test()->admin = User::factory()->create(['role' => 'admin']);
    test()->warehouse = User::factory()->create(['role' => 'warehouse']);
    test()->cashier = User::factory()->create(['role' => 'cashier']);

    $category = Category::factory()->create();
    $unit = Unit::factory()->create();

    test()->product = Product::factory()->create([
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'name' => 'Susu Sapi',
        'sku' => 'SKU-001',
        'barcode' => 'BAR-001',
    ]);
});

// Helper — buat StockLog
function stockLog(Product $product, array $attributes = []): StockLog
{
    return StockLog::factory()->create(array_merge([
        'product_id' => $product->id,
        'type' => 'in',
        'qty' => 10,
        'note' => 'Test note',
        'created_at' => Carbon::now(),
    ], $attributes));
}

// ============================================================
// Authorization
// ============================================================

describe('Authorization', function () {
    it('redirects unauthenticated user', function () {
        get(route('log-stock.index'))->assertRedirect(route('login'));
    });

    it('forbids cashier from log stock', function () {
        actingAs(test()->cashier)
            ->get(route('log-stock.index'))
            ->assertForbidden();
    });

    it('allows admin and warehouse to access log stock', function (string $role) {
        actingAs(test()->{$role})
            ->get(route('log-stock.index'))
            ->assertSuccessful();
    })->with(['admin', 'warehouse']);
});

// ============================================================
// Index
// ============================================================

describe('Index', function () {
    it('renders the LogStock/Index component', function () {
        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('LogStock/Index'));
    });

    it('passes logStocks prop with paginator structure', function () {
        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('logStocks.data')
                    ->has('logStocks.current_page')
                    ->has('logStocks.per_page')
                    ->has('logStocks.total')
            );
    });

    it('returns all stock logs when no filter provided', function () {
        stockLog(test()->product);
        stockLog(test()->product);

        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 2));
    });

    it('orders stock logs by latest first', function () {
        $older = stockLog(test()->product, ['created_at' => Carbon::now()->subDays(2)]);
        $newer = stockLog(test()->product, ['created_at' => Carbon::now()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('logStocks.data.0.id', $newer->id)
                    ->where('logStocks.data.1.id', $older->id)
            );
    });

    it('paginates with default 10 per page', function () {
        for ($i = 0; $i < 12; $i++) {
            stockLog(test()->product);
        }

        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('logStocks.data', 10)
                    ->where('logStocks.total', 12)
            );
    });

    it('respects per_page query parameter', function () {
        for ($i = 0; $i < 8; $i++) {
            stockLog(test()->product);
        }

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['per_page' => 5]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 5));
    });

    it('eager loads product relation', function () {
        stockLog(test()->product);

        actingAs(test()->admin)
            ->get(route('log-stock.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('logStocks.data.0.product')
                    ->has('logStocks.data.0.product.id')
            );
    });
});

// ============================================================
// Search filter
// ============================================================

describe('Search filter', function () {
    it('filters by product name', function () {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $otherProduct = Product::factory()->create([
            'name' => 'Kopi Arabika',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        stockLog(test()->product); // Susu Sapi
        stockLog($otherProduct);  // Kopi Arabika

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'Susu']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('filters by product SKU', function () {
        stockLog(test()->product); // SKU-001

        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $otherProduct = Product::factory()->create([
            'sku' => 'SKU-999',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        stockLog($otherProduct);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'SKU-001']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('filters by product barcode', function () {
        stockLog(test()->product); // BAR-001

        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $otherProduct = Product::factory()->create([
            'barcode' => 'BAR-999',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        stockLog($otherProduct);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'BAR-001']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('filters by type', function () {
        stockLog(test()->product, ['type' => 'in']);
        stockLog(test()->product, ['type' => 'out']);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'in']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('filters by note', function () {
        stockLog(test()->product, ['note' => 'pembelian batch januari']);
        stockLog(test()->product, ['note' => 'penyesuaian stok opname']);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'januari']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('returns empty when search has no match', function () {
        stockLog(test()->product);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'xyznonexistent']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 0));
    });

    it('search is case insensitive', function () {
        stockLog(test()->product); // name: Susu Sapi

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['search' => 'susu sapi']))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });
});

// ============================================================
// Date range filter
// ============================================================

describe('Date range filter', function () {
    it('filters logs within date range when both start and end provided', function () {
        stockLog(test()->product, ['created_at' => Carbon::today()]);
        stockLog(test()->product, ['created_at' => Carbon::yesterday()]);
        stockLog(test()->product, ['created_at' => Carbon::now()->subDays(10)]); // outside

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'start' => Carbon::yesterday()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 2));
    });

    it('excludes logs before start date', function () {
        stockLog(test()->product, ['created_at' => Carbon::now()->subDays(3)]);
        stockLog(test()->product, ['created_at' => Carbon::today()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'start' => Carbon::today()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('excludes logs after end date', function () {
        stockLog(test()->product, ['created_at' => Carbon::today()]);
        stockLog(test()->product, ['created_at' => Carbon::tomorrow()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'start' => Carbon::today()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('includes logs on start date boundary (start of day)', function () {
        stockLog(test()->product, ['created_at' => Carbon::today()->startOfDay()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'start' => Carbon::today()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('includes logs on end date boundary (end of day)', function () {
        stockLog(test()->product, ['created_at' => Carbon::today()->endOfDay()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'start' => Carbon::today()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });

    it('ignores date filter when only start is provided', function () {
        // Service: when(start && end, ...) — keduanya harus ada
        stockLog(test()->product, ['created_at' => Carbon::now()->subMonth()]);
        stockLog(test()->product, ['created_at' => Carbon::today()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['start' => Carbon::today()->toDateString()]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 2));
    });

    it('ignores date filter when only end is provided', function () {
        stockLog(test()->product, ['created_at' => Carbon::now()->subMonth()]);
        stockLog(test()->product, ['created_at' => Carbon::today()]);

        actingAs(test()->admin)
            ->get(route('log-stock.index', ['end' => Carbon::today()->toDateString()]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 2));
    });
});

// ============================================================
// Search + Date combined
// ============================================================

describe('Combined search and date filter', function () {
    it('applies both search and date filter simultaneously', function () {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $otherProduct = Product::factory()->create([
            'name' => 'Kopi Arabika',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        stockLog(test()->product, ['created_at' => Carbon::today()]);       // nama match, tanggal match
        stockLog($otherProduct, ['created_at' => Carbon::today()]);        // nama tidak match
        stockLog(test()->product, ['created_at' => Carbon::now()->subMonth()]); // nama match, tanggal tidak match

        actingAs(test()->admin)
            ->get(route('log-stock.index', [
                'search' => 'Susu',
                'start' => Carbon::today()->toDateString(),
                'end' => Carbon::today()->toDateString(),
            ]))
            ->assertInertia(fn ($page) => $page->has('logStocks.data', 1));
    });
});
