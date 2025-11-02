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
        // Add box quantity fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity_per_box')->nullable()->after('image')->comment('Number of units per box');
        });

        // Add box quantity fields to branch_products table
        Schema::table('branch_products', function (Blueprint $table) {
            $table->integer('quantity_of_boxes')->nullable()->after('stock_quantity')->comment('Number of boxes in stock');
            $table->integer('quantity_per_box')->nullable()->after('quantity_of_boxes')->comment('Number of units per box');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('quantity_per_box');
        });

        Schema::table('branch_products', function (Blueprint $table) {
            $table->dropColumn(['quantity_of_boxes', 'quantity_per_box']);
        });
    }
};
