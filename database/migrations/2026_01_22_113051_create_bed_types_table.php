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
        Schema::create('bed_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Single", "Double", "Queen", "King", "Twin"
            $table->string('code')->unique()->nullable(); // e.g., "SGL", "DBL", "QN", "KG", "TWN"
            $table->text('description')->nullable();
            $table->decimal('width_inches', 5, 2)->nullable(); // Bed width in inches
            $table->decimal('length_inches', 5, 2)->nullable(); // Bed length in inches
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_types');
    }
};
