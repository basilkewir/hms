<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_group_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->integer('min_guests');
            $table->integer('max_guests');
            $table->decimal('base_price_per_guest', 10, 2);
            $table->decimal('group_discount_percentage', 5, 2)->default(0);
            $table->decimal('final_price_per_guest', 10, 2);
            $table->json('pricing_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_pricing');
    }
};
