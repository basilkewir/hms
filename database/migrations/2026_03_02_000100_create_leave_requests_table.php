<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DEPRECATED: This migration is a duplicate.
 * The 'leave_requests' table is already created by
 * 2024_01_01_000006_create_time_tracking_tables.php
 * 
 * This migration has been converted to a no-op to prevent
 * "table already exists" errors while maintaining migration history.
 */
return new class extends Migration {
    public function up(): void
    {
        // Table already exists, created by 2024_01_01_000006
        // Skip creation to avoid "table already exists" error
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('leave_type');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('days_requested', 5, 1)->nullable();
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->decimal('vacation_days_used', 5, 1)->nullable();
                $table->decimal('sick_days_used', 5, 1)->nullable();
                $table->decimal('personal_days_used', 5, 1)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Do not drop the table as it's managed by 2024_01_01_000006
    }
};
