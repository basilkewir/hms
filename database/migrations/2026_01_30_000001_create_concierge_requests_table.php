<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concierge_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->string('guest_name');
            $table->string('room_number')->nullable();
            $table->string('service_type');
            $table->string('status')->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->text('details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concierge_requests');
    }
};
