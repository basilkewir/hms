<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Expand POS payment_method enums to support mobile and room_charge.
     */
    public function up(): void
    {
        // sales.payment_method
        DB::statement("
            ALTER TABLE `sales`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer', 'mobile', 'room_charge')
            NOT NULL
        ");

        // pos_transactions.payment_method
        DB::statement("
            ALTER TABLE `pos_transactions`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer', 'mobile', 'room_charge')
            NOT NULL
        ");

        // pos_expenses.payment_method
        DB::statement("
            ALTER TABLE `pos_expenses`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer', 'mobile', 'room_charge')
            NOT NULL
        ");
    }

    /**
     * Revert enums back to original 3 methods.
     * NOTE: this will fail if rows using 'mobile' or 'room_charge' still exist.
     */
    public function down(): void
    {
        // sales.payment_method
        DB::statement("
            ALTER TABLE `sales`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer')
            NOT NULL
        ");

        // pos_transactions.payment_method
        DB::statement("
            ALTER TABLE `pos_transactions`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer')
            NOT NULL
        ");

        // pos_expenses.payment_method
        DB::statement("
            ALTER TABLE `pos_expenses`
            MODIFY `payment_method` ENUM('cash', 'card', 'bank_transfer')
            NOT NULL
        ");
    }
};

