<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::create([
            'name' => 'Dewa Jayon',
            'email' => 'dewajayon3@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);
    }
}
