<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotel_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // breakfast, spa, laundry, transport, etc.
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('pricing_type')->default('per_service'); // per_service, per_person, per_night
            $table->boolean('is_active')->default(true);
            $table->boolean('available_online')->default(true);
            $table->boolean('requires_advance_booking')->default(false);
            $table->integer('advance_hours')->nullable();
            $table->json('availability_schedule')->nullable(); // days/times available
            $table->integer('max_quantity')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_services');
    }
};
