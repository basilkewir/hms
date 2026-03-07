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
        Schema::table('rooms', function (Blueprint $table) {
            // Change floor from integer to foreign key
            $table->dropColumn('floor');
            $table->foreignId('floor_id')->nullable()->after('room_type_id')->constrained('floors')->onDelete('restrict');
            
            // Change building/wing from string to foreign key
            $table->dropColumn(['building', 'wing']);
            $table->foreignId('building_wing_id')->nullable()->after('floor_id')->constrained('building_wings')->onDelete('restrict');
            
            // Add bed_type_id foreign key if bed_type exists as string
            if (Schema::hasColumn('rooms', 'bed_type')) {
                // We'll keep bed_type as string for now, but add bed_type_id for reference
                $table->foreignId('bed_type_id')->nullable()->after('bed_type')->constrained('bed_types')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');
            $table->integer('floor')->after('room_type_id');
            
            $table->dropForeign(['building_wing_id']);
            $table->dropColumn('building_wing_id');
            $table->string('building')->nullable()->after('floor');
            $table->string('wing')->nullable()->after('building');
            
            if (Schema::hasColumn('rooms', 'bed_type_id')) {
                $table->dropForeign(['bed_type_id']);
                $table->dropColumn('bed_type_id');
            }
        });
    }
};
