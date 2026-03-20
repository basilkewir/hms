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
        if (!Schema::hasTable('pos_transactions') || Schema::hasColumn('pos_transactions', 'user_id')) {
            return;
        }

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('sale_id')
                  ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('pos_transactions') || !Schema::hasColumn('pos_transactions', 'user_id')) {
            return;
        }

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
