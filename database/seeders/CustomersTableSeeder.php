<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'customer_number' => 'CUST-001',
                'name' => 'Kwame Mensah',
                'company' => null,
                'email' => 'kwame.mensah@email.com',
                'phone' => '+233 24 123 4567',
                'address' => '12 Cantonments Road',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => 'GA-123-4567',
                'customer_type' => 'individual',
                'outstanding_balance' => 0,
                'payment_terms' => 'immediate',
                'notes' => 'Regular customer',
            ],
            [
                'customer_number' => 'CUST-002',
                'name' => 'Akosua Boateng',
                'company' => null,
                'email' => 'akosua.b@email.com',
                'phone' => '+233 50 234 5678',
                'address' => '45 Osu Link',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => 'GA-234-5678',
                'customer_type' => 'individual',
                'outstanding_balance' => 0,
                'payment_terms' => 'immediate',
                'notes' => 'Loyal customer - weekly shopper',
            ],
            [
                'customer_number' => 'CUST-003',
                'name' => 'Ama Adjei',
                'company' => 'Adjei Catering Services',
                'email' => 'ama@adjeicatering.com',
                'phone' => '+233 24 345 6789',
                'address' => '78 Adum Commercial Area',
                'city' => 'Kumasi',
                'state' => 'Ashanti',
                'country' => 'Ghana',
                'postal_code' => 'AK-345-6789',
                'customer_type' => 'business',
                'outstanding_balance' => 1250.00,
                'payment_terms' => 'net_30',
                'notes' => 'Bulk buyer - catering business',
            ],
            [
                'customer_number' => 'CUST-004',
                'name' => 'Kofi Asante',
                'company' => null,
                'email' => 'kofi.asante@email.com',
                'phone' => '+233 26 456 7890',
                'address' => '34 Airport Residential Area',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => 'GA-456-7890',
                'customer_type' => 'individual',
                'outstanding_balance' => 0,
                'payment_terms' => 'immediate',
                'notes' => 'Prefers mobile money payments',
            ],
            [
                'customer_number' => 'CUST-005',
                'name' => 'Yaa Mensah',
                'company' => 'Yaa\'s Restaurant',
                'email' => 'yaa@yaasrestaurant.com',
                'phone' => '+233 27 567 8901',
                'address' => '56 Market Circle',
                'city' => 'Takoradi',
                'state' => 'Western',
                'country' => 'Ghana',
                'postal_code' => 'WR-567-8901',
                'customer_type' => 'business',
                'outstanding_balance' => 500.00,
                'payment_terms' => 'net_15',
                'notes' => 'Restaurant owner - weekly orders',
            ],
            [
                'customer_number' => 'CUST-006',
                'name' => 'Nana Osei',
                'company' => null,
                'email' => 'nana.osei@email.com',
                'phone' => '+233 20 678 9012',
                'address' => '89 Legon Campus',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => 'GA-678-9012',
                'customer_type' => 'individual',
                'outstanding_balance' => 0,
                'payment_terms' => 'immediate',
                'notes' => 'Student - prefers discounts',
            ],
            [
                'customer_number' => 'CUST-007',
                'name' => 'Abena Owusu',
                'company' => 'Owusu Trading Co.',
                'email' => 'abena@owusutrading.com',
                'phone' => '+233 24 789 0123',
                'address' => '23 Kejetia Market',
                'city' => 'Kumasi',
                'state' => 'Ashanti',
                'country' => 'Ghana',
                'postal_code' => 'AK-789-0123',
                'customer_type' => 'business',
                'outstanding_balance' => 2500.00,
                'payment_terms' => 'net_30',
                'notes' => 'Large wholesale orders - priority customer',
            ],
            [
                'customer_number' => 'CUST-008',
                'name' => 'Kwesi Darko',
                'company' => null,
                'email' => 'kwesi.darko@email.com',
                'phone' => '+233 55 890 1234',
                'address' => '67 Spintex Road',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => 'GA-890-1234',
                'customer_type' => 'individual',
                'outstanding_balance' => 0,
                'payment_terms' => 'immediate',
                'notes' => 'New customer',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('✓ Created ' . count($customers) . ' customers');
    }
}
