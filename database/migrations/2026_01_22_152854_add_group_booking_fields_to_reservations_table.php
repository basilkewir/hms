<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'group_booking_id')) {
                // Add column without constraint (constraint will be added after group_bookings table is created)
                $table->unsignedBigInteger('group_booking_id')->nullable()->after('guest_id');
            }
            if (!Schema::hasColumn('reservations', 'is_group_booking')) {
                $table->boolean('is_group_booking')->default(false)->after('group_booking_id');
            }
            if (!Schema::hasColumn('reservations', 'billing_type')) {
                $table->enum('billing_type', ['individual', 'group_consolidated', 'group_split'])->default('individual')->after('is_group_booking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['group_booking_id']);
            $table->dropColumn(['group_booking_id', 'is_group_booking', 'billing_type']);
        });
    }
};
