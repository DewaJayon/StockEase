<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'gateway' => 'midtrans',
            'external_id' => $this->faker->uuid,
            'status' => 'settlement',
            'amount' => 10000,
            'payment_type' => 'qris',
        ];
    }
}
