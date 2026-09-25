<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reservations', 'guest_display_expires_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                // Guest Display (lite) expiry: when set, the guest name is
                // removed from the in-room TV after this moment.
                $table->timestamp('guest_display_expires_at')->nullable()->after('police_reported_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reservations', 'guest_display_expires_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('guest_display_expires_at');
            });
        }
    }
};
