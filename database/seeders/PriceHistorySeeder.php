<?php

namespace Database\Seeders;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PriceHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create 15 price history logs
        for ($i = 0; $i < 15; $i++) {
            $product = $products->random();
            $user = $users->random();
            $date = Carbon::now()->subtract('days', rand(0, 60));

            $oldPurchasePrice = $product->purchase_price;
            $newPurchasePrice = $oldPurchasePrice * (1 + (rand(-10, 20) / 100));

            $oldSellingPrice = $product->selling_price;
            $newSellingPrice = $oldSellingPrice * (1 + (rand(-10, 20) / 100));

            PriceHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'old_purchase_price' => $oldPurchasePrice,
                'new_purchase_price' => $newPurchasePrice,
                'old_selling_price' => $oldSellingPrice,
                'new_selling_price' => $newSellingPrice,
                'reason' => fake()->randomElement(['Kenaikan harga supplier', 'Promo spesial', 'Penyesuaian margin', 'Diskon musiman']),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
