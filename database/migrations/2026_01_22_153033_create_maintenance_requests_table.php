<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'furniture', 'appliances', 'security', 'it', 'other'])->default('other');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'on_hold', 'resolved', 'closed', 'cancelled'])->default('open');
            
            $table->string('location')->nullable();
            $table->text('location_details')->nullable();
            
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            
            $table->text('resolution_notes')->nullable();
            $table->text('work_performed')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->boolean('requires_follow_up')->default(false);
            $table->text('follow_up_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'priority']);
            $table->index(['room_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
