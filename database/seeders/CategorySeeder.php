<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = database_path('seeders/json/category.json');
        $categories = json_decode(file_get_contents($categories), true);

        foreach ($categories as $category) {
            Category::create([
                'slug' => $category['slug'],
                'name' => $category['name']
            ]);
        }
    }
}
