<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxRate;

class TaxRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default VAT
        TaxRate::create([
            'name' => 'VAT',
            'rate' => 12.5,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        // NHIL (National Health Insurance Levy) - Standard in Ghana
        TaxRate::create([
            'name' => 'NHIL',
            'rate' => 2.5,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        // GETFund Levy
        TaxRate::create([
            'name' => 'GETFund',
            'rate' => 2.5,
            'type' => 'percentage',
            'is_active' => true,
        ]);
        
        // COVID-19 Health Recovery Levy
        TaxRate::create([
            'name' => 'COVID-19 HRL',
            'rate' => 1.0,
            'type' => 'percentage',
            'is_active' => true,
        ]);
    }
}
