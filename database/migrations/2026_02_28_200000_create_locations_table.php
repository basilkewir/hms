<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['warehouse', 'restaurant', 'frontdesk', 'bar', 'kitchen', 'other']);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add location_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
        });

        // Add location_id to stock_batches table
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
        });

        // Add destination_location_id to stock_transfers table
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('destination_location_id')->nullable()->constrained('locations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['destination_location_id']);
            $table->dropColumn('destination_location_id');
        });

        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['location_id']);
            $table->dropColumn('location_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['location_id']);
            $table->dropColumn('location_id');
        });

        Schema::dropIfExists('locations');
    }
};
