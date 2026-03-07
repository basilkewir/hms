<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_settings');
        Schema::create('hall_booking_group_availability_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100037')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->boolean('enable_availability_calendar')->default(true);
            $table->boolean('enable_availability_rules')->default(true);
            $table->boolean('enable_availability_exceptions')->default(true);
            $table->boolean('enable_availability_notifications')->default(true);
            $table->json('availability_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_settings');
    }
};
