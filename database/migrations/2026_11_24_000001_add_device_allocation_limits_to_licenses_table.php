<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->unsignedInteger('max_android_tv')->default(0);
            $table->unsignedInteger('max_smart_tv')->default(0);
            $table->unsignedInteger('max_backend')->default(0);
            $table->unsignedInteger('max_admin_panel')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn([
                'max_android_tv',
                'max_smart_tv',
                'max_backend',
                'max_admin_panel',
            ]);
        });
    }
};
