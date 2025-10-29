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
            $table->text('reason')->nullable()->after('status');
            $table->unsignedBigInteger('requested_by')->nullable()->after('approval_note');
            $table->timestamp('requested_at')->nullable()->after('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('requested_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('cancelled_at')->nullable()->after('approved_at');
            
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'reason',
                'requested_by',
                'requested_at',
                'approved_by',
                'approved_at',
                'cancelled_at'
            ]);
        });
    }
};
