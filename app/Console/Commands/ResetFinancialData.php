<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetFinancialData extends Command
{
    protected $signature = 'hms:reset-financials {--force : Skip confirmation prompt}';

    protected $description = 'Clear all transaction tables (sales, folios, payments, expenses, reservations, procurement) while keeping products with zero stock';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                'This will permanently delete ALL financial transactions including reservations, sales, folios, payments, expenses, procurement history, and set product stock to zero. Continue?',
                false
            );

            if (!$confirmed) {
                $this->warn('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $tablesToDeleteInOrder = [
            'expense_documents',
            'delivery_documents',
            'purchase_documents',
            'pos_return_requests',
            'sale_items',
            'pos_transactions',
            'hall_booking_payments',
            'supplier_payments',
            'purchase_order_items',
            'stock_batches',
            'stock_transfers',
            'stock_adjustments',
            'stock_movements',
            'budget_expenses',
            'pos_expenses',
            'expenses',
            'payments',
            'folio_charges',
            'sales',
            'guest_folios',
            'reservation_services',
            'reservation_room',
            'reservations',
            'purchase_orders',
            'cash_drawer_sessions',
            'employee_payroll',
            'payroll_periods',
        ];

        $deletedCounts = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::beginTransaction();

        try {
            foreach ($tablesToDeleteInOrder as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $count = (int) DB::table($table)->count();

                if ($count > 0) {
                    DB::table($table)->delete();
                }

                $deletedCounts[$table] = $count;
            }

            if (Schema::hasTable('products')) {
                $productUpdatePayload = ['stock_quantity' => 0];

                if (Schema::hasColumn('products', 'updated_at')) {
                    $productUpdatePayload['updated_at'] = now();
                }

                DB::table('products')->update($productUpdatePayload);
            }

            if (Schema::hasTable('product_warehouse')) {
                $warehouseUpdatePayload = ['quantity' => 0];

                if (Schema::hasColumn('product_warehouse', 'updated_at')) {
                    $warehouseUpdatePayload['updated_at'] = now();
                }

                DB::table('product_warehouse')->update($warehouseUpdatePayload);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Reset failed: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Financial reset completed.');

        foreach ($deletedCounts as $table => $count) {
            $this->line(sprintf('  - %s: %d deleted', $table, $count));
        }

        if (Schema::hasTable('products')) {
            $productsCount = (int) DB::table('products')->count();
            $this->line(sprintf('  - products retained: %d (stock_quantity set to 0)', $productsCount));
        }

        if (Schema::hasTable('product_warehouse')) {
            $this->line('  - product_warehouse quantities set to 0');
        }

        return self::SUCCESS;
    }
}