<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $type = ProductType::create([
            'name' => 'Konsumsi'
        ]);

        Product::insert([[
            'product_type_id' => $type->id,
            'name' => 'Kopi'
        ], [
            'product_type_id' => $type->id,
            'name' => 'Teh'
        ]]);
    }
}
