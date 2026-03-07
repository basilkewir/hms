<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Department;

class BudgetsSeeder extends Seeder
{
    public function run()
    {
        // Check if we already have budgets
        if (Budget::count() > 0) {
            $this->command->info('Budgets already exist, skipping...');
            return;
        }

        // Get categories and departments
        $categories = ExpenseCategory::all();
        $departments = Department::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No expense categories found. Please run the seeder for categories first.');
            return;
        }

        $currentYear = now()->year;

        // Create sample budgets for current year
        $sampleBudgets = [
            [
                'name' => 'Marketing Campaign Q1',
                'description' => 'Digital marketing and advertising budget for Q1 2026',
                'amount' => 50000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-03-31",
                'category_id' => $categories->where('code', 'MARK')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Includes social media, Google Ads, and email campaigns',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'IT Infrastructure',
                'description' => 'Server maintenance, software licenses, and network upgrades',
                'amount' => 75000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-12-31",
                'category_id' => $categories->where('code', 'TECH')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->skip(1)->first()?->id ?? $departments->first()?->id,
                'notes' => 'Annual IT budget for system maintenance',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Staff Training Program',
                'description' => 'Employee development and certification programs',
                'amount' => 25000.00,
                'start_date' => "{$currentYear}-02-01",
                'end_date' => "{$currentYear}-06-30",
                'category_id' => $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Customer service and technical training',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Restaurant Supplies',
                'description' => 'Kitchen equipment and restaurant supplies',
                'amount' => 30000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-06-30",
                'category_id' => $categories->where('code', 'FB')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->skip(2)->first()?->id ?? $departments->first()?->id,
                'notes' => 'Kitchen supplies and tableware',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Property Maintenance',
                'description' => 'Building repairs and facility maintenance',
                'amount' => 100000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-12-31",
                'category_id' => $categories->where('code', 'MAINT')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->skip(3)->first()?->id ?? $departments->first()?->id,
                'notes' => 'Annual maintenance budget',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Utilities - First Half',
                'description' => 'Electricity, water, and gas for first half of year',
                'amount' => 45000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-06-30",
                'category_id' => $categories->where('code', 'UTIL')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Utility expenses',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Marketing Q2',
                'description' => 'Spring marketing campaigns',
                'amount' => 35000.00,
                'start_date' => "{$currentYear}-04-01",
                'end_date' => "{$currentYear}-06-30",
                'category_id' => $categories->where('code', 'MARK')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Spring promotions and events',
                'status' => 'pending_approval',
                'created_by' => 1,
            ],
            [
                'name' => 'Pool Area Renovation',
                'description' => 'Renovation of pool and spa area',
                'amount' => 150000.00,
                'start_date' => "{$currentYear}-09-01",
                'end_date' => "{$currentYear}-11-30",
                'category_id' => $categories->where('code', 'MAINT')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Major renovation project',
                'status' => 'draft',
                'created_by' => 1,
            ],
            [
                'name' => 'Annual Insurance Premium',
                'description' => 'Property and liability insurance',
                'amount' => 60000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-12-31",
                'category_id' => $categories->where('code', 'INS')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->first()?->id,
                'notes' => 'Annual insurance coverage',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Housekeeping Supplies',
                'description' => 'Cleaning supplies and amenities',
                'amount' => 20000.00,
                'start_date' => "{$currentYear}-01-01",
                'end_date' => "{$currentYear}-03-31",
                'category_id' => $categories->where('code', 'SUPP')->first()?->id ?? $categories->first()->id,
                'department_id' => $departments->skip(4)->first()?->id ?? $departments->first()?->id,
                'notes' => 'Q1 housekeeping supplies',
                'status' => 'approved',
                'approved_by' => 1,
                'created_by' => 1,
            ],
        ];

        foreach ($sampleBudgets as $budgetData) {
            Budget::create($budgetData);
            $this->command->info("Created budget: {$budgetData['name']}");
        }

        // Add some actual spent amounts to simulate real usage
        $budgets = Budget::all();
        foreach ($budgets as $budget) {
            // Simulate some spent amounts (between 0% and 85% of budget)
            $spentPercentage = mt_rand(10, 85) / 100;
            $spentAmount = $budget->amount * $spentPercentage;

            $budget->update([
                'spent_amount' => $spentAmount,
            ]);

            $percentageDisplay = round($spentPercentage * 100, 1);
            $this->command->info("Updated {$budget->name}: Spent {$percentageDisplay}%");
        }

        $this->command->info("Created {$budgets->count()} sample budgets with realistic spending data!");
    }
}
