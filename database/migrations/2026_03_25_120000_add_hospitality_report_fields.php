<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add charge_type & is_revenue to folio_charges
        Schema::table('folio_charges', function (Blueprint $table) {
            $table->enum('charge_type', [
                'room',
                'food_beverage',
                'service_charge',
                'spa',
                'laundry',
                'damage',
                'incidental',
                'tax',
                'other',
            ])->default('other')->after('charge_code');

            $table->boolean('is_revenue')->default(true)->after('charge_type')
                ->comment('false = non-revenue recovery charge (e.g. damage fee)');

            $table->index('charge_type');
        });

        // Backfill charge_type from existing charge_code + reference_type
        DB::statement("
            UPDATE folio_charges SET
                charge_type = CASE
                    WHEN UPPER(charge_code) LIKE '%ROOM%'                          THEN 'room'
                    WHEN reference_type = 'damage'                                 THEN 'damage'
                    WHEN UPPER(charge_code) IN ('SERVICE','SVC','SERVICE_CHARGE')  THEN 'service_charge'
                    WHEN UPPER(charge_code) IN ('FOOD','BEVERAGE','F&B','FB','BAR','RESTAURANT','MENU') THEN 'food_beverage'
                    WHEN UPPER(charge_code) IN ('SPA','MASSAGE','POOL','FITNESS')  THEN 'spa'
                    WHEN UPPER(charge_code) IN ('LAUNDRY','LINEN','DRY_CLEAN')     THEN 'laundry'
                    WHEN UPPER(charge_code) IN ('MINIBAR','MINI_BAR','PHONE','TELEPHONE','INTERNET','WIFI') THEN 'incidental'
                    WHEN UPPER(charge_code) IN ('TAX','VAT','GST')                 THEN 'tax'
                    ELSE 'other'
                END,
                is_revenue = CASE
                    WHEN reference_type = 'damage' THEN 0
                    ELSE 1
                END
        ");

        // Add tip_amount to sales (POS) for gratuity tracking
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('tip_amount', 10, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('folio_charges', function (Blueprint $table) {
            $table->dropIndex(['charge_type']);
            $table->dropColumn(['charge_type', 'is_revenue']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('tip_amount');
        });
    }
};
