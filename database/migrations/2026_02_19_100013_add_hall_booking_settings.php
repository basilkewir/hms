<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('hall_booking_settings')->nullable(); // hall booking configuration
            $table->json('package_booking_settings')->nullable(); // package booking configuration
            $table->json('group_booking_settings')->nullable(); // group booking configuration
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['hall_booking_settings', 'package_booking_settings', 'group_booking_settings']);
        });
    }
};
