<?php

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin and warehouse roles to view suppliers', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    Supplier::factory()->count(3)->create();

    $response = actingAs($user)->get(route('supplier.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Supplier/Index')
            ->has('suppliers.data', 3)
    );
})->with(['admin', 'warehouse']);

it('denies cashier to view suppliers', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);
    $response = actingAs($cashier)->get(route('supplier.index'));
    $response->assertForbidden();
});

it('allows admin to create a supplier', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('supplier.store'), [
            'name' => 'New Supplier',
            'phone' => '08123456789',
            'address' => 'Supplier Address',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('suppliers', [
        'name' => 'New Supplier',
        'phone' => '08123456789',
        'slug' => 'new-supplier',
    ]);
});

it('validates supplier creation', function ($data, $errors) {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('supplier.store'), $data);

    $response->assertSessionHasErrors($errors);
})->with([
    'empty name' => [['name' => '', 'phone' => '123', 'address' => 'addr'], ['name']],
    'invalid phone' => [['name' => 'Name', 'phone' => 'abc', 'address' => 'addr'], ['phone']],
    'empty address' => [['name' => 'Name', 'phone' => '123', 'address' => ''], ['address']],
]);

it('allows admin to update a supplier', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::factory()->create(['name' => 'Old Name']);

    $response = actingAs($admin)
        ->put(route('supplier.update', $supplier), [
            'name' => 'Updated Name',
            'phone' => '08987654321',
            'address' => 'Updated Address',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
});

it('allows admin to delete a supplier', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::factory()->create();

    $response = actingAs($admin)
        ->delete(route('supplier.destroy', $supplier));

    $response->assertRedirect();
    assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
});
