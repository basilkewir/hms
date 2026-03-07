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
        // Modify the enum to include 'cleaning'
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN task_type ENUM('checkout', 'stayover', 'deep_clean', 'inspection', 'maintenance', 'cleaning') DEFAULT 'checkout'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN task_type ENUM('checkout', 'stayover', 'deep_clean', 'inspection', 'maintenance') DEFAULT 'checkout'");
    }
};
