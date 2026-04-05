<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend iptv_devices with fields needed for full remote management
        Schema::table('iptv_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('iptv_devices', 'device_name'))
                $table->string('device_name')->nullable()->after('device_id');
            if (!Schema::hasColumn('iptv_devices', 'device_type'))
                $table->string('device_type')->default('android_tv')->after('device_name');
            if (!Schema::hasColumn('iptv_devices', 'android_version'))
                $table->string('android_version')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'app_version'))
                $table->string('app_version')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'package'))
                $table->string('package')->default('basic');
            if (!Schema::hasColumn('iptv_devices', 'registered_at'))
                $table->timestamp('registered_at')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'last_seen'))
                $table->timestamp('last_seen')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'last_heartbeat'))
                $table->timestamp('last_heartbeat')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'device_info'))
                $table->json('device_info')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'pushed_settings'))
                $table->json('pushed_settings')->nullable();   // settings last pushed to device
            if (!Schema::hasColumn('iptv_devices', 'settings_version'))
                $table->integer('settings_version')->default(0);  // incremented on each push
            if (!Schema::hasColumn('iptv_devices', 'notes'))
                $table->text('notes')->nullable();
            if (!Schema::hasColumn('iptv_devices', 'registration_token'))
                $table->string('registration_token', 64)->nullable()->unique(); // one-time token
        });

        // Remote command queue: server → device
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_device_id')->constrained('iptv_devices')->cascadeOnDelete();
            $table->string('type');          // reboot | refresh_channels | push_settings | set_channel | message | lock | unlock
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'delivered', 'executed', 'failed'])->default('pending');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->string('dispatched_by')->nullable(); // admin username
            $table->timestamps();

            $table->index(['iptv_device_id', 'status']);
        });

        // Heartbeat / activity log
        Schema::create('device_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_device_id')->constrained('iptv_devices')->cascadeOnDelete();
            $table->string('status');          // online | buffering | idle | error
            $table->string('current_channel')->nullable();
            $table->string('app_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('settings_version')->default(0);
            $table->json('extra')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['iptv_device_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_heartbeats');
        Schema::dropIfExists('device_commands');
    }
};
