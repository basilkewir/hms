<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iptv_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('iptv_devices', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('status');
                $table->index('last_activity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('iptv_devices', function (Blueprint $table) {
            if (Schema::hasColumn('iptv_devices', 'last_activity')) {
                $table->dropIndex(['last_activity']);
                $table->dropColumn('last_activity');
            }
        });
    }
};
