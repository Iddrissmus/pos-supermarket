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
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_main')->default(false)->after('business_id');
        });

        // Set the first branch of each business as main
        $businesses = \Illuminate\Support\Facades\DB::table('businesses')->select('id')->get();
        foreach ($businesses as $business) {
            $firstBranch = \Illuminate\Support\Facades\DB::table('branches')
                ->where('business_id', $business->id)
                ->orderBy('created_at')
                ->first();
            
            if ($firstBranch) {
                \Illuminate\Support\Facades\DB::table('branches')
                    ->where('id', $firstBranch->id)
                    ->update(['is_main' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_main');
        });
    }
};
