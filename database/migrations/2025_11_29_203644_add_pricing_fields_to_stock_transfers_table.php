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
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Add quantity breakdown fields (if not already present)
            if (!Schema::hasColumn('stock_transfers', 'quantity_of_boxes')) {
                $table->integer('quantity_of_boxes')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('stock_transfers', 'quantity_per_box')) {
                $table->integer('quantity_per_box')->nullable()->after('quantity_of_boxes');
            }
            
            // Add pricing fields
            $table->decimal('price', 10, 2)->nullable()->after('quantity_per_box');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->decimal('price_per_kilo', 10, 2)->nullable()->after('cost_price');
            $table->decimal('price_per_box', 10, 2)->nullable()->after('price_per_kilo');
            $table->string('weight_unit')->nullable()->after('price_per_box');
            $table->decimal('price_per_unit_weight', 10, 2)->nullable()->after('weight_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_of_boxes',
                'quantity_per_box',
                'price',
                'cost_price',
                'price_per_kilo',
                'price_per_box',
                'weight_unit',
                'price_per_unit_weight',
            ]);
        });
    }
};
