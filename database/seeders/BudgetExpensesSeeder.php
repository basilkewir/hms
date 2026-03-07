<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\User;

class BudgetExpensesSeeder extends Seeder
{
    public function run()
    {
        if (BudgetExpense::count() > 0) {
            $this->command->info('Budget expenses already exist, skipping...');
            return;
        }

        $budgets = Budget::whereIn('status', ['approved', 'pending_approval'])->get();
        if ($budgets->isEmpty()) {
            $this->command->warn('No budgets found. Please seed budgets first.');
            return;
        }

        $defaultUserId = User::orderBy('id')->value('id');

        $vendors = [
            'Office Supplies Inc.',
            'City Maintenance Co.',
            'Tech Solutions Ltd.',
            'Food Wholesale Market',
            'Utility Provider',
            'Printing & Media House',
        ];

        foreach ($budgets as $budget) {
            $start = $budget->start_date ? $budget->start_date->copy() : now()->subMonths(2);
            $end = $budget->end_date ? $budget->end_date->copy() : now();
            if ($end->lt($start)) {
                $end = $start->copy();
            }

            $count = random_int(2, 6);
            for ($i = 0; $i < $count; $i++) {
                $date = $start->copy()->addDays(random_int(0, max(0, $start->diffInDays($end))));

                $status = collect(['pending', 'approved', 'paid', 'rejected'])->random();
                $approvedAt = null;
                $approvedBy = null;

                if ($status !== BudgetExpense::STATUS_PENDING) {
                    $approvedAt = $date->copy()->addDays(random_int(0, 5));
                    $approvedBy = $defaultUserId;
                }

                BudgetExpense::create([
                    'budget_id' => $budget->id,
                    'description' => 'Sample expense ' . ($i + 1) . ' for ' . $budget->name,
                    'amount' => random_int(50, 2500),
                    'expense_date' => $date->toDateString(),
                    'vendor' => $vendors[array_rand($vendors)],
                    'receipt_number' => 'REC-' . random_int(10000, 99999),
                    'notes' => random_int(0, 1) ? 'Auto-generated sample expense record.' : null,
                    'created_by' => $defaultUserId,
                    'status' => $status,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                ]);
            }
        }

        $this->command->info('Created sample budget expenses: ' . BudgetExpense::count());
    }
}
