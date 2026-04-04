<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

it('allows admin to view users list', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(5)->create();

    $response = actingAs($admin)->get(route('users.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn ($page) => $page
            ->component('User/Index')
            ->has('users.data', 6) // admin + 5 users
    );
});

it('denies cashier to access users list', function () {
    /** @var User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = actingAs($cashier)->get(route('users.index'));

    $response->assertForbidden();
});

it('allows admin to create a user', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

    $response = actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'cashier',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'cashier',
    ]);
});

it('allows admin to update user details', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    /** @var User $user */
    $user = User::factory()->create(['name' => 'Old Name', 'role' => 'warehouse']);

    $response = actingAs($admin)
        ->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'role' => 'admin',
    ]);
});

it('allows admin to reset user password', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($admin)
        ->put(route('users.reset-password', $user), [
            'password' => 'new-password-123',
        ]);

    $response->assertRedirect();
    $user->refresh();
    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});

it('allows admin to delete a user', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['role' => 'admin']);
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($admin)
        ->delete(route('users.destroy', $user));

    $response->assertRedirect();
    assertDatabaseMissing('users', ['id' => $user->id]);
});
