<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->string('discount_code')->unique();
            $table->string('discount_type'); // percentage, fixed_amount
            $table->decimal('discount_value', 10, 2);
            $table->integer('min_guests')->nullable();
            $table->integer('max_usage')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->json('usage_rules')->nullable(); // additional usage rules
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_discounts');
    }
};
