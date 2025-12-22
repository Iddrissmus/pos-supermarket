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
        Schema::create('business_signup_requests', function (Blueprint $table) {
            $table->id();

            // Business info
            $table->string('business_name');
            $table->string('logo')->nullable();

            // Owner / future Business Admin info
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone', 30);

            // First / main branch info
            $table->string('branch_name');
            $table->string('address', 500);
            $table->string('region', 100);
            $table->string('branch_contact', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_signup_requests');
    }
};





