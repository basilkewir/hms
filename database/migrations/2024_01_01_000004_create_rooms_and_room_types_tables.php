<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Room Types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard, Deluxe, Suite, etc.
            $table->string('code')->unique(); // STD, DLX, STE
            $table->text('description')->nullable();
            $table->integer('max_occupancy');
            $table->integer('max_adults');
            $table->integer('max_children');
            $table->decimal('base_price', 10, 2); // Base nightly rate
            $table->decimal('extra_adult_charge', 8, 2)->default(0);
            $table->decimal('extra_child_charge', 8, 2)->default(0);
            $table->json('amenities')->nullable(); // WiFi, AC, TV, etc.
            $table->json('iptv_channels')->nullable(); // Available IPTV channels
            $table->string('iptv_package')->nullable(); // Basic, Premium, VIP
            $table->decimal('room_size_sqft', 8, 2)->nullable();
            $table->string('bed_type')->nullable(); // Single, Double, Queen, King
            $table->integer('bed_count')->default(1);
            $table->boolean('has_balcony')->default(false);
            $table->boolean('has_kitchen')->default(false);
            $table->boolean('has_living_room')->default(false);
            $table->string('view_type')->nullable(); // City, Ocean, Garden
            $table->json('images')->nullable(); // Room images
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->foreignId('room_type_id')->constrained()->onDelete('restrict');
            $table->integer('floor');
            $table->string('building')->nullable();
            $table->string('wing')->nullable();
            
            // Room Status
            $table->enum('status', [
                'available', 'occupied', 'maintenance', 'cleaning', 
                'out_of_order', 'reserved'
            ])->default('available');
            
            // IPTV Configuration
            $table->string('iptv_device_id')->nullable(); // Android device ID
            $table->string('iptv_mac_address')->nullable();
            $table->string('iptv_ip_address')->nullable();
            $table->boolean('iptv_active')->default(true);
            $table->json('iptv_settings')->nullable(); // Custom IPTV settings
            $table->timestamp('iptv_last_seen')->nullable();
            
            // Room Features
            $table->boolean('is_smoking')->default(false);
            $table->boolean('is_accessible')->default(false); // ADA compliant
            $table->boolean('has_connecting_room')->default(false);
            $table->string('connecting_room_number')->nullable();
            
            // Maintenance
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('maintenance_notes')->nullable();
            
            // Housekeeping
            $table->enum('housekeeping_status', [
                'clean', 'dirty', 'inspected', 'maintenance_required'
            ])->default('clean');
            $table->timestamp('last_cleaned_at')->nullable();
            $table->foreignId('last_cleaned_by')->nullable()->constrained('users');
            
            // Pricing Override
            $table->decimal('custom_price', 10, 2)->nullable(); // Override room type price
            $table->date('custom_price_start')->nullable();
            $table->date('custom_price_end')->nullable();
            
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index('floor');
            $table->index('room_type_id');
            $table->index('iptv_device_id');
        });

        // Room Amenities (many-to-many)
        Schema::create('room_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('amenity_name');
            $table->string('amenity_type'); // furniture, electronics, bathroom, etc.
            $table->text('description')->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'broken'])->default('good');
            $table->date('last_checked')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_amenities');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
