<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique()->nullable(); // For staff
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('USA');
            $table->string('postal_code')->nullable();
            
            // Employment details
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('pay_type', ['hourly', 'salary'])->default('hourly');
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Documents and verification
            $table->string('national_id')->nullable(); // SSN, National ID, etc.
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('work_permit')->nullable();
            $table->date('work_permit_expiry')->nullable();
            
            // Profile
            $table->string('avatar')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index(['employment_status', 'is_active']);
            $table->index('department');
            $table->index('hire_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
