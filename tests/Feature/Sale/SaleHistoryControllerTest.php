<?php

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows admin and cashier to view sale history', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    Sale::factory()->count(3)->create(['status' => 'completed']);

    $response = actingAs($user)->get(route('sale.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Sale/Index')
        ->has('sales.data', 3)
    );
})->with(['admin', 'cashier']);

it('allows admin to view sale details', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    $sale = Sale::factory()->create(['status' => 'completed']);

    $response = actingAs($admin)->get(route('sale.show', $sale));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Sale/Show')
        ->has('sale.id')
    );
});

it('denies warehouse to view sale history', function () {
    /** @var User $warehouse */
    $warehouse = User::factory()->create(['role' => 'warehouse']);
    $response = actingAs($warehouse)->get(route('sale.index'));
    $response->assertForbidden();
});
