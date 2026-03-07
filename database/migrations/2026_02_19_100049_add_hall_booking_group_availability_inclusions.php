<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_group_availability_inclusions');
        Schema::create('hall_booking_group_availability_inclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('group_booking_id');
            $table->foreign('group_booking_id', 'fk_gbid_100049')->references('id')->on('group_bookings')->onDelete('cascade');
            $table->string('inclusion_type'); // catering, decoration, audio_visual, etc.
            $table->json('inclusion_details'); // details about the inclusion
            $table->decimal('inclusion_price', 10, 2)->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_availability_inclusions');
    }
};
