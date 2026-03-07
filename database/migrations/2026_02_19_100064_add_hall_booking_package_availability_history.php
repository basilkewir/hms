<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_package_availability_history');
        Schema::create('hall_booking_package_availability_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('history_type'); // price_change, availability_change, package_update, etc.
            $table->json('history_data'); // details about the change
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('history_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_package_availability_history');
    }
};
