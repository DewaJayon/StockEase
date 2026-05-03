<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function () {
    config(['app.debug' => false]);
});

it('renders the custom error page for 404', function () {
    get('/non-existent-page')
        ->assertStatus(404)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
        );
});

it('renders the custom error page for 403', function () {
    Route::middleware('web')->get('/test-403', fn () => abort(403));

    get('/test-403')
        ->assertStatus(403)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 403)
        );
});

it('renders the custom error page for 500', function () {
    Route::middleware('web')->get('/test-500', fn () => abort(500));

    get('/test-500')
        ->assertStatus(500)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 500)
        );
});
