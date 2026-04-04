<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockLog>
 */
class StockLogFactory extends Factory
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
            'type' => 'in',
            'reference_type' => 'manual',
            'reference_id' => null,
            'qty' => fake()->numberBetween(1, 10),
            'note' => fake()->sentence(),
        ];
    }
}
