<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the room_id column exists and make it nullable
        if (Schema::hasColumn('room_amenities', 'room_id')) {
            Schema::table('room_amenities', function (Blueprint $table) {
                $table->unsignedBigInteger('room_id')->nullable()->change();
            });
        }
    }

    public function down()
    {
        // Revert back to non-nullable if needed
        if (Schema::hasColumn('room_amenities', 'room_id')) {
            Schema::table('room_amenities', function (Blueprint $table) {
                $table->unsignedBigInteger('room_id')->nullable(false)->change();
            });
        }
    }
};
