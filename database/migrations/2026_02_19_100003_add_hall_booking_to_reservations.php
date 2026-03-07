<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('hall_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('hall_start_time')->nullable();
            $table->dateTime('hall_end_time')->nullable();
            $table->json('hall_booking_details')->nullable(); // additional hall booking details
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['hall_id']);
            $table->dropColumn(['hall_id', 'hall_start_time', 'hall_end_time', 'hall_booking_details']);
        });
    }
};
