<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_rules');
        Schema::create('hall_booking_group_availability_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100033')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->string('rule_type'); // min_guests, max_guests, min_days_advance, max_days_advance, blackout_dates, etc.
            $table->json('rule_value'); // rule-specific value
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_rules');
    }
};
