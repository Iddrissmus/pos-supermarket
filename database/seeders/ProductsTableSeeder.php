<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'business_id' => 1,
            'category_id' => 1,
            'name' => 'Royal Aroma Rice 5kg',
            'description' => 'Premium jasmine rice',
            'sku' => 'RICE-001',
            'image' => null,
        ]);
    }
}
