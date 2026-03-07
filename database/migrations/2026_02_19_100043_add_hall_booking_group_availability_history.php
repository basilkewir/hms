<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_history');
        Schema::create('hall_booking_group_availability_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100043')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->date('history_date');
            $table->integer('total_slots');
            $table->integer('booked_slots');
            $table->integer('available_slots');
            $table->decimal('price_multiplier', 5, 2);
            $table->json('changes')->nullable(); // what changed
            $table->string('changed_by')->nullable(); // user who made the change
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_history');
    }
};
