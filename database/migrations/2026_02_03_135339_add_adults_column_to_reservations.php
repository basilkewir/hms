<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('reservations', 'adults')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->integer('adults')->default(1)->after('nights');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('reservations', 'adults')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('adults');
            });
        }
    }
};
