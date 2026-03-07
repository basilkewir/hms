<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('group_booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_type'); // deposit, full_payment, partial_payment
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // cash, card, bank_transfer, online_payment
            $table->string('transaction_id')->nullable();
            $table->string('status'); // pending, completed, failed, refunded
            $table->json('payment_details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_payments');
    }
};
