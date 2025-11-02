<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "Auto-Categorizing Uncategorized Products\n";
echo str_repeat('=', 80) . "\n\n";

$uncategorized = Product::whereNull('category_id')->get();

if ($uncategorized->count() === 0) {
    echo "✅ No uncategorized products found!\n";
    exit(0);
}

echo "Found {$uncategorized->count()} uncategorized products\n\n";

// Smart categorization mapping
$categoryMapping = [
    'rice' => 'Rice & Grains',
    'oil' => 'Oils & Fats',
    'flour' => 'Flour & Baking',
    'sardine' => 'Canned & Packaged Foods',
    'sugar' => 'Sugar & Sweeteners',
    'bulb' => 'Bulbs & Lighting',
    'cable' => 'Chargers & Cables',
    'batteries' => 'Batteries',
    'battery' => 'Batteries',
    'iphone' => 'Phone Accessories',
    'phone' => 'Phone Accessories',
];

$categorized = 0;
$failed = 0;

foreach ($uncategorized as $product) {
    $productNameLower = strtolower($product->name);
    $suggestedCategory = null;
    
    // Try to match based on product name keywords
    foreach ($categoryMapping as $keyword => $categoryName) {
        if (strpos($productNameLower, $keyword) !== false) {
            $suggestedCategory = $categoryName;
            break;
        }
    }
    
    // Default to General Merchandise if no match
    if (!$suggestedCategory) {
        $suggestedCategory = 'General Merchandise';
    }
    
    // Find the category
    $category = Category::where('business_id', $product->business_id)
        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($suggestedCategory)])
        ->first();
    
    if ($category) {
        $product->category_id = $category->id;
        $product->save();
        
        $parentInfo = $category->parent ? " (under {$category->parent->name})" : "";
        echo "✅ {$product->name} → {$category->name}{$parentInfo}\n";
        $categorized++;
    } else {
        echo "❌ {$product->name} → Category '{$suggestedCategory}' not found\n";
        $failed++;
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Summary:\n";
echo "  Categorized: $categorized\n";
echo "  Failed: $failed\n";
echo "  Remaining uncategorized: " . Product::whereNull('category_id')->count() . "\n";
