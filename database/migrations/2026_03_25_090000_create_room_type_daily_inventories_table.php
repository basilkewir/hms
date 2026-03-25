<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_type_daily_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('inventory_date');
            $table->unsignedInteger('total_inventory')->default(0);
            $table->unsignedInteger('reserved_count')->default(0);
            $table->unsignedInteger('hold_count')->default(0);
            $table->unsignedInteger('overbooking_allowance')->default(0);
            $table->unsignedInteger('available_count')->default(0);
            $table->timestamps();

            $table->unique(['room_type_id', 'inventory_date']);
            $table->index(['inventory_date', 'room_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_daily_inventories');
    }
};
