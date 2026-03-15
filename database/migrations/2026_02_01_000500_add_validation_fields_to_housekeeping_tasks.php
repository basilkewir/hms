<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('housekeeping_tasks', 'validation_status')) {
                $table->enum('validation_status', ['validated', 'rejected'])
                      ->nullable()
                      ->after('completed_at');
            }
            if (!Schema::hasColumn('housekeeping_tasks', 'validation_timestamp')) {
                $table->timestamp('validation_timestamp')
                      ->nullable()
                      ->after('validation_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('housekeeping_tasks', 'validation_timestamp')) {
                $table->dropColumn('validation_timestamp');
            }
            if (Schema::hasColumn('housekeeping_tasks', 'validation_status')) {
                $table->dropColumn('validation_status');
            }
        });
    }
};
