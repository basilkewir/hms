<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'features')) {
                $table->json('features')->nullable()->after('housekeeping_status');
            }
            if (!Schema::hasColumn('rooms', 'special_features')) {
                $table->text('special_features')->nullable()->after('features');
            }
            if (!Schema::hasColumn('rooms', 'notes')) {
                $table->text('notes')->nullable()->after('special_features');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['features', 'special_features', 'notes']);
        });
    }
};
