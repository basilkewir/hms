<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Work Shifts
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Morning, Evening, Night
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_overnight')->default(false); // Crosses midnight
            $table->decimal('hours', 4, 2); // Total hours in shift
            $table->decimal('break_minutes', 5, 2)->default(0);
            $table->decimal('overtime_threshold', 4, 2)->nullable(); // Hours before overtime
            $table->decimal('overtime_multiplier', 3, 2)->default(1.5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Employee Shift Assignments
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_shift_id')->constrained()->onDelete('cascade');
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->json('days_of_week'); // [1,2,3,4,5] for Mon-Fri
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'effective_date']);
        });

        // Time Clock Entries
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_shift_id')->nullable()->constrained();
            $table->date('work_date');
            
            // Clock In/Out Times
            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('clock_out_time')->nullable();
            $table->timestamp('break_start_time')->nullable();
            $table->timestamp('break_end_time')->nullable();
            
            // Calculated Hours
            $table->decimal('regular_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('break_hours', 5, 2)->default(0);
            $table->decimal('total_hours', 5, 2)->default(0);
            
            // Status
            $table->enum('status', ['active', 'completed', 'incomplete', 'approved', 'rejected'])->default('active');
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_out')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_out_minutes')->default(0);
            
            // Location Tracking
            $table->string('clock_in_ip')->nullable();
            $table->string('clock_out_ip')->nullable();
            $table->decimal('clock_in_latitude', 10, 8)->nullable();
            $table->decimal('clock_in_longitude', 11, 8)->nullable();
            $table->decimal('clock_out_latitude', 10, 8)->nullable();
            $table->decimal('clock_out_longitude', 11, 8)->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'work_date']);
            $table->index(['work_date', 'status']);
            $table->unique(['user_id', 'work_date']); // One entry per employee per day
        });

        // Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('leave_type', [
                'vacation', 'sick', 'personal', 'emergency', 'bereavement', 
                'maternity', 'paternity', 'unpaid'
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_requested', 4, 1); // Can be half days
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            
            // Approval Workflow
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Leave Balance Impact
            $table->decimal('vacation_days_used', 4, 1)->default(0);
            $table->decimal('sick_days_used', 4, 1)->default(0);
            $table->decimal('personal_days_used', 4, 1)->default(0);
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Employee Leave Balances
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('vacation_days_allocated', 4, 1)->default(0);
            $table->decimal('vacation_days_used', 4, 1)->default(0);
            $table->decimal('vacation_days_remaining', 4, 1)->default(0);
            $table->decimal('sick_days_allocated', 4, 1)->default(0);
            $table->decimal('sick_days_used', 4, 1)->default(0);
            $table->decimal('sick_days_remaining', 4, 1)->default(0);
            $table->decimal('personal_days_allocated', 4, 1)->default(0);
            $table->decimal('personal_days_used', 4, 1)->default(0);
            $table->decimal('personal_days_remaining', 4, 1)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('work_shifts');
    }
};
