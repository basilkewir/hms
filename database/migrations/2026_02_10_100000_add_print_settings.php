<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add print settings to settings table
        DB::table('settings')->updateOrInsert(
            ['key' => 'pos_print_paper_width'],
            ['value' => '80', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'pos_print_font_size'],
            ['value' => '12', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'pos_print_show_logo'],
            ['value' => '1', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'frontdesk_print_paper_width'],
            ['value' => '80', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'frontdesk_print_font_size'],
            ['value' => '12', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'frontdesk_print_show_logo'],
            ['value' => '1', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'pos_print_paper_width',
            'pos_print_font_size',
            'pos_print_show_logo',
            'frontdesk_print_paper_width',
            'frontdesk_print_font_size',
            'frontdesk_print_show_logo',
        ])->delete();
    }
};
