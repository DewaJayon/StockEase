<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = database_path('seeders/json/suppliers.json');

        $suppliers = json_decode(file_get_contents($suppliers), true);

        foreach ($suppliers as $supplier) {
            Supplier::create([
                'slug' => $supplier['slug'],
                'name' => $supplier['name'],
                'phone' => $supplier['phone'],
                'address' => $supplier['address']
            ]);
        }
    }
}
