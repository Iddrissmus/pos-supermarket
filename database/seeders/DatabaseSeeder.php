<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
        UsersTableSeeder::class,
        BusinessesTableSeeder::class,
        BranchesTableSeeder::class,
        CategoriesTableSeeder::class,
        ProductsTableSeeder::class,
        BranchesProductsTableSeeder::class,
        SalesTableSeeder::class,
        SalesItemsTableSeeder::class,
        StockTransfersTableSeeder::class,
        StockLogsTableSeeder::class,
    ]);
        
    }
}
