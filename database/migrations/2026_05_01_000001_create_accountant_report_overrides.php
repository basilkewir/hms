<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flag on users table: marks the user as a "custom accountant" with report overrides
        if (!Schema::hasColumn('users', 'is_custom_accountant')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_custom_accountant')->default(false)->after('is_active');
            });
        }

        Schema::create('accountant_report_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // report_type: profit_loss | balance_sheet | cash_flow | revenue
            $table->string('report_type', 50);
            // metric_key: e.g. total_revenue, net_profit, total_expenses etc.
            $table->string('metric_key', 100);
            // The value to display (JSON encoded — supports numbers, strings, arrays)
            $table->text('custom_value');
            $table->timestamps();

            $table->unique(['user_id', 'report_type', 'metric_key'], 'aro_unique');
            $table->index(['user_id', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_report_overrides');
        if (Schema::hasColumn('users', 'is_custom_accountant')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_custom_accountant');
            });
        }
    }
};
