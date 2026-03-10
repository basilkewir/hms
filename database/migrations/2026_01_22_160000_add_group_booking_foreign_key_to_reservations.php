<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key constraint to group_booking_id after group_bookings table exists
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'group_booking_id') && 
                !Schema::hasColumn('reservations', 'id') === false) { // Table exists
                try {
                    $table->foreign('group_booking_id')->references('id')->on('group_bookings')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist, continue
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign(['group_booking_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
        });
    }
};
