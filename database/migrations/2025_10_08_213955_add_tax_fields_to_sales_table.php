<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Livewire\after;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('total');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');   // Tax percentage (e.g., 12.50 for 12.5%)
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate'); 
            $table->json('tax_components')->nullable()->after('tax_amount'); // Store multiple tax types
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount', 'tax_components']);
        });
    }
};
