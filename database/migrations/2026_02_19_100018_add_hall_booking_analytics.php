<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->date('analytics_date');
            $table->integer('total_bookings');
            $table->integer('total_guests');
            $table->decimal('total_revenue', 10, 2);
            $table->decimal('average_booking_value', 10, 2);
            $table->decimal('occupancy_rate', 5, 2);
            $table->json('booking_sources')->nullable(); // breakdown of booking sources
            $table->json('package_usage')->nullable(); // package usage statistics
            $table->json('cancellation_stats')->nullable(); // cancellation statistics
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_analytics');
    }
};
