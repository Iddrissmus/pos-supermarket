<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BusinessesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure business admin exists
        $admin = User::find(2);
        if (!$admin) {
             // Create a placeholder user or the actual business admin if missing
             // We can rely on UsersTableSeeder to fill in details later or just create it here
             User::withoutEvents(function () use (&$admin) {
                 $admin = User::forceCreate([
                    'id' => 2,
                    'name' => 'Business Administrator',
                    'email' => 'businessadmin@pos.com',
                    'password' => '$2y$12$K.x.xyz...', // dummy hash or Hash::make('password') if imported
                    'role' => 'business_admin',
                    'business_id' => null, // validation skipped
                 ]);
             });
        }

        Business::create([
            'name' => 'FreshMart Supermarket',
            'business_admin_id' => $admin->id, 
            'logo' => null,
        ]);

        Business::create([
            'name' => 'QuickShop Retail',
            'business_admin_id' => 1, // SuperAdmin 
            'logo' => null,
        ]);

        $this->command->info('✓ Businesses seeded successfully');
    }
}
