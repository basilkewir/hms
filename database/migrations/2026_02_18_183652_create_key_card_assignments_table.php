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
        Schema::create('key_card_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('returned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('returned_at')->nullable();
            $table->string('action')->default('assigned'); // assigned, returned, lost, damaged
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['key_card_id', 'assigned_at']);
            $table->index(['guest_id', 'assigned_at']);
            $table->index(['room_id', 'assigned_at']);
            $table->index(['assigned_by', 'assigned_at']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_card_assignments');
    }
};
