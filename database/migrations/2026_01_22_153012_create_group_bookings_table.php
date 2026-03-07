<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('group_number')->unique();
            $table->string('group_name');
            $table->foreignId('primary_guest_id')->constrained('guests')->onDelete('restrict');
            $table->foreignId('contact_person_id')->nullable()->constrained('guests')->onDelete('set null');
            
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('total_rooms');
            $table->integer('total_guests');
            $table->integer('total_adults');
            $table->integer('total_children')->default(0);
            
            $table->decimal('group_discount_percentage', 5, 2)->default(0);
            $table->decimal('group_discount_amount', 10, 2)->default(0);
            $table->decimal('total_group_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            
            $table->enum('billing_type', ['consolidated', 'individual', 'split'])->default('consolidated');
            $table->text('billing_instructions')->nullable();
            
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending');
            
            $table->text('special_requests')->nullable();
            $table->text('group_notes')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_bookings');
    }
};
