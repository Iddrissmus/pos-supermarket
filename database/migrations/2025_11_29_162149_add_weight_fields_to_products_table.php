<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add weight information to products table (product-level data)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('box_weight', 10, 3)->nullable()->after('quantity_per_box')->comment('Weight of one box in kg');
            $table->enum('selling_mode', ['unit', 'weight', 'box', 'both'])->default('unit')->after('box_weight')->comment('How product is sold: unit, weight, box, or both');
        });

        // Add weight-based pricing to branch_products table (branch-specific pricing)
        Schema::table('branch_products', function (Blueprint $table) {
            $table->decimal('price_per_kilo', 10, 2)->nullable()->after('price')->comment('Selling price per kilogram');
            $table->decimal('price_per_box', 10, 2)->nullable()->after('price_per_kilo')->comment('Selling price per box');
            $table->enum('weight_unit', ['kg', 'g', 'ton', 'lb', 'oz'])->nullable()->after('price_per_box')->comment('Unit for weight-based pricing');
            $table->decimal('price_per_unit_weight', 10, 2)->nullable()->after('weight_unit')->comment('Price per selected weight unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['box_weight', 'selling_mode']);
        });

        Schema::table('branch_products', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_kilo',
                'price_per_box',
                'weight_unit',
                'price_per_unit_weight'
            ]);
        });
    }
};
