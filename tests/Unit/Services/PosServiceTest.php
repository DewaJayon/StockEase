<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Sale\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/** @property User $user */
/** @property PosService $posService */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cashier']);
    Auth::login($this->user);
    $this->posService = new PosService;
});

it('can get categories for select', function () {
    Category::factory()->count(3)->create();

    $categories = $this->posService->getCategories();

    expect($categories)->toHaveCount(3);
    expect($categories->first())->toHaveKeys(['value', 'label']);
});

it('can get paginated products', function () {
    Product::factory()->count(15)->create();

    $products = $this->posService->getPaginatedProducts([], 10);

    expect($products->total())->toBe(15);
    expect($products->count())->toBe(10);
});

it('can filter products by category', function () {
    $category = Category::factory()->create(['slug' => 'food']);
    Product::factory()->count(5)->create(['category_id' => $category->id]);
    Product::factory()->count(3)->create(); // other categories

    $products = $this->posService->getPaginatedProducts(['category' => 'food']);

    expect($products->total())->toBe(5);
});

it('can get or create a draft cart', function () {
    $cart = $this->posService->getOrCreateCart();

    expect($cart)->toBeInstanceOf(Sale::class);
    expect($cart->status)->toBe('draft');
    expect($cart->user_id)->toBe($this->user->id);

    // Test that it returns existing draft
    $sameCart = $this->posService->getOrCreateCart();
    expect($sameCart->id)->toBe($cart->id);
});

it('can add item to cart', function () {
    expect(Auth::check())->toBeTrue();
    $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);

    $result = $this->posService->addToCart($product->id);

    expect($result['cart']->saleItems)->toHaveCount(1);
    expect((int) $result['cart']->total)->toBe(1000);
    expect((int) $result['total'])->toBe(1000);
});

it('can update item qty in cart', function () {
    $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);
    $cart = $this->posService->getOrCreateCart();
    SaleItem::create([
        'sale_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $result = $this->posService->updateCartItemQty($product->id, 5);

    expect((int) $result['total'])->toBe(5000);
    expect($cart->fresh()->saleItems->first()->qty)->toBe(5);
});

it('throws exception if updating qty exceeds product stock', function () {
    $product = Product::factory()->create(['stock' => 5, 'selling_price' => 1000]);
    $cart = $this->posService->getOrCreateCart();
    SaleItem::create([
        'sale_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $this->posService->updateCartItemQty($product->id, 10);
})->throws(Exception::class, 'Stok produk tidak mencukupi');

it('removes item from cart if qty is 0', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $cart = $this->posService->getOrCreateCart();
    SaleItem::create([
        'sale_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => 1000,
    ]);

    $this->posService->updateCartItemQty($product->id, 0);

    expect($cart->fresh()->saleItems)->toHaveCount(0);
});

it('can empty the cart', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->posService->addToCart($product->id);

    $this->posService->emptyCart();

    $cart = $this->posService->getOrCreateCart();
    expect($cart->saleItems)->toHaveCount(0);
    expect($cart->total)->toBe('0.0000');
});

it('throws exception if adding product with no stock', function () {
    $product = Product::factory()->create(['stock' => 0]);

    $this->posService->addToCart($product->id);
})->throws(Exception::class, 'Stok produk habis');

it('can checkout a sale successfully', function () {
    $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);
    $this->posService->addToCart($product->id);

    $checkoutData = [
        'payment_method' => 'cash',
        'customer_name' => 'John Doe',
        'paid' => 1000,
        'change' => 0,
    ];

    $result = $this->posService->checkout($checkoutData);

    expect($result['cart']->status)->toBe('draft'); // New draft created after checkout

    $completedSale = Sale::where('customer_name', 'John Doe')->first();
    expect($completedSale->status)->toBe('completed');
    expect($product->fresh()->stock)->toBe(9);
});
