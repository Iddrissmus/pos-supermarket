<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('sale_items', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('sale_items', 'total_cost')) {
                $table->decimal('total_cost', 12, 2)->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('sale_items', 'gross_margin')) {
                $table->decimal('gross_margin', 12, 2)->nullable()->after('total_cost');
            }
            if (!Schema::hasColumn('sale_items', 'margin_percent')) {
                $table->decimal('margin_percent', 5, 2)->nullable()->after('gross_margin');
            }
        });
        
        // Add index separately to avoid conflicts
        Schema::table('sale_items', function (Blueprint $table) {
            // Check if index doesn't exist before adding
            $indexExists = collect(DB::select("SHOW INDEX FROM sale_items"))
                ->pluck('Key_name')
                ->contains('sale_items_sale_id_product_id_index');
                
            if (!$indexExists) {
                $table->index(['sale_id', 'product_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id', 'product_id']);
            $table->dropColumn(['unit_cost', 'total_cost', 'gross_margin', 'margin_percent']);
        });
    }
};
