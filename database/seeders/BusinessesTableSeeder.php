<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Business;

class BusinessesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Business::create([
            'name' => 'FreshMart Supermarket',
            'owner_id' => 1,
            'logo' => null,
        ]);
    }
}
