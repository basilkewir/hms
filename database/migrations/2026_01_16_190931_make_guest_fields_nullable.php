<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('occupation')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->string('emergency_contact_name')->nullable()->change();
            $table->string('emergency_contact_phone')->nullable()->change();
            $table->string('emergency_contact_relationship')->nullable()->change();
            $table->text('emergency_contact_address')->nullable()->change();
            $table->string('id_type')->nullable()->change();
            $table->string('id_number')->nullable()->change();
            $table->string('id_issuing_authority')->nullable()->change();
            $table->string('arrival_from')->nullable()->change();
            $table->string('departure_to')->nullable()->change();
            $table->string('purpose_of_visit')->nullable()->change();
            $table->integer('expected_duration_days')->nullable()->change();
            $table->integer('total_companions')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
