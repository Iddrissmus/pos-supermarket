<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Business;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get all businesses
        $businesses = Business::all();

        if ($businesses->isEmpty()) {
            $this->command->error('⚠️  No businesses found. Please seed businesses first.');
            return;
        }

        // Comprehensive Ghana-focused categories
        $categoriesData = [
            [
                'name' => 'Food & Groceries',
                'icon' => 'fa-shopping-basket',
                'color' => 'green',
                'description' => 'Fresh and packaged food items',
                'subcategories' => [
                    ['name' => 'Rice & Grains', 'icon' => 'fa-seedling', 'color' => 'yellow'],
                    ['name' => 'Oils & Fats', 'icon' => 'fa-bottle-droplet', 'color' => 'amber'],
                    ['name' => 'Canned & Packaged Foods', 'icon' => 'fa-box', 'color' => 'orange'],
                    ['name' => 'Spices & Seasonings', 'icon' => 'fa-mortar-pestle', 'color' => 'red'],
                    ['name' => 'Flour & Baking', 'icon' => 'fa-wheat-awn', 'color' => 'yellow'],
                    ['name' => 'Sugar & Sweeteners', 'icon' => 'fa-candy-cane', 'color' => 'pink'],
                ]
            ],
            [
                'name' => 'Beverages',
                'icon' => 'fa-glass-water',
                'color' => 'blue',
                'description' => 'Drinks and beverages',
                'subcategories' => [
                    ['name' => 'Soft Drinks', 'icon' => 'fa-bottle-water', 'color' => 'blue'],
                    ['name' => 'Water', 'icon' => 'fa-droplet', 'color' => 'cyan'],
                    ['name' => 'Juices', 'icon' => 'fa-lemon', 'color' => 'orange'],
                    ['name' => 'Alcoholic Drinks', 'icon' => 'fa-wine-bottle', 'color' => 'purple'],
                    ['name' => 'Tea & Coffee', 'icon' => 'fa-mug-hot', 'color' => 'brown'],
                    ['name' => 'Energy Drinks', 'icon' => 'fa-bolt', 'color' => 'yellow'],
                ]
            ],
            [
                'name' => 'Dairy & Eggs',
                'icon' => 'fa-egg',
                'color' => 'yellow',
                'description' => 'Dairy products and eggs',
                'subcategories' => [
                    ['name' => 'Milk', 'icon' => 'fa-glass-water', 'color' => 'white'],
                    ['name' => 'Yogurt', 'icon' => 'fa-ice-cream', 'color' => 'pink'],
                    ['name' => 'Cheese', 'icon' => 'fa-cheese', 'color' => 'yellow'],
                    ['name' => 'Butter & Margarine', 'icon' => 'fa-cube', 'color' => 'yellow'],
                    ['name' => 'Eggs', 'icon' => 'fa-egg', 'color' => 'amber'],
                ]
            ],
            [
                'name' => 'Snacks & Confectionery',
                'icon' => 'fa-cookie-bite',
                'color' => 'orange',
                'description' => 'Snacks, sweets, and treats',
                'subcategories' => [
                    ['name' => 'Biscuits & Cookies', 'icon' => 'fa-cookie', 'color' => 'brown'],
                    ['name' => 'Chips & Crisps', 'icon' => 'fa-bag-shopping', 'color' => 'red'],
                    ['name' => 'Chocolates', 'icon' => 'fa-chocolate-bar', 'color' => 'brown'],
                    ['name' => 'Candies & Sweets', 'icon' => 'fa-candy', 'color' => 'pink'],
                    ['name' => 'Nuts & Seeds', 'icon' => 'fa-peanut', 'color' => 'amber'],
                    ['name' => 'Popcorn', 'icon' => 'fa-popcorn', 'color' => 'yellow'],
                ]
            ],
            [
                'name' => 'Personal Care',
                'icon' => 'fa-hand-sparkles',
                'color' => 'purple',
                'description' => 'Personal hygiene and care products',
                'subcategories' => [
                    ['name' => 'Soap & Body Wash', 'icon' => 'fa-soap', 'color' => 'blue'],
                    ['name' => 'Shampoo & Conditioner', 'icon' => 'fa-pump-soap', 'color' => 'purple'],
                    ['name' => 'Toothpaste & Oral Care', 'icon' => 'fa-tooth', 'color' => 'cyan'],
                    ['name' => 'Skin Care', 'icon' => 'fa-hand-holding-droplet', 'color' => 'pink'],
                    ['name' => 'Deodorants & Perfumes', 'icon' => 'fa-spray-can', 'color' => 'purple'],
                    ['name' => 'Hair Care', 'icon' => 'fa-head-side-medical', 'color' => 'brown'],
                ]
            ],
            [
                'name' => 'Household Items',
                'icon' => 'fa-house',
                'color' => 'indigo',
                'description' => 'Household cleaning and supplies',
                'subcategories' => [
                    ['name' => 'Detergents & Washing', 'icon' => 'fa-jug-detergent', 'color' => 'blue'],
                    ['name' => 'Cleaning Supplies', 'icon' => 'fa-broom', 'color' => 'green'],
                    ['name' => 'Air Fresheners', 'icon' => 'fa-wind', 'color' => 'cyan'],
                    ['name' => 'Tissue & Paper Products', 'icon' => 'fa-toilet-paper', 'color' => 'gray'],
                    ['name' => 'Trash Bags', 'icon' => 'fa-trash-can', 'color' => 'gray'],
                    ['name' => 'Kitchen Supplies', 'icon' => 'fa-kitchen-set', 'color' => 'red'],
                ]
            ],
            [
                'name' => 'Baby Products',
                'icon' => 'fa-baby',
                'color' => 'pink',
                'description' => 'Baby care and products',
                'subcategories' => [
                    ['name' => 'Diapers & Wipes', 'icon' => 'fa-child-dress', 'color' => 'blue'],
                    ['name' => 'Baby Food', 'icon' => 'fa-bottle-baby', 'color' => 'green'],
                    ['name' => 'Baby Care', 'icon' => 'fa-bath', 'color' => 'pink'],
                ]
            ],
            [
                'name' => 'Frozen Foods',
                'icon' => 'fa-snowflake',
                'color' => 'cyan',
                'description' => 'Frozen and chilled items',
                'subcategories' => [
                    ['name' => 'Frozen Meat', 'icon' => 'fa-drumstick-bite', 'color' => 'red'],
                    ['name' => 'Frozen Fish', 'icon' => 'fa-fish', 'color' => 'blue'],
                    ['name' => 'Ice Cream', 'icon' => 'fa-ice-cream', 'color' => 'pink'],
                    ['name' => 'Frozen Vegetables', 'icon' => 'fa-carrot', 'color' => 'green'],
                ]
            ],
            [
                'name' => 'Electronics & Gadgets',
                'icon' => 'fa-plug',
                'color' => 'gray',
                'description' => 'Electronic devices and accessories',
                'subcategories' => [
                    ['name' => 'Phone Accessories', 'icon' => 'fa-mobile-screen', 'color' => 'blue'],
                    ['name' => 'Batteries', 'icon' => 'fa-battery-three-quarters', 'color' => 'green'],
                    ['name' => 'Chargers & Cables', 'icon' => 'fa-plug', 'color' => 'gray'],
                    ['name' => 'Bulbs & Lighting', 'icon' => 'fa-lightbulb', 'color' => 'yellow'],
                ]
            ],
            [
                'name' => 'Stationery & Books',
                'icon' => 'fa-book',
                'color' => 'blue',
                'description' => 'Stationery items and reading materials',
                'subcategories' => [
                    ['name' => 'Notebooks & Paper', 'icon' => 'fa-book-open', 'color' => 'gray'],
                    ['name' => 'Pens & Pencils', 'icon' => 'fa-pen', 'color' => 'blue'],
                    ['name' => 'School Supplies', 'icon' => 'fa-graduation-cap', 'color' => 'blue'],
                ]
            ],
        ];

        // Seed for each business
        foreach ($businesses as $business) {
            $displayOrder = 1;
            
            foreach ($categoriesData as $categoryData) {
                // Create parent category
                $parent = Category::create([
                    'business_id' => $business->id,
                    'name' => $categoryData['name'],
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                    'description' => $categoryData['description'],
                    'is_active' => true,
                    'display_order' => $displayOrder++,
                ]);

                // Create subcategories
                if (isset($categoryData['subcategories'])) {
                    $subOrder = 1;
                    foreach ($categoryData['subcategories'] as $subData) {
                        Category::create([
                            'business_id' => $business->id,
                            'parent_id' => $parent->id,
                            'name' => $subData['name'],
                            'icon' => $subData['icon'],
                            'color' => $subData['color'],
                            'is_active' => true,
                            'display_order' => $subOrder++,
                        ]);
                    }
                }
            }

            $this->command->info("✓ Categories seeded for business: {$business->name}");
        }
    }
}