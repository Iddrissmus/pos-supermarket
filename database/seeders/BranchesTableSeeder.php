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
            // FreshMart Branches
            [
                'business_id' => 1,
                'name' => 'Main Branch',
                'address' => '123 Oxford Street, Accra',
                'contact' => '0551234567',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => 1,
                'name' => 'Downtown Branch',
                'address' => '456 Ring Road, Kumasi',
                'contact' => '0249876543',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => 1,
                'name' => 'Airport Branch',
                'address' => '789 Airport Road, Accra',
                'contact' => '0201122334',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // QuickShop Branches
            [
                'business_id' => 2,
                'name' => 'Central Store',
                'address' => '321 High Street, Takoradi',
                'contact' => '0243344556',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✓ Branches seeded successfully');
    }
}
