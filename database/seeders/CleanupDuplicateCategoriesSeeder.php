<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateCategoriesSeeder extends Seeder
{
    /**
     * Clean up duplicate parent categories (those with 0 subcategories)
     */
    public function run(): void
    {
        $this->command->info('Cleaning up duplicate categories...');

        // Find duplicate parent categories (same name, no subcategories)
        $duplicates = Category::whereNull('parent_id')
            ->get()
            ->groupBy('name')
            ->filter(function ($group) {
                return $group->count() > 1;
            });

        $deletedCount = 0;

        foreach ($duplicates as $name => $categories) {
            $this->command->warn("Found duplicates for: {$name}");
            
            // Keep the one with subcategories, delete empty ones
            $withSubcategories = $categories->filter(function ($cat) {
                return $cat->subcategories()->count() > 0;
            });

            $emptyOnes = $categories->filter(function ($cat) {
                return $cat->subcategories()->count() === 0;
            });

            if ($withSubcategories->count() > 0 && $emptyOnes->count() > 0) {
                $keepCategory = $withSubcategories->first();
                $this->command->info("  ✓ Keeping: ID {$keepCategory->id} with {$keepCategory->subcategories()->count()} subcategories");
                
                foreach ($emptyOnes as $emptyCategory) {
                    // Check if it has any products
                    if ($emptyCategory->products()->count() > 0) {
                        // Move products to the kept category
                        $productCount = $emptyCategory->products()->count();
                        $emptyCategory->products()->update(['category_id' => $keepCategory->id]);
                        $this->command->info("  → Moved {$productCount} products to kept category");
                    }
                    
                    $emptyCategory->delete();
                    $deletedCount++;
                    $this->command->info("  ✗ Deleted duplicate: ID {$emptyCategory->id}");
                }
            }
        }

        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("Cleanup complete! Deleted {$deletedCount} duplicate categories.");
        $this->command->info(str_repeat('=', 80));
    }
}
