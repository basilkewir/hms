<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add margin_percentage to products
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('margin_percentage', 8, 4)->nullable()->after('cost_price');
        });

        // Add sale_price and location_id to stock_batches
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('unit_cost');
            if (Schema::hasColumn('stock_batches', 'location_id')) {
                // already exists from locations migration
            } else {
                $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete()->after('sale_price');
            }
        });

        // Add location_id to stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'location_id')) {
                $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('margin_percentage');
        });

        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropColumn('sale_price');
            if (Schema::hasColumn('stock_batches', 'location_id')) {
                $table->dropForeignIdFor(\App\Models\Location::class);
                $table->dropColumn('location_id');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'location_id')) {
                $table->dropForeignIdFor(\App\Models\Location::class);
                $table->dropColumn('location_id');
            }
        });
    }
};
