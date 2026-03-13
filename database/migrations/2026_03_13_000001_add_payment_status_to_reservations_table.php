<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded'])
                      ->default('pending')
                      ->after('status');
            }
            if (!Schema::hasColumn('reservations', 'source')) {
                $table->string('source')->nullable()->after('booking_source');
            }
            if (!Schema::hasColumn('reservations', 'ota_confirmation_number')) {
                $table->string('ota_confirmation_number')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('reservations', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('reservations', 'ota_confirmation_number')) {
                $table->dropColumn('ota_confirmation_number');
            }
        });
    }
};
