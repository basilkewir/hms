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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->after('notes')
                  ->constrained('budgets')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->after('budget_id')
                  ->constrained('guests')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->after('notes')
                  ->constrained('budgets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropForeign(['guest_id']);
            $table->dropColumn(['budget_id', 'guest_id']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
        });
    }
};
