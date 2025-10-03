<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::insert([
            [
                'business_id' => 1,
                'name' => 'Accra Central',
                'address' => 'Accra',
                'contact' => '0551234567',
            ],
            [
                'business_id' => 1,
                'name' => 'Kumasi Market',
                'address' => 'Kumasi',
                'contact' => '0249876543',
            ]
        ]);
    }
}
