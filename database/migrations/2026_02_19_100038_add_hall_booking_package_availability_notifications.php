<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('hall_booking_package_availability_notifications');
        Schema::create('hall_booking_package_availability_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id', 'fk_pid_100038')->references('id')->on('packages')->onDelete('cascade');
            $table->string('notification_type'); // availability_change, price_change, slot_availability, etc.
            $table->json('notification_data'); // notification-specific data
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_package_availability_notifications');
    }
};
