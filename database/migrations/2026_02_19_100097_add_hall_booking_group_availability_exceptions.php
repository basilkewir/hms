<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_exceptions');
        Schema::create('hall_booking_group_availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100097')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->string('exception_type'); // holiday, maintenance, special_event, etc.
            $table->date('exception_date');
            $table->json('exception_details'); // details about the exception
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_exceptions');
    }
};
