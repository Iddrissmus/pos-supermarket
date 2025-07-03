<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StockTransfer;

class StockTransfersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockTransfer::create([
            'from_branch_id' => 1,
            'to_branch_id' => 2,
            'product_id' => 1,
            'quantity' => 100,
            'status' => 'completed',
        ]);
    }
}
