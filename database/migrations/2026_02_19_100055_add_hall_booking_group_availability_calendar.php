<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_calendar');
        Schema::create('hall_booking_group_availability_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100055')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->date('calendar_date');
            $table->integer('available_slots');
            $table->decimal('price_multiplier', 5, 2)->default(1.0);
            $table->json('calendar_data')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_calendar');
    }
};
