<?php

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['midtrans.server_key' => 'test-server-key']);
});

it('allows admin and cashier to create midtrans transactions', function ($role) {
    /** @var User $user */
    $user = User::factory()->create(['role' => $role]);
    $sale = Sale::factory()->create(['user_id' => $user->id, 'total' => 1000]);

    // Mock Midtrans Snap
    $mock = Mockery::mock('alias:Midtrans\Snap');
    $mock->shouldReceive('getSnapToken')->andReturn('mock-snap-token');

    $response = actingAs($user)
        ->postJson(route('pos.qris-token'), [
            'amount' => 1000,
            'customer_name' => 'John Doe',
        ]);

    $response->assertSuccessful();
    $response->assertJson(['snap_token' => 'mock-snap-token']);
})->with(['admin', 'cashier']);
