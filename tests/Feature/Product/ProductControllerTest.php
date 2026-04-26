<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin and warehouse to view products', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    Product::factory()->count(3)->create();

    $response = actingAs($user)->get(route('product.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Product/Index')
            ->has('products.data', 3)
    );
})->with(['admin', 'warehouse']);

it('allows admin to create a product with image', function () {
    /** @var FilesystemAdapter $storage */
    $storage = Storage::fake('public');
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    $file = UploadedFile::fake()->image('product.jpg');

    $response = actingAs($admin)
        ->post(route('product.store'), [
            'name' => 'New Product',
            'category_id' => $category->id,
            'sku' => 'SKU123',
            'barcode' => '123456789',
            'description' => 'Description',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'alert_stock' => 5,
            'unit_id' => Unit::factory()->create()->id,
            'image' => $file,
        ]);

    $response->assertRedirect(route('product.index'));
    assertDatabaseHas('products', [
        'name' => 'New Product',
        'sku' => 'SKU123',
    ]);

    $product = Product::where('name', 'New Product')->first();
    $storage->assertExists(str_replace('storage/', '', $product->image_path));
});

it('allows admin to update a product', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['name' => 'Old Product']);
    $category = Category::factory()->create();

    $response = actingAs($admin)
        ->patch(route('product.update', $product), [
            'name' => 'Updated Product',
            'category_id' => $category->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'alert_stock' => 10,
            'unit_id' => Unit::factory()->create()->id,
        ]);

    $response->assertRedirect(route('product.index'));
    assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
    ]);
});

it('allows admin to delete a product', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create();

    $response = actingAs($admin)
        ->delete(route('product.destroy', $product));

    $response->assertRedirect(route('product.index'));
    assertDatabaseMissing('products', ['id' => $product->id]);
});
