<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('guest_id')->unique(); // Hotel guest ID
            
            // Personal Information
            $table->string('title')->nullable(); // Mr, Mrs, Dr, etc.
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('nationality');
            $table->string('occupation')->nullable();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postal_code')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            $table->text('emergency_contact_address')->nullable();
            
            // Identification Documents (Police Requirements)
            $table->enum('id_type', ['passport', 'national_id', 'drivers_license', 'other']);
            $table->string('id_number');
            $table->string('id_issuing_authority');
            $table->date('id_issue_date');
            $table->date('id_expiry_date');
            $table->string('id_document_path')->nullable(); // Scanned copy
            
            // Passport Details (if applicable)
            $table->string('passport_number')->nullable();
            $table->string('passport_issuing_country')->nullable();
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('passport_document_path')->nullable();
            
            // Visa Details (for foreign nationals)
            $table->string('visa_number')->nullable();
            $table->string('visa_type')->nullable();
            $table->date('visa_issue_date')->nullable();
            $table->date('visa_expiry_date')->nullable();
            $table->string('visa_document_path')->nullable();
            
            // Police Verification
            $table->enum('police_verification_status', ['pending', 'verified', 'flagged', 'rejected'])->default('pending');
            $table->text('police_verification_notes')->nullable();
            $table->timestamp('police_verification_date')->nullable();
            $table->string('police_verification_officer')->nullable();
            $table->string('police_case_number')->nullable(); // If flagged
            
            // Travel Information
            $table->string('arrival_from')->nullable(); // Previous location
            $table->string('departure_to')->nullable(); // Next destination
            $table->string('purpose_of_visit');
            $table->integer('expected_duration_days')->nullable();
            
            // Companion Information
            $table->integer('total_companions')->default(0);
            $table->json('companion_details')->nullable(); // Array of companion info
            
            // Vehicle Information
            $table->string('vehicle_registration')->nullable();
            $table->string('vehicle_make_model')->nullable();
            $table->string('vehicle_color')->nullable();
            
            // Guest Preferences
            $table->json('preferences')->nullable(); // Room type, amenities, etc.
            $table->text('special_requests')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('dietary_restrictions')->nullable();
            
            // System Fields
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['id_type', 'id_number']);
            $table->index('passport_number');
            $table->index('police_verification_status');
            $table->index(['first_name', 'last_name']);
            $table->index('phone');
            $table->index('nationality');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guests');
    }
};
