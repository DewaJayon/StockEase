<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockAdjustmentSeeder extends Seeder
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

        // Create 20 stock adjustments
        for ($i = 0; $i < 20; $i++) {
            DB::transaction(function () use ($users, $products) {
                $product = $products->random();
                $user = $users->random();
                $date = Carbon::now()->subtract('days', rand(0, 30));

                $oldStock = $product->stock;
                $adjustment = rand(-5, 5);

                if ($adjustment == 0) {
                    $adjustment = 1;
                }

                $newStock = max(0, $oldStock + $adjustment);

                if ($oldStock == $newStock) {
                    return;
                }

                StockAdjustment::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reason' => fake()->randomElement(['Barang Rusak', 'Kesalahan Input', 'Barang Kadaluarsa', 'Stok Opname']),
                    'date' => $date->format('Y-m-d'),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $product->update(['stock' => $newStock]);

                StockLog::create([
                    'product_id' => $product->id,
                    'qty' => abs($adjustment),
                    'type' => 'adjust',
                    'reference_type' => 'StockAdjustment',
                    'reference_id' => null,
                    'note' => 'Penyesuaian stok (Seeded)',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            });
        }
    }
}
