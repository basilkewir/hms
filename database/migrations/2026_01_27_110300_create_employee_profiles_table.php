<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();

            $table->string('employee_code')->unique();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();

            $table->string('job_title')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();

            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'casual'])->default('full_time');
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('eligible_for_overtime')->default(true);

            $table->string('pay_frequency')->default('monthly'); // monthly, bi_weekly, weekly
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();

            $table->json('contact_person')->nullable(); // emergency contact details
            $table->json('settings')->nullable(); // custom per‑employee preferences

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};

