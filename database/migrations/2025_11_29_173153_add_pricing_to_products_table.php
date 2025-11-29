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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('selling_mode')->comment('Default selling price per unit');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price')->comment('Default cost price per unit');
            $table->decimal('price_per_kilo', 10, 2)->nullable()->after('cost_price')->comment('Default price per kilogram');
            $table->decimal('price_per_box', 10, 2)->nullable()->after('price_per_kilo')->comment('Default price per box');
            $table->string('weight_unit', 10)->nullable()->after('price_per_box')->comment('Alternative weight unit');
            $table->decimal('price_per_unit_weight', 10, 2)->nullable()->after('weight_unit')->comment('Price per alternative weight unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'cost_price', 'price_per_kilo', 'price_per_box', 'weight_unit', 'price_per_unit_weight']);
        });
    }
};
