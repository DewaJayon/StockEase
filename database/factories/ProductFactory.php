<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->word(),
            'sku' => fake()->unique()->ean8(),
            'barcode' => fake()->ean13(),
            'unit' => fake()->randomElement(['pcs', 'box', 'pack']),
            'stock' => fake()->numberBetween(0, 100),
            'purchase_price' => fake()->numberBetween(1000, 10000),
            'selling_price' => fake()->numberBetween(11000, 20000),
            'alert_stock' => 10,
        ];
    }
}
