<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = database_path('seeders/json/products.json');
        $products = json_decode(file_get_contents($products), true);

        foreach ($products as $product) {
            Product::create([
                'category_id' => $product['category_id'],
                'slug' => $product['slug'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'barcode' => $product['barcode'],
                'unit' => $product['unit'],
                'stock' => $product['stock'],
                'purchase_price' => $product['purchase_price'],
                'selling_price' => $product['selling_price'],
                'alert_stock' => $product['alert_stock'],
                'image_path' => $product['image_path']
            ]);
        }
    }
}
