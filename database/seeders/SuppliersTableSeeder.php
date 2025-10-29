<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Ghana Food Distributors Ltd',
                'type' => 'warehouse',
                'address' => '123 Industrial Area, Accra',
                'phone' => '0302123456',
                'email' => 'info@ghanafood.com',
                'contact_person' => 'Kwame Asante',
                'notes' => 'Main food supplier',
                'is_active' => true,
            ],
            [
                'name' => 'Unilever Ghana Limited',
                'type' => 'manufacturer',
                'address' => '456 Spintex Road, Accra',
                'phone' => '0302789012',
                'email' => 'orders@unilever.com.gh',
                'contact_person' => 'Akosua Mensah',
                'notes' => 'Personal care products',
                'is_active' => true,
            ],
            [
                'name' => 'Nestlé Ghana Ltd',
                'type' => 'manufacturer',
                'address' => '789 Ring Road East, Accra',
                'phone' => '0302345678',
                'email' => 'supply@nestle.com.gh',
                'contact_person' => 'Kofi Boateng',
                'notes' => 'Beverages and food products',
                'is_active' => true,
            ],
            [
                'name' => 'Coca-Cola Bottling Company',
                'type' => 'manufacturer',
                'address' => '321 Liberation Road, Accra',
                'phone' => '0302456789',
                'email' => 'distribution@coca-cola.com.gh',
                'contact_person' => 'Ama Serwaa',
                'notes' => 'Soft drinks and beverages',
                'is_active' => true,
            ],
            [
                'name' => 'Tropical Cables & Electrical',
                'type' => 'external',
                'address' => '234 Achimota Road, Accra',
                'phone' => '0302567890',
                'email' => 'sales@tropicalcables.com',
                'contact_person' => 'Yaw Mensah',
                'notes' => 'Electronics supplies',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('✓ Suppliers seeded successfully');
    }
}
