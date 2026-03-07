<?php
require_once 'vendor/autoload.php';

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Department;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = Carbon::now()->year;

$categories = ExpenseCategory::all();
$departments = Department::all();

if ($categories->count() > 0 && $departments->count() > 0) {
    $cat1 = $categories[0]->id ?? 1;
    $cat2 = $categories[1]->id ?? 1;
    $cat3 = $categories[2]->id ?? 1;
    $dept1 = $departments[0]->id ?? 1;
    $dept2 = $departments[1]->id ?? 1;

    Budget::create([
        'name' => 'Marketing Q1 Campaign',
        'description' => 'Q1 2026 Marketing and advertising budget',
        'amount' => 50000,
        'start_date' => Carbon::create($year, 1, 1),
        'end_date' => Carbon::create($year, 3, 31),
        'category_id' => $cat1,
        'department_id' => $dept1,
        'status' => 'approved',
        'created_by' => 1,
    ]);

    Budget::create([
        'name' => 'Operations Maintenance',
        'description' => 'General operations and maintenance budget',
        'amount' => 75000,
        'start_date' => Carbon::create($year, 1, 1),
        'end_date' => Carbon::create($year, 12, 31),
        'category_id' => $cat2,
        'department_id' => $dept2,
        'status' => 'approved',
        'created_by' => 1,
    ]);

    Budget::create([
        'name' => 'Staff Training Program',
        'description' => 'Employee training and development',
        'amount' => 25000,
        'start_date' => Carbon::create($year, 1, 1),
        'end_date' => Carbon::create($year, 6, 30),
        'category_id' => $cat3,
        'department_id' => $dept1,
        'status' => 'pending_approval',
        'created_by' => 1,
    ]);

    echo "Additional budgets created. Total: " . Budget::count() . "\n";
} else {
    echo "Categories: " . $categories->count() . ", Departments: " . $departments->count() . "\n";
}
