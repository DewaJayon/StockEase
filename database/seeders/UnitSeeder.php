<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Pcs', 'short_name' => 'pcs'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'gram'],
            ['name' => 'Liter', 'short_name' => 'liter'],
            ['name' => 'Pack', 'short_name' => 'pack'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['short_name' => $unit['short_name']],
                [
                    'name' => $unit['name'],
                    'slug' => Str::slug($unit['name']),
                ]
            );
        }
    }
}
