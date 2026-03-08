<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NOTE: This migration is intentionally a no-op.
     * The actual change was applied by 2026_03_06_200829_make_reservation_id_nullable_in_payments_table.php
     */
    public function up(): void
    {
        // no-op — handled by previous migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op
    }
};
