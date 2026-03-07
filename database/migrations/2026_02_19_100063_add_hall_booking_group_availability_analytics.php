<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_analytics');
        Schema::create('hall_booking_group_availability_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100063')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->date('analytics_date');
            $table->integer('total_views');
            $table->integer('total_bookings');
            $table->decimal('conversion_rate', 5, 2);
            $table->decimal('average_booking_value', 10, 2);
            $table->json('analytics_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_analytics');
    }
};
