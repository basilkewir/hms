<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Hashed confirmation token — used to authenticate booking lookup requests
            // without exposing sequential/guessable reservation numbers (prevents IDOR).
            $table->string('confirmation_token', 64)->nullable()->after('reservation_number');
            $table->index('confirmation_token');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['confirmation_token']);
            $table->dropColumn('confirmation_token');
        });
    }
};
