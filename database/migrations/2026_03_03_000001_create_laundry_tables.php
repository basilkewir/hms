<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // staff who created
            $table->enum('status', ['pending', 'picked_up', 'in_progress', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->enum('priority', ['normal', 'express', 'overnight'])->default('normal');
            $table->date('pickup_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('pickup_time')->nullable();
            $table->time('delivery_time')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('express_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'billed_to_room'])->default('unpaid');
            $table->text('special_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('laundry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_order_id')->constrained('laundry_orders')->cascadeOnDelete();
            $table->string('item_name');
            $table->enum('service_type', ['wash', 'dry_clean', 'iron', 'wash_iron', 'dry_clean_iron'])->default('wash');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_items');
        Schema::dropIfExists('laundry_orders');
    }
};
