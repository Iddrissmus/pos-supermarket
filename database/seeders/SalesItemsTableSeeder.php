<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SaleItem;

class SalesItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SaleItem::create([
            'sale_id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'price' => 80.00,
            'total' => 160.00,
        ]);
    }
}
