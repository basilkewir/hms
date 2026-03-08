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
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'number_of_adults')) {
                $table->integer('number_of_adults')->default(1)->after('nights');
            }
            if (!Schema::hasColumn('reservations', 'number_of_children')) {
                $table->integer('number_of_children')->default(0)->after('number_of_adults');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('reservations', 'number_of_adults'))   { $cols[] = 'number_of_adults'; }
            if (Schema::hasColumn('reservations', 'number_of_children')) { $cols[] = 'number_of_children'; }
            if (!empty($cols)) { $table->dropColumn($cols); }
        });
    }
};
