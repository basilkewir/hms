<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Supplier payments table
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['partial', 'full']);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'credit_card']);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['supplier_id', 'payment_date']);
            $table->index('payment_number');
        });

        // Stock batches table
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('batch_number')->unique();
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['product_id', 'received_date']);
            $table->index('batch_number');
        });

        // Purchase documents table
        Schema::create('purchase_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // receipt, invoice, delivery_note, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['purchase_order_id', 'document_type']);
        });

        // Expense documents table
        Schema::create('expense_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('pos_expenses')->onDelete('cascade');
            $table->string('document_type'); // receipt, invoice, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['expense_id', 'document_type']);
        });

        // Delivery documents table
        Schema::create('delivery_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // delivery_note, proof_of_delivery, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['purchase_order_id', 'document_type']);
        });

        // Update purchase_orders table to add delivery conditions
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->integer('delivery_time_days')->nullable()->after('expected_date');
            $table->text('purchase_conditions')->nullable()->after('notes');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('paid_amount');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_time_days', 'purchase_conditions', 'paid_amount', 'remaining_amount']);
        });

        Schema::dropIfExists('delivery_documents');
        Schema::dropIfExists('expense_documents');
        Schema::dropIfExists('purchase_documents');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('supplier_payments');
    }
};
