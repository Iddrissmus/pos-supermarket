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
            // Total inventory in the warehouse/central stock
            $table->integer('total_boxes')->default(0)->after('quantity_per_box')
                ->comment('Total boxes available in warehouse before branch assignment');
            $table->integer('total_units')->default(0)->after('total_boxes')
                ->comment('Total units (total_boxes * quantity_per_box)');
            $table->integer('assigned_units')->default(0)->after('total_units')
                ->comment('Units already assigned to branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['total_boxes', 'total_units', 'assigned_units']);
        });
    }
};
