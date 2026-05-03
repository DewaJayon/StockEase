<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = database_path('seeders/json/promotions.json');
        $promotions = json_decode(file_get_contents($promotions), true);

        foreach ($promotions as $promo) {
            $categoryId = null;
            $productId = null;

            if (! empty($promo['category_slug'])) {
                $categoryId = Category::where('slug', $promo['category_slug'])->value('id');
            }

            if (! empty($promo['product_slug'])) {
                $productId = Product::where('slug', $promo['product_slug'])->value('id');
            }

            Promotion::create([
                'name' => $promo['name'],
                'type' => $promo['type'],
                'discount_value' => $promo['discount_value'] ?? null,
                'buy_qty' => $promo['buy_qty'] ?? null,
                'get_qty' => $promo['get_qty'] ?? null,
                'category_id' => $categoryId,
                'product_id' => $productId,
                'start_date' => now()->addDays($promo['start_date_offset']),
                'end_date' => now()->addDays($promo['end_date_offset']),
                'is_active' => $promo['is_active'],
            ]);
        }
    }
}
