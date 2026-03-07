<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the old table exists and the new table doesn't exist
        if (Schema::hasTable('role_permissions') && !Schema::hasTable('role_has_permissions')) {
            Schema::rename('role_permissions', 'role_has_permissions');
        }

        // If the new table doesn't exist but old table doesn't exist either, create it
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->unique(['role_id', 'permission_id']);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Reverse the rename if needed
        if (Schema::hasTable('role_has_permissions')) {
            Schema::rename('role_has_permissions', 'role_permissions');
        }
    }
};
