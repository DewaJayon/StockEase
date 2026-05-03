<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['percentage', 'nominal']),
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ];
    }
}
