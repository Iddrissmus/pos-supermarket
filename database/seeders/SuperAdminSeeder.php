<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $superAdminExists = User::where('role', User::ROLE_SUPERADMIN)->exists();

        if (!$superAdminExists){
            User::create([
                'name' => 'System Administrator',
                'email' => 'superadmin@pos.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPERADMIN,
            ]);

            $this->command->info('Super Admin user created: superadmin@pos.com / password');
        } else {
            $this->command->info('Super Admin user already exists. No action taken.');
        }
    }
}
