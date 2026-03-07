<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('restrict');
            
            $table->date('requested_check_in');
            $table->date('requested_check_out');
            $table->integer('requested_nights');
            $table->integer('number_of_adults');
            $table->integer('number_of_children')->default(0);
            
            $table->integer('priority')->default(0);
            $table->enum('status', ['active', 'notified', 'converted', 'cancelled', 'expired'])->default('active');
            
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('special_requests')->nullable();
            
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
            
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['requested_check_in', 'requested_check_out']);
            $table->index(['status', 'priority']);
            $table->index('room_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
