<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Guest Folios (Bills)
        Schema::create('guest_folios', function (Blueprint $table) {
            $table->id();
            $table->string('folio_number')->unique();
            $table->foreignId('reservation_id')->constrained()->onDelete('restrict');
            $table->foreignId('guest_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_id')->constrained()->onDelete('restrict');
            
            // Folio Status
            $table->enum('status', ['open', 'closed', 'transferred', 'voided'])->default('open');
            $table->date('folio_date');
            
            // Totals
            $table->decimal('room_charges', 12, 2)->default(0);
            $table->decimal('service_charges', 12, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            
            // Closure Details
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'folio_date']);
            $table->index('reservation_id');
        });

        // Folio Charges
        Schema::create('folio_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_folio_id')->constrained()->onDelete('cascade');
            $table->string('charge_code'); // ROOM, FOOD, BEVERAGE, IPTV, etc.
            $table->string('description');
            $table->date('charge_date');
            $table->time('charge_time')->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0); // Percentage
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            
            // Reference Information
            $table->string('reference_type')->nullable(); // reservation, iptv_usage, service_order
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('department')->nullable(); // Front Office, F&B, Housekeeping
            
            // Posting Details
            $table->foreignId('posted_by')->constrained('users');
            $table->timestamp('posted_at')->useCurrent();
            $table->boolean('is_voided')->default(false);
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['guest_folio_id', 'charge_date']);
            $table->index(['charge_code', 'charge_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('guest_folio_id')->constrained()->onDelete('restrict');
            $table->foreignId('reservation_id')->constrained()->onDelete('restrict');
            
            // Payment Details
            $table->enum('payment_method', [
                'cash', 'credit_card', 'debit_card', 'bank_transfer', 
                'check', 'mobile_payment', 'crypto', 'comp'
            ]);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('local_amount', 12, 2); // Amount in hotel's currency
            
            // Card/Electronic Payment Details
            $table->string('card_type')->nullable(); // Visa, MasterCard, etc.
            $table->string('card_last_four')->nullable();
            $table->string('authorization_code')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('processor_response')->nullable();
            
            // Check Details
            $table->string('check_number')->nullable();
            $table->string('bank_name')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'voided'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->constrained('users');
            
            // Refund Information
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users');
            $table->text('refund_reason')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'processed_at']);
            $table->index(['payment_method', 'processed_at']);
        });

        // Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('parent_category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Hotel Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('expense_category_id')->constrained();
            $table->string('vendor_name');
            $table->text('description');
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_method', ['cash', 'check', 'credit_card', 'bank_transfer']);
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file_path')->nullable();
            
            // Approval Workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('submitted_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Payment Details
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->string('payment_reference')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'expense_date']);
            $table->index(['expense_category_id', 'expense_date']);
        });

        // Payroll
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name'); // "January 2024", "Week 1 - 2024"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('frequency', ['weekly', 'bi_weekly', 'monthly'])->default('bi_weekly');
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->decimal('total_gross_pay', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net_pay', 15, 2)->default(0);
            $table->foreignId('calculated_by')->nullable()->constrained('users');
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['start_date', 'end_date']);
        });

        // Employee Payroll
        Schema::create('employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->decimal('regular_hours', 6, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('holiday_hours', 6, 2)->default(0);
            $table->decimal('sick_hours', 6, 2)->default(0);
            $table->decimal('vacation_hours', 6, 2)->default(0);
            $table->decimal('regular_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('holiday_pay', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('federal_tax', 8, 2)->default(0);
            $table->decimal('state_tax', 8, 2)->default(0);
            $table->decimal('social_security', 8, 2)->default(0);
            $table->decimal('medicare', 8, 2)->default(0);
            $table->decimal('health_insurance', 8, 2)->default(0);
            $table->decimal('other_deductions', 8, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['payroll_period_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_payroll');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('folio_charges');
        Schema::dropIfExists('guest_folios');
    }
};
