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
            $table->foreign('group_booking_id', 'fk_gbid_100069')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->string('discount_type'); // early_booking, group_booking, seasonal, etc.
            $table->decimal('discount_value', 5, 2);
            $table->string('discount_unit'); // percentage, fixed_amount
            $table->json('discount_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_discounts');
    }
};
