<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Groceries
            [
                'business_id' => 1,
                'category_id' => 1,
                'name' => 'Royal Aroma Rice 5kg',
                'description' => 'Premium jasmine rice from Thailand',
                'sku' => 'RICE-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 1,
                'name' => 'Pure Vegetable Oil 3L',
                'description' => 'Pure vegetable cooking oil',
                'sku' => 'OIL-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 1,
                'name' => 'Golden Penny Flour 2kg',
                'description' => 'All-purpose wheat flour',
                'sku' => 'FLOUR-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 1,
                'name' => 'Titus Sardine 425g',
                'description' => 'Sardines in tomato sauce',
                'sku' => 'FISH-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 1,
                'name' => 'Sugar 1kg',
                'description' => 'Refined white sugar',
                'sku' => 'SUGAR-001',
                'image' => null,
            ],

            // Beverages
            [
                'business_id' => 1,
                'category_id' => 2,
                'name' => 'Coca-Cola 500ml',
                'description' => 'Classic Coca-Cola soft drink',
                'sku' => 'DRINK-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 2,
                'name' => 'Voltic Mineral Water 750ml',
                'description' => 'Pure mineral water',
                'sku' => 'WATER-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 2,
                'name' => 'Milo 400g',
                'description' => 'Chocolate malt drink',
                'sku' => 'MILO-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 2,
                'name' => 'Lipton Tea 100 Bags',
                'description' => 'Yellow label tea bags',
                'sku' => 'TEA-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 2,
                'name' => 'Malta Guinness 330ml',
                'description' => 'Non-alcoholic malt drink',
                'sku' => 'MALTA-001',
                'image' => null,
            ],

            // Household Items
            [
                'business_id' => 1,
                'category_id' => 3,
                'name' => 'Omo Washing Powder 900g',
                'description' => 'Multi-active washing powder',
                'sku' => 'DETERG-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 3,
                'name' => 'Toilet Tissue 10 Rolls',
                'description' => 'Soft toilet tissue',
                'sku' => 'TISSUE-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 3,
                'name' => 'Vim Dishwashing Liquid 750ml',
                'description' => 'Lemon scented dish soap',
                'sku' => 'DISH-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 3,
                'name' => 'Air Freshener 300ml',
                'description' => 'Lavender scent room freshener',
                'sku' => 'FRESH-001',
                'image' => null,
            ],

            // Personal Care
            [
                'business_id' => 1,
                'category_id' => 4,
                'name' => 'Colgate Toothpaste 125ml',
                'description' => 'Triple action toothpaste',
                'sku' => 'TOOTH-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 4,
                'name' => 'Dove Soap 100g',
                'description' => 'Moisturizing beauty bar',
                'sku' => 'SOAP-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 4,
                'name' => 'Nivea Body Lotion 400ml',
                'description' => 'Nourishing body lotion',
                'sku' => 'LOTION-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 4,
                'name' => 'Head & Shoulders Shampoo 200ml',
                'description' => 'Anti-dandruff shampoo',
                'sku' => 'SHAMP-001',
                'image' => null,
            ],

            // Electronics
            [
                'business_id' => 1,
                'category_id' => 5,
                'name' => 'LED Bulb 15W',
                'description' => 'Energy-saving LED bulb',
                'sku' => 'BULB-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 5,
                'name' => 'USB Cable Type-C',
                'description' => '1.5m fast charging cable',
                'sku' => 'CABLE-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 5,
                'name' => 'AA Batteries Pack of 4',
                'description' => 'Alkaline batteries',
                'sku' => 'BATT-001',
                'image' => null,
            ],

            // Snacks & Confectionery
            [
                'business_id' => 1,
                'category_id' => 6,
                'name' => 'Pringles Original 165g',
                'description' => 'Potato chips',
                'sku' => 'CHIPS-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 6,
                'name' => 'Cadbury Chocolate 200g',
                'description' => 'Dairy milk chocolate bar',
                'sku' => 'CHOCO-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 6,
                'name' => 'Tom Tom Candy',
                'description' => 'Menthol flavored candy',
                'sku' => 'CANDY-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 6,
                'name' => 'Biscuit Digestive 400g',
                'description' => 'Whole wheat biscuits',
                'sku' => 'BISCUIT-001',
                'image' => null,
            ],

            // Dairy & Eggs
            [
                'business_id' => 1,
                'category_id' => 7,
                'name' => 'Fresh Milk 1L',
                'description' => 'Full cream fresh milk',
                'sku' => 'MILK-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 7,
                'name' => 'Butter 500g',
                'description' => 'Salted butter',
                'sku' => 'BUTTER-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 7,
                'name' => 'Eggs Tray (30 pieces)',
                'description' => 'Fresh farm eggs',
                'sku' => 'EGGS-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 7,
                'name' => 'Cheese Slices 200g',
                'description' => 'Processed cheese slices',
                'sku' => 'CHEESE-001',
                'image' => null,
            ],

            // Frozen Foods
            [
                'business_id' => 1,
                'category_id' => 8,
                'name' => 'Frozen Chicken 1kg',
                'description' => 'Whole frozen chicken',
                'sku' => 'CHICKEN-001',
                'image' => null,
            ],
            [
                'business_id' => 1,
                'category_id' => 8,
                'name' => 'Fish Fillet 500g',
                'description' => 'Frozen tilapia fillet',
                'sku' => 'FILLET-001',
                'image' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('✓ Products seeded successfully (' . count($products) . ' items)');
    }
}
