<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily disable model events to allow creating cashiers without branch_id
        User::withoutEvents(function () {
            // Business Admin User - Will be assigned to first business
            User::updateOrCreate(
                ['email' => 'businessadmin@pos.com'],
                [
                    'name' => 'Business Administrator',
                    'password' => Hash::make('password'),
                    'role' => 'business_admin',
                    'business_id' => 1, // Will be assigned to first business
                ]
            );

            // Manager User
            User::firstOrCreate(
                ['email' => 'manager@pos.com'],
                [
                    'name' => 'Manager User',
                    'password' => Hash::make('password'),
                    'role' => 'manager',
                ]
            );

            // Cashier User - Branch will be assigned later
            User::firstOrCreate(
                ['email' => 'cashier@pos.com'],
                [
                    'name' => 'Cashier User',
                    'password' => Hash::make('password'),
                    'role' => 'cashier',
                    'branch_id' => null, // Will be assigned after branches exist
                ]
            );

            // Additional manager for second branch
            User::firstOrCreate(
                ['email' => 'manager2@pos.com'],
                [
                    'name' => 'Branch Manager 2',
                    'password' => Hash::make('password'),
                    'role' => 'manager',
                ]
            );

            // Additional cashier - Branch will be assigned later
            User::firstOrCreate(
                ['email' => 'cashier2@pos.com'],
                [
                    'name' => 'Cashier 2',
                    'password' => Hash::make('password'),
                    'role' => 'cashier',
                    'branch_id' => null, // Will be assigned after branches exist
                ]
            );
        });

        $this->command->info('✓ Users seeded successfully (Password: password)');
    }
}
