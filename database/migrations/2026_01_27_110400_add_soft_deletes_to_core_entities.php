<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guests
        if (!Schema::hasColumn('guests', 'deleted_at')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Reservations
        if (!Schema::hasColumn('reservations', 'deleted_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Guest folios
        if (!Schema::hasColumn('guest_folios', 'deleted_at')) {
            Schema::table('guest_folios', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Hotel services
        if (!Schema::hasColumn('hotel_services', 'deleted_at')) {
            Schema::table('hotel_services', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_services', 'deleted_at')) {
            Schema::table('hotel_services', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('guest_folios', 'deleted_at')) {
            Schema::table('guest_folios', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('reservations', 'deleted_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('guests', 'deleted_at')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

