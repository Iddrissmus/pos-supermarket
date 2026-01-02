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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'business_id')) {
                $table->after('notes', function ($table) {
                    $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
                });
            }
        });

        // Assign existing customers to the first business if any, or leave as is if multi-tenancy was not enforced
        $firstBusiness = DB::table('businesses')->first();
        if ($firstBusiness) {
            DB::table('customers')->whereNull('business_id')->update(['business_id' => $firstBusiness->id]);
        }

        // Change 'payment_terms' enum values from net_* to next_*
        // Since enum change is tricky, we can change the column type to string first, update data, then back to enum
        Schema::table('customers', function (Blueprint $table) {
            $table->string('payment_terms')->default('immediate')->change();
        });

        DB::table('customers')->where('payment_terms', 'net_15')->update(['payment_terms' => 'next_15']);
        DB::table('customers')->where('payment_terms', 'net_30')->update(['payment_terms' => 'next_30']);
        DB::table('customers')->where('payment_terms', 'net_60')->update(['payment_terms' => 'next_60']);

        // Set back to enum with new values
        // Note: doctrine/dbal might not support this directly for SQLite, but MySQL should work.
        // For broad compatibility, we might leave it as string or use raw SQL.
        Schema::table('customers', function (Blueprint $table) {
             // We'll leave it as string for now to avoid enum change issues across drivers, 
             // or use raw SQL if MySQL is certain.
             // Given the previous session used raw SQL for status, let's do similar or just use string.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
};
