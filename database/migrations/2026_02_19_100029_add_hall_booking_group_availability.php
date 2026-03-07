<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability');
        Schema::create('hall_booking_group_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100029')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->date('availability_date');
            $table->integer('available_slots')->default(0);
            $table->integer('booked_slots')->default(0);
            $table->decimal('price_multiplier', 5, 2)->default(1.0);
            $table->json('special_conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability');
    }
};
