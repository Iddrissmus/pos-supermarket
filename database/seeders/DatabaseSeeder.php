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
            SuperAdminSeeder::class, // Create superadmin first (no dependencies)
            BusinessesTableSeeder::class, // Then create businesses
            UsersTableSeeder::class, // Then create business admin, managers, cashiers
            BranchesTableSeeder::class,
            AssignCashiersToBranchesSeeder::class, // Assign cashiers after branches exist
            SuppliersTableSeeder::class,
            CategoriesTableSeeder::class,
            ProductsTableSeeder::class,
            BranchesProductsTableSeeder::class,
            CustomersTableSeeder::class, // Create customers
            StockReceiptsTableSeeder::class, // Create stock receipts
            SalesTableSeeder::class, // Create sales data
            NotificationsTableSeeder::class, // Create low stock notifications
            // SalesItemsTableSeeder::class,
            // StockTransfersTableSeeder::class,
            // StockLogsTableSeeder::class,
        ]);
        
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Test Accounts:');
        $this->command->info('  SuperAdmin:       superadmin@pos.com      / password123');
        $this->command->info('  Business Admin:   businessadmin@pos.com   / password');
        $this->command->info('  Manager:          manager@pos.com         / password');
        $this->command->info('  Cashier:          cashier@pos.com         / password');
        $this->command->info('========================================');
    }
}
