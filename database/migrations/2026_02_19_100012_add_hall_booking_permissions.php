<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('can_book_halls')->default(false);
            $table->boolean('can_manage_halls')->default(false);
            $table->boolean('can_manage_packages')->default(false);
            $table->boolean('can_manage_group_bookings')->default(false);
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['can_book_halls', 'can_manage_halls', 'can_manage_packages', 'can_manage_group_bookings']);
        });
    }
};
