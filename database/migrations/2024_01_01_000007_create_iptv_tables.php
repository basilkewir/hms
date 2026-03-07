<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Note: IPTV Channels will be managed via Xtream Codes API
        // No local channel storage needed

        // IPTV Packages (still needed for room assignments)
        Schema::create('iptv_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Premium, VIP
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 8, 2)->default(0);
            $table->boolean('includes_adult_content')->default(false);
            $table->boolean('includes_premium_channels')->default(false);
            $table->boolean('includes_international_channels')->default(false);
            $table->json('xtream_categories')->nullable(); // Xtream Codes category IDs
            $table->json('xtream_channel_groups')->nullable(); // Specific channel group IDs from Xtream
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Room IPTV Settings (updated for Xtream Codes)
        Schema::create('room_iptv_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('iptv_package_id')->constrained();
            $table->json('xtream_custom_categories')->nullable(); // Additional Xtream category IDs
            $table->json('xtream_blocked_categories')->nullable(); // Blocked Xtream category IDs
            $table->json('xtream_blocked_channels')->nullable(); // Blocked specific channel IDs from Xtream
            $table->boolean('adult_content_enabled')->default(false);
            $table->string('parental_control_pin')->nullable();
            $table->integer('volume_limit')->default(100); // Max volume %
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->json('language_preferences')->nullable();
            $table->boolean('auto_power_off')->default(false);
            $table->time('auto_power_off_time')->nullable();
            $table->timestamps();

            $table->unique('room_id');
        });

        // IPTV Device Management
        Schema::create('iptv_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique(); // Android device ID
            $table->string('device_name')->nullable();
            $table->string('mac_address')->unique();
            $table->string('ip_address')->nullable();
            $table->foreignId('room_id')->nullable()->constrained();
            $table->string('android_version')->nullable();
            $table->string('app_version')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance', 'error'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_heartbeat')->nullable();
            $table->json('device_info')->nullable(); // Hardware specs, etc.
            $table->json('current_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['status', 'is_active']);
            $table->index('room_id');
        });

        // IPTV Usage Logs (updated for Xtream Codes)
        Schema::create('iptv_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('reservation_id')->nullable()->constrained();
            $table->foreignId('iptv_device_id')->constrained();
            $table->string('xtream_channel_id')->nullable(); // Channel ID from Xtream Codes API
            $table->string('xtream_stream_id')->nullable(); // Stream ID from Xtream Codes API
            $table->string('channel_name')->nullable(); // Channel name for logging
            $table->string('action'); // channel_change, volume_change, power_on, power_off
            $table->json('action_data')->nullable(); // Additional action details
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('guest_ip')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'started_at']);
            $table->index(['reservation_id', 'started_at']);
            $table->index('action');
            $table->index('xtream_channel_id');
        });

        // VOD Content
        Schema::create('vod_content', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('genre');
            $table->string('language')->default('English');
            $table->year('release_year')->nullable();
            $table->string('rating')->nullable(); // G, PG, PG-13, R, etc.
            $table->integer('duration_minutes')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('stream_url');
            $table->decimal('rental_price', 6, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_adult_content')->default(false);
            $table->boolean('requires_subscription')->default(false);
            $table->integer('view_count')->default(0);
            $table->decimal('rating_score', 3, 1)->nullable(); // 1.0 to 10.0
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['genre', 'is_active']);
            $table->index(['language', 'is_active']);
            $table->index('rating');
        });

        // VOD Viewing History
        Schema::create('vod_viewing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('reservation_id')->nullable()->constrained();
            $table->foreignId('vod_content_id')->constrained('vod_content');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('watch_duration_seconds')->default(0);
            $table->integer('total_duration_seconds');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('rental_charge', 6, 2)->default(0);
            $table->boolean('was_completed')->default(false);
            $table->timestamps();
            
            $table->index(['room_id', 'started_at']);
            $table->index(['reservation_id', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vod_viewing_history');
        Schema::dropIfExists('vod_content');
        Schema::dropIfExists('iptv_usage_logs');
        Schema::dropIfExists('iptv_devices');
        Schema::dropIfExists('room_iptv_settings');
        Schema::dropIfExists('iptv_packages');
        // Note: iptv_channels and iptv_package_channels tables removed
    }
};
