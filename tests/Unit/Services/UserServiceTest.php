<?php

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** @property UserService $service */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new UserService;
});

it('can get paginated users', function () {
    User::factory()->count(15)->create();

    $result = $this->service->getPaginatedUsers([], 10);

    expect($result->items())->toHaveCount(10);
    expect($result->total())->toBe(15);
});

it('can filter users by search query', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'admin']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'cashier']);

    // Search by name
    $result = $this->service->getPaginatedUsers(['search' => 'John']);
    expect($result->items())->toHaveCount(1);

    // Search by email
    $result = $this->service->getPaginatedUsers(['search' => 'jane@example.com']);
    expect($result->items())->toHaveCount(1);

    // Search by role
    $result = $this->service->getPaginatedUsers(['search' => 'admin']);
    expect($result->items())->toHaveCount(1);
});

it('can store a user', function () {
    $data = [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'role' => 'cashier',
    ];

    $user = $this->service->storeUser($data);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('New User');
    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

it('can update a user', function () {
    $user = User::factory()->create(['name' => 'Old User']);
    $data = ['name' => 'Updated User'];

    $this->service->updateUser($user, $data);

    $user->refresh();
    expect($user->name)->toBe('Updated User');
});

it('can reset password', function () {
    $user = User::factory()->create(['password' => Hash::make('old_password')]);

    $this->service->resetPassword($user, 'new_password');

    $user->refresh();
    expect(Hash::check('new_password', $user->password))->toBeTrue();
});

it('can delete a user', function () {
    $user = User::factory()->create();

    $this->service->deleteUser($user);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
