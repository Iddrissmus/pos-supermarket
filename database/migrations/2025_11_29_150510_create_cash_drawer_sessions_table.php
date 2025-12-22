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
        Schema::create('cash_drawer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cashier
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('opening_amount', 10, 2); // Starting cash in drawer
            $table->decimal('expected_amount', 10, 2)->nullable(); // Expected at end of day
            $table->decimal('actual_amount', 10, 2)->nullable(); // Actual count at end
            $table->decimal('difference', 10, 2)->nullable(); // Over/short
            $table->date('session_date');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();
            
            // Ensure one active session per cashier per day
            $table->unique(['user_id', 'session_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_drawer_sessions');
    }
};
