<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Business;

class ComprehensiveCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates a comprehensive category structure for a Ghanaian supermarket
     * covering all types of products and inventory items.
     */
    public function run(): void
    {
        $business = Business::first();
        
        if (!$business) {
            $this->command->error('No business found. Please create a business first.');
            return;
        }

        $this->command->info('Creating comprehensive category structure...');

        // Define comprehensive category structure
        $categories = [
            'Food & Groceries' => [
                'Rice & Grains',
                'Flour & Baking',
                'Oils & Fats',
                'Spices & Seasonings',
                'Sugar & Sweeteners',
                'Canned & Packaged Foods',
                'Pasta & Noodles',
                'Breakfast Cereals',
                'Sauces & Condiments',
                'Soups & Stock',
            ],
            
            'Beverages' => [
                'Soft Drinks',
                'Water',
                'Juices',
                'Tea & Coffee',
                'Energy Drinks',
                'Alcoholic Drinks',
                'Wine & Spirits',
                'Malt Drinks',
            ],
            
            'Dairy & Eggs' => [
                'Milk',
                'Yogurt',
                'Cheese',
                'Butter & Margarine',
                'Eggs',
                'Cream & Condensed Milk',
            ],
            
            'Meat & Seafood' => [
                'Fresh Meat',
                'Fresh Fish',
                'Frozen Meat',
                'Frozen Fish',
                'Poultry',
                'Processed Meats',
                'Dried Fish & Seafood',
            ],
            
            'Fresh Produce' => [
                'Vegetables',
                'Fruits',
                'Herbs & Spices (Fresh)',
                'Salads & Greens',
            ],
            
            'Frozen Foods' => [
                'Frozen Vegetables',
                'Frozen Fish',
                'Frozen Meat',
                'Ice Cream',
                'Frozen Snacks',
                'Frozen Ready Meals',
            ],
            
            'Snacks & Confectionery' => [
                'Chips & Crisps',
                'Biscuits & Cookies',
                'Chocolates',
                'Candies & Sweets',
                'Nuts & Seeds',
                'Popcorn',
                'Energy Bars',
            ],
            
            'Bakery' => [
                'Bread',
                'Pastries',
                'Cakes',
                'Buns & Rolls',
            ],
            
            'Personal Care' => [
                'Soap & Body Wash',
                'Shampoo & Conditioner',
                'Hair Care',
                'Skin Care',
                'Toothpaste & Oral Care',
                'Deodorants & Perfumes',
                'Shaving & Grooming',
                'Feminine Hygiene',
                'Men\'s Care',
            ],
            
            'Household Items' => [
                'Detergents & Washing',
                'Cleaning Supplies',
                'Tissue & Paper Products',
                'Kitchen Supplies',
                'Air Fresheners',
                'Trash Bags',
                'Insecticides & Pest Control',
                'Batteries',
                'Light Bulbs',
            ],
            
            'Baby Products' => [
                'Diapers & Wipes',
                'Baby Food',
                'Baby Care',
                'Baby Toiletries',
                'Feeding Accessories',
            ],
            
            'Health & Wellness' => [
                'Vitamins & Supplements',
                'First Aid',
                'Pain Relief',
                'Cold & Flu',
                'Digestive Health',
                'Health Devices',
            ],
            
            'Electronics & Gadgets' => [
                'Phone Accessories',
                'Chargers & Cables',
                'Batteries',
                'Bulbs & Lighting',
                'Small Appliances',
                'Audio Accessories',
            ],
            
            'Stationery & Books' => [
                'Notebooks & Paper',
                'Pens & Pencils',
                'School Supplies',
                'Art Supplies',
                'Books & Magazines',
            ],
            
            'Pet Care' => [
                'Pet Food',
                'Pet Accessories',
                'Pet Hygiene',
            ],
            
            'Household Essentials' => [
                'Matches & Lighters',
                'Candles',
                'Storage & Organization',
                'Laundry Accessories',
            ],
            
            'Traditional & Local' => [
                'Local Spices',
                'Traditional Foods',
                'Local Snacks',
                'Palm Products',
                'Shea Products',
            ],
            
            'General Merchandise' => [
                'Kitchen Utensils',
                'Tableware',
                'Glassware',
                'Plastic Ware',
                'Gift Items',
                'Seasonal Items',
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($categories as $parentName => $subcategories) {
            // Create or get parent category
            $parent = Category::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $parentName,
                    'parent_id' => null,
                ],
                [
                    'is_active' => true,
                    'display_order' => $createdCount + 1,
                ]
            );

            if ($parent->wasRecentlyCreated) {
                $createdCount++;
                $this->command->info("✓ Created parent: {$parentName}");
            } else {
                $skippedCount++;
                $this->command->warn("⊘ Skipped (exists): {$parentName}");
            }

            // Create subcategories
            foreach ($subcategories as $index => $subName) {
                $subcategory = Category::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $subName,
                        'parent_id' => $parent->id,
                    ],
                    [
                        'is_active' => true,
                        'display_order' => $index + 1,
                    ]
                );

                if ($subcategory->wasRecentlyCreated) {
                    $createdCount++;
                    $this->command->info("  ✓ Created: {$subName}");
                } else {
                    $skippedCount++;
                }
            }
        }

        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("Category seeding complete!");
        $this->command->info("Created: {$createdCount} | Skipped: {$skippedCount}");
        $this->command->info(str_repeat('=', 80));
    }
}
