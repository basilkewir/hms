<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('group_booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('notification_type'); // booking_request, booking_confirmation, booking_cancellation, payment_reminder
            $table->string('recipient_type'); // customer, staff, manager
            $table->string('recipient_email');
            $table->string('recipient_phone');
            $table->text('message');
            $table->json('template_data')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_notifications');
    }
};
