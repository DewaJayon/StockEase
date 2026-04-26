<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();
        $users = User::all();
        $products = Product::all();

        if ($suppliers->isEmpty() || $users->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create purchases for the last 3 months
        for ($i = 90; $i >= 0; $i -= rand(5, 10)) {
            DB::transaction(function () use ($i, $suppliers, $users, $products) {
                $date = Carbon::now()->subtract('days', $i);
                $purchase = Purchase::create([
                    'supplier_id' => $suppliers->random()->id,
                    'user_id' => $users->random()->id,
                    'total' => 0,
                    'date' => $date->format('Y-m-d'),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $total = 0;
                $itemsCount = rand(2, 5);
                $selectedProducts = $products->random($itemsCount);

                foreach ($selectedProducts as $product) {
                    $qty = rand(10, 50);
                    $price = $product->purchase_price > 0 ? $product->purchase_price : rand(1000, 5000);
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'remaining_qty' => $qty,
                        'price' => $price,
                        'expiry_date' => Carbon::parse($date)->addMonths(rand(6, 24))->format('Y-m-d'),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $product->increment('stock', $qty);

                    StockLog::create([
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'type' => 'in',
                        'reference_type' => 'Purchase',
                        'reference_id' => $purchase->id,
                        'note' => "Pembelian produk {$product->name} (Seeded)",
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }

                $purchase->update(['total' => $total]);
            });
        }
    }
}
