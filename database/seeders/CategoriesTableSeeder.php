<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // FreshMart Categories
            ['business_id' => 1, 'name' => 'Groceries'],
            ['business_id' => 1, 'name' => 'Beverages'],
            ['business_id' => 1, 'name' => 'Household Items'],
            ['business_id' => 1, 'name' => 'Personal Care'],
            ['business_id' => 1, 'name' => 'Electronics'],
            ['business_id' => 1, 'name' => 'Snacks & Confectionery'],
            ['business_id' => 1, 'name' => 'Dairy & Eggs'],
            ['business_id' => 1, 'name' => 'Frozen Foods'],
            
            // QuickShop Categories
            ['business_id' => 2, 'name' => 'Food Items'],
            ['business_id' => 2, 'name' => 'Drinks'],
            ['business_id' => 2, 'name' => 'Home & Kitchen'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✓ Categories seeded successfully');
    }
}
