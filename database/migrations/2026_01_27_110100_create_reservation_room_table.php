<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('restrict');

            // Per‑room details
            $table->boolean('is_primary')->default(false);
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->decimal('room_rate', 10, 2)->nullable();
            $table->decimal('total_room_charges', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['reservation_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_room');
    }
};

