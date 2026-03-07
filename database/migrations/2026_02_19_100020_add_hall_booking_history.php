<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('group_booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // booking_created, booking_updated, booking_cancelled, payment_received, etc.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('performed_by'); // system, staff, customer
            $table->string('performed_by_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_history');
    }
};
