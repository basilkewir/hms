<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_bill_adjustment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('guest_folio_id')->nullable()->constrained('guest_folios')->nullOnDelete();
            $table->foreignId('folio_charge_id')->nullable()->constrained('folio_charges')->nullOnDelete();
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255);
            $table->text('request_notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['reservation_id', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_bill_adjustment_requests');
    }
};