<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('hotel_id', 100)->nullable()->index()->after('license_key');
            $table->string('hotel_name')->nullable()->after('hotel_id');
            $table->integer('current_devices')->default(0)->after('max_devices');
            $table->integer('validation_count')->default(0)->after('last_validated_at');
            $table->json('features')->nullable()->after('allowed_features');
            $table->json('metadata')->nullable()->after('device_info');
            $table->integer('assigned_rooms')->nullable()->after('metadata');
            $table->index(['status', 'expires_at']);
            $table->index(['license_type', 'status']);
        });

        Schema::create('license_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id');
            $table->string('device_id')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_os')->nullable();
            $table->string('device_os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->integer('activation_count')->default(1);
            $table->timestamp('first_activated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
            $table->index(['license_id', 'device_fingerprint']);
            $table->index('status');

            $table->timestamps();
        });

        Schema::create('license_validation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->string('device_id')->nullable();
            $table->string('validation_type')->default('initial');
            $table->enum('status', ['success', 'invalid', 'expired', 'blocked', 'failed'])->default('invalid');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->float('processing_time')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
            $table->index(['license_id', 'status']);
            $table->index('validated_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_validation_logs');
        Schema::dropIfExists('license_devices');

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            $table->dropIndex(['license_type', 'status']);
            $table->dropColumn([
                'hotel_id',
                'hotel_name',
                'current_devices',
                'validation_count',
                'features',
                'metadata',
                'assigned_rooms',
            ]);
        });
    }
};
