<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Testing accountant budget permissions...\n";

// Find the accountant role
$accountantRole = Role::where('name', 'accountant')->first();

if (!$accountantRole) {
    echo "Error: Accountant role not found.\n";
    exit(1);
}

echo "Found accountant role: {$accountantRole->display_name}\n";

// Test specific budget permissions
$budgetPermissions = [
    'view_budgets',
    'create_budgets',
    'edit_budgets',
    'approve_budgets',
    'monitor_budgets',
    'view_budget_analytics',
    'export_budget_data'
];

echo "\nTesting budget permissions:\n";
foreach ($budgetPermissions as $permissionName) {
    $permission = Permission::where('name', $permissionName)->first();
    if ($permission) {
        $hasPermission = $accountantRole->hasPermission($permission);
        $status = $hasPermission ? '✓' : '✗';
        echo "  {$status} {$permissionName}\n";
    } else {
        echo "  ✗ {$permissionName} (not found)\n";
    }
}

// Count total budget permissions
$accountantRole->load('permissions');
$budgetPerms = $accountantRole->permissions->filter(function($perm) {
    return $perm->category === 'Budget';
});

echo "\nAccountant role has {$budgetPerms->count()} budget permissions out of " . count($budgetPermissions) . " tested.\n";

if ($budgetPerms->count() >= 5) {
    echo "\n✓ SUCCESS: Accountant role has sufficient budget permissions!\n";
    echo "The accountant should now be able to access:\n";
    echo "  - http://localhost:8000/admin/budget/dashboard\n";
    echo "  - http://localhost:8000/admin/budget/reports\n";
    echo "  - http://localhost:8000/admin/budget/analytics\n";
    echo "  - http://localhost:8000/admin/budget/alerts\n";
} else {
    echo "\n✗ FAILURE: Accountant role needs more budget permissions.\n";
}

echo "\nDone!\n";
