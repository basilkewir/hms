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
        // Add 'waiting_for_check' to housekeeping_status enum
        DB::statement("ALTER TABLE rooms MODIFY COLUMN housekeeping_status ENUM('clean', 'dirty', 'inspected', 'maintenance_required', 'waiting_for_check') DEFAULT 'clean'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE rooms MODIFY COLUMN housekeeping_status ENUM('clean', 'dirty', 'inspected', 'maintenance_required') DEFAULT 'clean'");
    }
};
