<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfers', 'from_location_id')) {
                $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete()->after('to_warehouse_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfers', 'from_location_id')) {
                $table->dropForeign(['from_location_id']);
                $table->dropColumn('from_location_id');
            }
        });
    }
};
