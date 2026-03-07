<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->date('id_issue_date')->nullable()->change();
            $table->date('id_expiry_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->date('id_issue_date')->nullable(false)->change();
        });
    }
};
