<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('guest_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('room_type_id')->constrained()->onDelete('restrict');
            
            // Booking Details
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->integer('adults');
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            
            // Status
            $table->enum('status', [
                'pending', 'confirmed', 'checked_in', 'checked_out', 
                'cancelled', 'no_show', 'modified'
            ])->default('pending');
            
            // Pricing
            $table->decimal('room_rate', 10, 2); // Per night
            $table->decimal('total_room_charges', 12, 2);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('service_charges', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            
            // Check-in/out Details
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->foreignId('checked_out_by')->nullable()->constrained('users');
            
            // Booking Source
            $table->enum('booking_source', [
                'walk_in', 'phone', 'email', 'website', 'booking_com', 
                'expedia', 'agoda', 'travel_agent', 'corporate'
            ])->default('walk_in');
            $table->string('booking_reference')->nullable(); // External booking ref
            
            // Guest Requests
            $table->text('special_requests')->nullable();
            $table->json('room_preferences')->nullable();
            $table->boolean('early_check_in_requested')->default(false);
            $table->boolean('late_check_out_requested')->default(false);
            $table->time('preferred_check_in_time')->nullable();
            $table->time('preferred_check_out_time')->nullable();
            
            // IPTV Preferences
            $table->json('iptv_preferences')->nullable(); // Preferred channels, parental controls
            $table->boolean('iptv_adult_content')->default(false);
            $table->string('iptv_language_preference')->nullable();
            
            // Additional Services
            $table->boolean('airport_pickup')->default(false);
            $table->boolean('airport_drop')->default(false);
            $table->boolean('breakfast_included')->default(false);
            $table->boolean('wifi_included')->default(true);
            $table->boolean('parking_required')->default(false);
            
            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->text('cancellation_reason')->nullable();
            $table->decimal('cancellation_charges', 10, 2)->default(0);
            
            // System Fields
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index(['check_in_date', 'check_out_date']);
            $table->index(['status', 'check_in_date']);
            $table->index('guest_id');
            $table->index('room_id');
            $table->index('booking_source');
        });

        // Reservation Companions
        Schema::create('reservation_companions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('relationship'); // spouse, child, friend, etc.
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('nationality')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservation_companions');
        Schema::dropIfExists('reservations');
    }
};
