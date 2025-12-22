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
        $branchProducts = [
            // Main Branch (Branch 1) - Well stocked
            ['branch_id' => 1, 'product_id' => 1, 'stock_quantity' => 45, 'reorder_level' => 20, 'price' => 85.00, 'cost_price' => 65.00],
            ['branch_id' => 1, 'product_id' => 2, 'stock_quantity' => 30, 'reorder_level' => 15, 'price' => 95.00, 'cost_price' => 75.00],
            ['branch_id' => 1, 'product_id' => 3, 'stock_quantity' => 60, 'reorder_level' => 25, 'price' => 28.00, 'cost_price' => 22.00],
            ['branch_id' => 1, 'product_id' => 4, 'stock_quantity' => 8, 'reorder_level' => 15, 'price' => 12.50, 'cost_price' => 9.50], // LOW STOCK
            ['branch_id' => 1, 'product_id' => 5, 'stock_quantity' => 80, 'reorder_level' => 30, 'price' => 8.50, 'cost_price' => 6.50],
            ['branch_id' => 1, 'product_id' => 6, 'stock_quantity' => 120, 'reorder_level' => 50, 'price' => 3.50, 'cost_price' => 2.50],
            ['branch_id' => 1, 'product_id' => 7, 'stock_quantity' => 100, 'reorder_level' => 40, 'price' => 2.00, 'cost_price' => 1.50],
            ['branch_id' => 1, 'product_id' => 8, 'stock_quantity' => 55, 'reorder_level' => 25, 'price' => 35.00, 'cost_price' => 28.00],
            ['branch_id' => 1, 'product_id' => 9, 'stock_quantity' => 40, 'reorder_level' => 20, 'price' => 15.00, 'cost_price' => 12.00],
            ['branch_id' => 1, 'product_id' => 10, 'stock_quantity' => 5, 'reorder_level' => 12, 'price' => 4.50, 'cost_price' => 3.50], // LOW STOCK
            ['branch_id' => 1, 'product_id' => 11, 'stock_quantity' => 35, 'reorder_level' => 15, 'price' => 48.00, 'cost_price' => 38.00],
            ['branch_id' => 1, 'product_id' => 12, 'stock_quantity' => 25, 'reorder_level' => 10, 'price' => 22.00, 'cost_price' => 18.00],
            ['branch_id' => 1, 'product_id' => 13, 'stock_quantity' => 40, 'reorder_level' => 15, 'price' => 18.00, 'cost_price' => 14.00],
            ['branch_id' => 1, 'product_id' => 14, 'stock_quantity' => 50, 'reorder_level' => 20, 'price' => 12.00, 'cost_price' => 9.50],
            ['branch_id' => 1, 'product_id' => 15, 'stock_quantity' => 65, 'reorder_level' => 25, 'price' => 9.50, 'cost_price' => 7.50],
            
            // Downtown Branch (Branch 2) - Some low stock items
            ['branch_id' => 2, 'product_id' => 1, 'stock_quantity' => 25, 'reorder_level' => 20, 'price' => 85.00, 'cost_price' => 65.00],
            ['branch_id' => 2, 'product_id' => 2, 'stock_quantity' => 35, 'reorder_level' => 15, 'price' => 95.00, 'cost_price' => 75.00],
            ['branch_id' => 2, 'product_id' => 3, 'stock_quantity' => 18, 'reorder_level' => 25, 'price' => 28.00, 'cost_price' => 22.00], // LOW STOCK
            ['branch_id' => 2, 'product_id' => 6, 'stock_quantity' => 90, 'reorder_level' => 50, 'price' => 3.50, 'cost_price' => 2.50],
            ['branch_id' => 2, 'product_id' => 7, 'stock_quantity' => 75, 'reorder_level' => 40, 'price' => 2.00, 'cost_price' => 1.50],
            ['branch_id' => 2, 'product_id' => 8, 'stock_quantity' => 30, 'reorder_level' => 25, 'price' => 35.00, 'cost_price' => 28.00],
            ['branch_id' => 2, 'product_id' => 11, 'stock_quantity' => 20, 'reorder_level' => 15, 'price' => 48.00, 'cost_price' => 38.00],
            ['branch_id' => 2, 'product_id' => 15, 'stock_quantity' => 45, 'reorder_level' => 25, 'price' => 9.50, 'cost_price' => 7.50],
            ['branch_id' => 2, 'product_id' => 16, 'stock_quantity' => 35, 'reorder_level' => 20, 'price' => 12.00, 'cost_price' => 9.50],
            ['branch_id' => 2, 'product_id' => 20, 'stock_quantity' => 40, 'reorder_level' => 15, 'price' => 22.00, 'cost_price' => 18.00],
            
            // Airport Branch (Branch 3) - Critical low stock
            ['branch_id' => 3, 'product_id' => 1, 'stock_quantity' => 12, 'reorder_level' => 20, 'price' => 85.00, 'cost_price' => 65.00], // LOW STOCK
            ['branch_id' => 3, 'product_id' => 6, 'stock_quantity' => 38, 'reorder_level' => 50, 'price' => 3.50, 'cost_price' => 2.50], // LOW STOCK
            ['branch_id' => 3, 'product_id' => 7, 'stock_quantity' => 55, 'reorder_level' => 40, 'price' => 2.00, 'cost_price' => 1.50],
            ['branch_id' => 3, 'product_id' => 8, 'stock_quantity' => 15, 'reorder_level' => 25, 'price' => 35.00, 'cost_price' => 28.00], // LOW STOCK
            ['branch_id' => 3, 'product_id' => 15, 'stock_quantity' => 30, 'reorder_level' => 25, 'price' => 9.50, 'cost_price' => 7.50],
            ['branch_id' => 3, 'product_id' => 16, 'stock_quantity' => 25, 'reorder_level' => 20, 'price' => 12.00, 'cost_price' => 9.50],
            ['branch_id' => 3, 'product_id' => 21, 'stock_quantity' => 18, 'reorder_level' => 15, 'price' => 28.00, 'cost_price' => 22.00],
            ['branch_id' => 3, 'product_id' => 22, 'stock_quantity' => 30, 'reorder_level' => 20, 'price' => 15.00, 'cost_price' => 12.00],
        ];

        foreach ($branchProducts as $branchProduct) {
            BranchProduct::create($branchProduct);
        }

        $this->command->info('✓ Branch products seeded successfully (Including low stock items for testing)');
    }
}
