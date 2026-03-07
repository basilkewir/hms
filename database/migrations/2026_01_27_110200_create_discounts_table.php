<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();

            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 10, 2); // percentage or fixed amount

            // Scope / applicability
            $table->enum('applicable_to', ['room', 'service', 'folio', 'pos', 'all'])->default('room');
            $table->boolean('auto_apply')->default(false);
            $table->boolean('is_global')->default(false);

            // Conditions
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('per_guest_limit')->nullable();
            $table->integer('used_count')->default(0);

            $table->boolean('requires_code')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'applicable_to', 'is_active']);
            $table->index(['code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};

