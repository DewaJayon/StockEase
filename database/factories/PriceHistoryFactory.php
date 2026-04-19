<?php

namespace Database\Factories;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceHistory>
 */
class PriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'old_purchase_price' => fake()->randomFloat(2, 1000, 5000),
            'new_purchase_price' => fake()->randomFloat(2, 5000, 10000),
            'old_selling_price' => fake()->randomFloat(2, 5000, 10000),
            'new_selling_price' => fake()->randomFloat(2, 10000, 20000),
            'reason' => fake()->sentence(),
        ];
    }
}
