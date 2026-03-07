<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'group_booking_id')) {
                $table->foreignId('group_booking_id')->nullable()->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('reservations', 'group_booking_details')) {
                $table->json('group_booking_details')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['group_booking_id']);
            $table->dropColumn(['group_booking_id', 'group_booking_details']);
        });
    }
};
