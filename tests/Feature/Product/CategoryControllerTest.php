<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin to view categories', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    Category::factory()->count(3)->create();

    $response = actingAs($admin)->get(route('category.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Category/Index')
            ->has('categories.data', 3)
    );
});

it('denies cashier to view categories', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($cashier)->get(route('category.index'));

    $response->assertForbidden();
});

it('allows admin to create a category', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('category.store'), [
            'name' => 'New Category',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});

it('validates category creation', function ($data, $errors) {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('category.store'), $data);

    $response->assertSessionHasErrors($errors);
})->with([
    'empty name' => [['name' => ''], ['name']],
    'name too long' => [['name' => str_repeat('a', 256)], ['name']],
]);

it('allows admin to update a category', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create(['name' => 'Old Name']);

    $response = actingAs($admin)
        ->put(route('category.update', $category), [
            'name' => 'Updated Name',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
});

it('allows admin to delete a category', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    $response = actingAs($admin)
        ->delete(route('category.destroy', $category));

    $response->assertRedirect();
    assertDatabaseMissing('categories', ['id' => $category->id]);
});
