<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('availability_rules')->nullable(); // array of availability rules
            $table->json('booking_blackouts')->nullable(); // array of blackout dates
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['availability_rules', 'booking_blackouts']);
        });
    }
};
