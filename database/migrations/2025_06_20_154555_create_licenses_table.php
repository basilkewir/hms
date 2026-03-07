<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique();
            $table->string('license_type')->default('iptv'); // iptv, hotel_management, premium
            $table->string('product_name');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('organization')->nullable();
            $table->integer('max_devices')->default(1);
            $table->integer('max_rooms')->default(10);
            $table->integer('max_channels')->default(50);
            $table->boolean('vod_enabled')->default(false);
            $table->boolean('premium_features')->default(false);
            $table->json('allowed_features')->nullable(); // JSON array of allowed features
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->string('status')->default('active'); // active, expired, suspended, revoked
            $table->string('hardware_fingerprint')->nullable();
            $table->string('activation_code')->nullable();
            $table->integer('activation_count')->default(0);
            $table->integer('max_activations')->default(1);
            $table->json('device_info')->nullable(); // Store device information
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['license_key', 'status']);
            $table->index(['customer_email', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
