<?php

use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows admin and cashier to view midtrans transactions', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    PaymentTransaction::factory()->count(3)->create();

    $response = actingAs($user)->get(route('midtrans.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('MidtransTransaction/Index')
        ->has('midtransTransactions.data', 3)
    );
})->with(['admin', 'cashier']);

it('denies warehouse to view midtrans transactions', function () {
    /** @var User $warehouse */
    $warehouse = User::factory()->create(['role' => 'warehouse']);
    $response = actingAs($warehouse)->get(route('midtrans.index'));
    $response->assertForbidden();
});
