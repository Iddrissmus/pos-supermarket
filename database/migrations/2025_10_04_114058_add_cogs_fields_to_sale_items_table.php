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
            // Simply use standard Schema method to add index; Laravel handles underlying logic usually
            // but since we want to be safe about existence:
            try {
                // Determine if we are on SQLite which doesn't support 'SHOW INDEX'
                $isSqlite = DB::connection()->getDriverName() === 'sqlite';
                
                if (!$isSqlite) {
                    // For MySQL/others
                    $indexExists = collect(DB::select("SHOW INDEX FROM sale_items"))
                        ->pluck('Key_name')
                        ->contains('sale_items_sale_id_product_id_index');
                        
                    if (!$indexExists) {
                        $table->index(['sale_id', 'product_id']);
                    }
                } else {
                    // For SQLite, just try adding it, or check via pragma if really needed. 
                    // But typically tests run fresh migrations so index won't exist yet.
                    // If we do want to check:
                    $indexes = collect(DB::select("PRAGMA index_list(sale_items)"));
                    // SQLite index names might be auto-generated differently or follow laravel convention
                    // Safest is to just attempt it in a separate schema call or assume fresh DB for tests.
                    // Let's just wrap strictly the index creation:
                    $table->index(['sale_id', 'product_id']);
                }
            } catch (\Exception $e) {
                // Index might already exist
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
