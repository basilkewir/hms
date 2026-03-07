<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('group_booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cancellation_type'); // customer_cancellation, hotel_cancellation, no_show
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->decimal('cancellation_fee', 10, 2)->default(0);
            $table->string('refund_status'); // pending, processing, completed, failed
            $table->text('cancellation_reason');
            $table->json('cancellation_details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_cancellations');
    }
};
