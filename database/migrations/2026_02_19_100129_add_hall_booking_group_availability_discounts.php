<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_discounts');
        Schema::create('hall_booking_group_availability_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100129')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->string('discount_type'); // percentage, fixed_amount, etc.
            $table->decimal('discount_value', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_discounts');
    }
};
