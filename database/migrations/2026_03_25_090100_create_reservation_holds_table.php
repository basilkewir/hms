<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservation_holds')) {
            Schema::drop('reservation_holds');
        }

        Schema::create('reservation_holds', function (Blueprint $table) {
            $table->id();
            $table->string('hold_token')->unique();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('status', ['active', 'consumed', 'expired', 'cancelled'])->default('active');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('created_from_ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['room_type_id', 'check_in_date', 'check_out_date'], 'res_hold_room_dates_idx');
            $table->index(['status', 'expires_at'], 'res_hold_status_exp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_holds');
    }
};
