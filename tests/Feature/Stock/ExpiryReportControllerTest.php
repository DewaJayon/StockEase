<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    /** @var TestCase&object{admin:User, warehouse:User, cashier:User, category:Category, supplier:Supplier, product:Product} $this */
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->warehouse = User::factory()->create(['role' => 'warehouse']);
    $this->cashier = User::factory()->create(['role' => 'cashier']);

    $this->category = Category::factory()->create();
    $unit = Unit::factory()->create();
    $this->supplier = Supplier::factory()->create();

    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'unit_id' => $unit->id,
        'sku' => 'SKU-001',
    ]);
});

// Helper — buat PurchaseItem dengan expiry_date
function expiryItem(Product $product, Supplier $supplier, string $expiryDate, array $attributes = []): PurchaseItem
{
    $purchase = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'date' => Carbon::today()->toDateString(),
    ]);

    return PurchaseItem::factory()->create(array_merge([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'expiry_date' => $expiryDate,
        'qty' => 10,
        'price' => 5000,
    ], $attributes));
}

// Helper — buat PurchaseItem tanpa expiry_date
function itemWithoutExpiry(Product $product, Supplier $supplier): PurchaseItem
{
    $purchase = Purchase::factory()->create(['supplier_id' => $supplier->id]);

    return PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'expiry_date' => null,
    ]);
}

// ============================================================
// Authorization
// ============================================================

describe('Authorization', function () {
    it('redirects unauthenticated user', function () {
        get(route('reports.expiry.index'))->assertRedirect(route('login'));
    });

    it('forbids cashier from expiry report', function () {
        /** @var TestCase&object{cashier:User} $this */
        actingAs($this->cashier)
            ->get(route('reports.expiry.index'))
            ->assertForbidden();
    });

    it('allows admin and warehouse to access expiry report', function (string $role) {
        /** @var TestCase&object{admin:User, warehouse:User} $this */
        actingAs($this->{$role})
            ->get(route('reports.expiry.index'))
            ->assertSuccessful();
    })->with(['admin', 'warehouse']);
});

// ============================================================
// Index
// ============================================================

describe('Index', function () {
    it('renders the Reports/Expiry/Index component', function () {
        /** @var TestCase&object{admin:User} $this */
        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Reports/Expiry/Index'));
    });

    it('passes expiryData and filters props', function () {
        /** @var TestCase&object{admin:User} $this */
        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('expiryData')
                    ->has('filters')
            );
    });

    it('passes paginator structure in expiryData', function () {
        /** @var TestCase&object{admin:User} $this */
        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('expiryData.data')
                    ->has('expiryData.current_page')
                    ->has('expiryData.per_page')
                    ->has('expiryData.total')
            );
    });

    it('only shows purchase items that have expiry_date', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->addDays(10)->toDateString());
        itemWithoutExpiry($this->product, $this->supplier);

        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('orders items by expiry_date ascending', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $productA = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $productB = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);

        expiryItem($productA, $this->supplier, Carbon::today()->addDays(30)->toDateString());
        expiryItem($productB, $this->supplier, Carbon::today()->addDays(5)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->where('expiryData.data.0.product.id', $productB->id)
                    ->where('expiryData.data.1.product.id', $productA->id)
            );
    });

    it('paginates with default 10 per page', function () {
        /** @var TestCase&object{admin:User, category:Category, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        for ($i = 0; $i < 12; $i++) {
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]);
            expiryItem($product, $this->supplier, Carbon::today()->addDays($i + 1)->toDateString());
        }

        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->has('expiryData.data', 10)
                    ->where('expiryData.total', 12)
            );
    });

    it('returns empty list when no items have expiry_date', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        itemWithoutExpiry($this->product, $this->supplier);

        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 0));
    });

    it('passes active filters back in filters prop', function () {
        /** @var TestCase&object{admin:User} $this */
        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'susu', 'status' => 'expired']))
            ->assertInertia(
                fn ($page) => $page
                    ->where('filters.search', 'susu')
                    ->where('filters.status', 'expired')
            );
    });
});

// ============================================================
// Search filter
// ============================================================

describe('Search filter', function () {
    it('filters by product name', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $productA = Product::factory()->create([
            'name' => 'Susu Sapi',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        $productB = Product::factory()->create([
            'name' => 'Kopi Arabika',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        expiryItem($productA, $this->supplier, Carbon::today()->addDays(10)->toDateString());
        expiryItem($productB, $this->supplier, Carbon::today()->addDays(10)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'Susu']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('filters by product SKU', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $productWithSku = Product::factory()->create([
            'sku' => 'SKU-MATCH',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        $otherProduct = Product::factory()->create([
            'sku' => 'SKU-OTHER',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        expiryItem($productWithSku, $this->supplier, Carbon::today()->addDays(10)->toDateString());
        expiryItem($otherProduct, $this->supplier, Carbon::today()->addDays(10)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'SKU-MATCH']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('returns empty when search has no match', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->addDays(10)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'xyznonexistent']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 0));
    });

    it('search is case insensitive', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Susu Sapi',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        expiryItem($product, $this->supplier, Carbon::today()->addDays(10)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'susu sapi']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });
});

// ============================================================
// Status filter
// ============================================================

describe('Status filter', function () {
    it('filters expired items — expiry_date before today', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $expiredProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $validProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);

        expiryItem($expiredProduct, $this->supplier, Carbon::yesterday()->toDateString());
        expiryItem($validProduct, $this->supplier, Carbon::today()->addDays(60)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('does not include today as expired', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 0));
    });

    it('filters near_expired items — expiry_date within 30 days from today', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $nearProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $farProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $expiredProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);

        expiryItem($nearProduct, $this->supplier, Carbon::today()->addDays(15)->toDateString());
        expiryItem($farProduct, $this->supplier, Carbon::today()->addDays(60)->toDateString());
        expiryItem($expiredProduct, $this->supplier, Carbon::yesterday()->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'near_expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('includes today in near_expired range', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'near_expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('includes day 30 in near_expired range', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->addDays(30)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'near_expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });

    it('excludes day 31 from near_expired range', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->addDays(31)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'near_expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 0));
    });

    it('shows all items when status filter is not provided', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $expiredProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $nearProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $validProduct = Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id]);

        expiryItem($expiredProduct, $this->supplier, Carbon::yesterday()->toDateString());
        expiryItem($nearProduct, $this->supplier, Carbon::today()->addDays(15)->toDateString());
        expiryItem($validProduct, $this->supplier, Carbon::today()->addDays(90)->toDateString());

        actingAs($this->admin)
            ->get(route('reports.expiry.index'))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 3));
    });

    it('ignores unrecognized status value and shows all items', function () {
        /** @var TestCase&object{admin:User, product:Product, supplier:Supplier} $this */
        expiryItem($this->product, $this->supplier, Carbon::today()->addDays(10)->toDateString());

        // Status tidak dikenal — service tidak masuk ke branch manapun
        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['status' => 'invalid_status']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });
});

// ============================================================
// Search + Status combined
// ============================================================

describe('Combined search and status filter', function () {
    it('applies both search and status filter simultaneously', function () {
        /** @var TestCase&object{admin:User, supplier:Supplier} $this */
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $targetProduct = Product::factory()->create([
            'name' => 'Susu Sapi',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        $wrongNameProduct = Product::factory()->create([
            'name' => 'Kopi Arabika',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        $wrongStatusProduct = Product::factory()->create([
            'name' => 'Susu Kambing',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        expiryItem($targetProduct, $this->supplier, Carbon::yesterday()->toDateString());      // expired + name match
        expiryItem($wrongNameProduct, $this->supplier, Carbon::yesterday()->toDateString());   // expired, name mismatch
        expiryItem($wrongStatusProduct, $this->supplier, Carbon::today()->addDays(60)->toDateString()); // name match, not expired

        actingAs($this->admin)
            ->get(route('reports.expiry.index', ['search' => 'Susu', 'status' => 'expired']))
            ->assertInertia(fn ($page) => $page->has('expiryData.data', 1));
    });
});
