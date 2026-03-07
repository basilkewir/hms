<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_package_availability_analytics');
        Schema::create('hall_booking_package_availability_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->date('analytics_date');
            $table->integer('total_slots');
            $table->integer('booked_slots');
            $table->integer('available_slots');
            $table->decimal('occupancy_rate', 5, 2);
            $table->decimal('average_price', 10, 2);
            $table->json('analytics_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_package_availability_analytics');
    }
};
