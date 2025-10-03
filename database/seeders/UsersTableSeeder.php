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
        User::firstOrCreate(    //won't create duplicates
            ['email' => 'admin@example.com'],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]
);
    }
}
