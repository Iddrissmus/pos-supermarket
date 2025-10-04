<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Central Distribution Center',
                'type' => 'warehouse',
                'address' => '123 Industrial Park, Accra, Ghana',
                'phone' => '+233-20-123-4567',
                'email' => 'orders@centraldc.com',
                'contact_person' => 'Samuel Asante',
                'notes' => 'Main warehouse for general merchandise distribution',
                'is_active' => true,
            ],
            [
                'name' => 'GOIL Energy & Petroleum',
                'type' => 'manufacturer',
                'address' => '456 Energy Avenue, Tema, Ghana',
                'phone' => '+233-30-789-0123',
                'email' => 'supply@goil.com.gh',
                'contact_person' => 'Akosua Mensah',
                'notes' => 'Primary petroleum products supplier',
                'is_active' => true,
            ],
            [
                'name' => 'Ghana Food Distributors Ltd',
                'type' => 'external',
                'address' => '789 Market Street, Kumasi, Ghana',
                'phone' => '+233-32-456-7890',
                'email' => 'info@ghanafood.com',
                'contact_person' => 'Kwame Boateng',
                'notes' => 'Specialized in food and beverage products',
                'is_active' => true,
            ],
            [
                'name' => 'TechParts Manufacturing',
                'type' => 'manufacturer',
                'address' => '321 Technology Park, Accra, Ghana',
                'phone' => '+233-21-234-5678',
                'email' => 'sales@techparts.gh',
                'contact_person' => 'Ama Osei',
                'notes' => 'Electronics and automotive parts manufacturer',
                'is_active' => true,
            ],
            [
                'name' => 'West Africa Imports',
                'type' => 'external',
                'address' => '654 Port Road, Takoradi, Ghana',
                'phone' => '+233-31-567-8901',
                'email' => 'imports@westafricaltd.com',
                'contact_person' => 'Ibrahim Mohammed',
                'notes' => 'International goods importer and distributor',
                'is_active' => true,
            ],
            [
                'name' => 'Local Beverages Company',
                'type' => 'manufacturer',
                'address' => '987 Brewery Lane, Cape Coast, Ghana',
                'phone' => '+233-33-678-9012',
                'email' => 'orders@localbeverages.gh',
                'contact_person' => 'Grace Amponsah',
                'notes' => 'Local soft drinks and water manufacturer',
                'is_active' => true,
            ],
            [
                'name' => 'Regional Warehouse Solutions',
                'type' => 'warehouse',
                'address' => '147 Logistics Hub, Tamale, Ghana',
                'phone' => '+233-37-789-0123',
                'email' => 'operations@regwarehouse.com',
                'contact_person' => 'Abdul Rahman',
                'notes' => 'Northern region distribution center',
                'is_active' => true,
            ],
            [
                'name' => 'Quick Supplies & Services',
                'type' => 'external',
                'address' => '258 Business District, Ho, Ghana',
                'phone' => '+233-36-890-1234',
                'email' => 'quicksupplies@gmail.com',
                'contact_person' => 'Edem Agbeko',
                'notes' => 'Emergency and quick delivery supplier',
                'is_active' => true,
            ],
            [
                'name' => 'Inactive Old Supplier',
                'type' => 'external',
                'address' => '999 Old Street, Accra, Ghana',
                'phone' => '+233-20-999-9999',
                'email' => 'old@supplier.com',
                'contact_person' => 'Old Contact',
                'notes' => 'This supplier is no longer active',
                'is_active' => false,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
