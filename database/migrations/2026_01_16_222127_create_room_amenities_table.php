<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('room_amenities')) {
            Schema::create('room_amenities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('room_type_amenity')) {
            Schema::create('room_type_amenity', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
                $table->foreignId('room_amenity_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_amenity');
        Schema::dropIfExists('room_amenities');
    }
};
