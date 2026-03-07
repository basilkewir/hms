<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('task_type', ['checkout', 'stayover', 'deep_clean', 'inspection', 'maintenance'])->default('checkout');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inspected_at')->nullable();
            $table->enum('inspection_status', ['passed', 'failed', 'pending'])->nullable();
            $table->text('inspection_notes')->nullable();
            
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            
            $table->timestamps();
            
            $table->index(['scheduled_date', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
