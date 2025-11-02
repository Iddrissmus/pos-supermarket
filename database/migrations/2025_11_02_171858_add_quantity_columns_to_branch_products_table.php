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
        Schema::table('branch_products', function (Blueprint $table) {
            $table->integer('quantity_of_boxes')->default(0)->after('stock_quantity');
            $table->integer('quantity_per_box')->default(1)->after('quantity_of_boxes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_products', function (Blueprint $table) {
            $table->dropColumn(['quantity_of_boxes', 'quantity_per_box']);
        });
    }
};
