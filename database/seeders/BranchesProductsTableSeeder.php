<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BranchProduct;

class BranchesProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BranchProduct::create([
            'branch_id' => 1,
            'product_id' => 1,
            'stock_quantity' => 50,
            'reorder_level' => 10,
            'price' => 80.00,
            'cost_price' => 60.00,
        ]);
    }
}
