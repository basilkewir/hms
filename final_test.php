<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Final verification of accountant budget permissions...\n";

// Find the accountant role
$accountantRole = Role::where('name', 'accountant')->first();

if (!$accountantRole) {
    echo "Error: Accountant role not found.\n";
    exit(1);
}

echo "Found accountant role: {$accountantRole->display_name}\n";

// Test key budget permissions
$budgetPermissions = [
    'view_budgets',
    'create_budgets',
    'edit_budgets',
    'approve_budgets',
    'monitor_budgets',
    'view_budget_analytics',
    'export_budget_data'
];

echo "\nTesting key budget permissions:\n";
$hasAllPermissions = true;

foreach ($budgetPermissions as $permissionName) {
    $permission = Permission::where('name', $permissionName)->first();
    if ($permission) {
        $hasPermission = $accountantRole->hasPermission($permission);
        $status = $hasPermission ? '✓' : '✗';
        echo "  {$status} {$permissionName}\n";
        if (!$hasPermission) {
            $hasAllPermissions = false;
        }
    } else {
        echo "  ✗ {$permissionName} (not found)\n";
        $hasAllPermissions = false;
    }
}

// Count total budget permissions
$accountantRole->load('permissions');
$budgetPerms = $accountantRole->permissions->filter(function($perm) {
    return $perm->category === 'Budget';
});

echo "\nAccountant role has {$budgetPerms->count()} budget permissions.\n";

if ($hasAllPermissions && $budgetPerms->count() >= 5) {
    echo "\n🎉 SUCCESS: Accountant role has all required budget permissions!\n";
    echo "\nThe following budget pages should now be accessible to accountants:\n";
    echo "  ✓ http://localhost:8000/admin/budget/dashboard\n";
    echo "  ✓ http://localhost:8000/admin/budget/reports\n";
    echo "  ✓ http://localhost:8000/admin/budget/analytics\n";
    echo "  ✓ http://localhost:8000/admin/budget/alerts\n";
    echo "\nAccountants can now:\n";
    echo "  - View budget dashboard and analytics\n";
    echo "  - Create, edit, and manage budgets\n";
    echo "  - Approve budgets and monitor utilization\n";
    echo "  - Export budget data and generate reports\n";
    echo "  - Set up budget alerts and notifications\n";
} else {
    echo "\n❌ FAILURE: Accountant role still missing some budget permissions.\n";
}

echo "\nTask completed!\n";
