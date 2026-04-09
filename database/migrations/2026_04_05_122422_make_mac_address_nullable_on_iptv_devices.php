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
        Schema::table('iptv_devices', function (Blueprint $table) {
            // Android devices don't always expose MAC address (restricted since Android 10).
            // Make it nullable so registration works without a MAC.
            $table->string('mac_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('iptv_devices', function (Blueprint $table) {
            $table->string('mac_address')->nullable(false)->change();
        });
    }
};
