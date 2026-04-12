<?php

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin and warehouse to view units', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    Unit::factory()->count(3)->create();

    $response = actingAs($admin)->get(route('unit.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Unit/Index')
            ->has('units.data', 10)
    );
});

it('denies cashier to view units', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($cashier)->get(route('unit.index'));

    $response->assertForbidden();
});

it('allows admin to create a unit', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('unit.store'), [
            'name' => 'New Unit',
            'short_name' => 'NU',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('units', [
        'name' => 'New Unit',
        'short_name' => 'NU',
        'slug' => 'new-unit',
    ]);
});

it('validates unit creation', function ($data, $errors) {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('unit.store'), $data);

    $response->assertSessionHasErrors($errors);
})->with([
    'empty name' => [['name' => '', 'short_name' => 'A'], ['name']],
    'empty short name' => [['name' => 'A', 'short_name' => ''], ['short_name']],
    'name too long' => [['name' => str_repeat('a', 256), 'short_name' => 'A'], ['name']],
]);

it('allows admin to update a unit', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $unit = Unit::factory()->create(['name' => 'Old Name', 'short_name' => 'ON']);

    $response = actingAs($admin)
        ->put(route('unit.update', $unit), [
            'name' => 'Updated Name',
            'short_name' => 'UN',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('units', [
        'id' => $unit->id,
        'name' => 'Updated Name',
        'short_name' => 'UN',
        'slug' => 'updated-name',
    ]);
});

it('allows admin to delete a unit', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $unit = Unit::factory()->create();

    $response = actingAs($admin)
        ->delete(route('unit.destroy', $unit));

    $response->assertRedirect();
    assertDatabaseMissing('units', ['id' => $unit->id]);
});
