<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StockLog;

class StockLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockLog::create([
            'branch_id' => 1,
            'product_id' => 1,
            'action' => 'added',
            'quantity' => 50,
            'note' => 'Initial stock from supplier'
        ]);
    }
}
