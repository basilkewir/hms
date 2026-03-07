<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('customer_id')->constrained('rooms')->onDelete('set null');
            $table->foreignId('reservation_id')->nullable()->after('room_id')->constrained('reservations')->onDelete('set null');
            $table->foreignId('guest_id')->nullable()->after('reservation_id')->constrained('guests')->onDelete('set null');
            $table->boolean('is_charged_to_room')->default(false)->after('guest_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['reservation_id']);
            $table->dropForeign(['guest_id']);
            $table->dropColumn(['room_id', 'reservation_id', 'guest_id', 'is_charged_to_room']);
        });
    }
};
