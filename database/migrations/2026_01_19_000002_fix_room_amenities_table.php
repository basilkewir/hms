<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the table has the old structure and needs to be updated
        if (Schema::hasTable('room_amenities')) {
            if (!Schema::hasColumn('room_amenities', 'name')) {
                // The table has the old structure, we need to add the new columns
                Schema::table('room_amenities', function (Blueprint $table) {
                    $table->string('name')->nullable()->after('id');
                    $table->string('icon')->nullable()->after('name');
                    $table->text('description')->nullable()->change();
                    $table->boolean('is_active')->default(true)->after('description');
                });
            }
        }
    }

    public function down(): void
    {
        // We won't remove columns in the down migration to avoid data loss
    }
};
