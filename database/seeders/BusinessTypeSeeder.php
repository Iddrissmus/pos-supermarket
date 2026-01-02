<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Retail Store',
                'description' => 'General retail shop selling varied text-itemsgoods.',
            ],
            [
                'name' => 'Supermarket',
                'description' => 'Large grocery store with wide variety of products.',
            ],
            [
                'name' => 'Pharmacy',
                'description' => 'Drugstore or pharmacy selling medicines and health products.',
            ],
            [
                'name' => 'Wholesaler',
                'description' => 'Business selling goods in large quantities to retailers.',
            ],
            [
                'name' => 'Restaurant / Cafe',
                'description' => 'Food and beverage service establishment.',
            ],
            [
                'name' => 'Service Provider',
                'description' => 'Business offering services rather than physical goods.',
            ],
            [
                'name' => 'Other',
                'description' => 'Other business types.',
            ],
        ];

        foreach ($types as $type) {
            BusinessType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
