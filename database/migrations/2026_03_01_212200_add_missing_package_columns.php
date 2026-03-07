<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('is_active');
            }

            if (!Schema::hasColumn('packages', 'max_bookings')) {
                $table->integer('max_bookings')->nullable()->after('is_available');
            }

            if (!Schema::hasColumn('packages', 'min_guests')) {
                $table->integer('min_guests')->nullable()->after('max_bookings');
            }

            if (!Schema::hasColumn('packages', 'max_guests')) {
                $table->integer('max_guests')->nullable()->after('min_guests');
            }

            if (!Schema::hasColumn('packages', 'duration_hours')) {
                $table->integer('duration_hours')->nullable()->after('max_guests');
            }
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('packages', 'is_available')) {
                $columns[] = 'is_available';
            }
            if (Schema::hasColumn('packages', 'max_bookings')) {
                $columns[] = 'max_bookings';
            }
            if (Schema::hasColumn('packages', 'min_guests')) {
                $columns[] = 'min_guests';
            }
            if (Schema::hasColumn('packages', 'max_guests')) {
                $columns[] = 'max_guests';
            }
            if (Schema::hasColumn('packages', 'duration_hours')) {
                $columns[] = 'duration_hours';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
