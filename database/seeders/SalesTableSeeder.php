<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sale;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sale::create([
            'branch_id' => 1,
            'cashier_id' => 1,
            'total' => 160.00,
            'payment_method' => 'cash',
        ]);
    }
}
