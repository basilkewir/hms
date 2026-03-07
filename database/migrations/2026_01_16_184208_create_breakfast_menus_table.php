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
        Schema::create('breakfast_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // continental, american, buffet, a_la_carte
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('items')->nullable(); // menu items list
            $table->string('serving_time_start')->nullable(); // e.g., "07:00"
            $table->string('serving_time_end')->nullable(); // e.g., "10:30"
            $table->boolean('is_active')->default(true);
            $table->boolean('available_online')->default(true);
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakfast_menus');
    }
};
