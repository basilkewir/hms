<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Testing budget permissions setup...\n";

// Check if permissions table exists and has data
$permissionCount = Permission::count();
echo "Total permissions in database: {$permissionCount}\n";

// Check for budget permissions
$budgetPermissions = Permission::where('category', 'Budget')->get();
echo "Budget permissions found: {$budgetPermissions->count()}\n";

if ($budgetPermissions->count() > 0) {
    echo "Budget permissions:\n";
    foreach ($budgetPermissions as $perm) {
        echo "  - {$perm->name} ({$perm->display_name})\n";
    }
}

// Check accountant role
$accountantRole = Role::where('name', 'accountant')->first();
if ($accountantRole) {
    echo "\nAccountant role found: {$accountantRole->display_name}\n";

    // Load permissions for accountant role
    $accountantRole->load('permissions');
    $accountantPerms = $accountantRole->permissions;
    echo "Accountant role has {$accountantPerms->count()} permissions\n";

    // Check for specific budget permissions
    $keyBudgetPerms = ['view_budgets', 'create_budgets', 'edit_budgets', 'approve_budgets'];
    $hasBudgetPerms = 0;

    foreach ($keyBudgetPerms as $permName) {
        $hasPerm = $accountantRole->hasPermission($permName);
        $status = $hasPerm ? '✓' : '✗';
        echo "  {$status} {$permName}\n";
        if ($hasPerm) $hasBudgetPerms++;
    }

    if ($hasBudgetPerms >= 3) {
        echo "\n🎉 SUCCESS: Accountant role has budget permissions!\n";
        echo "Budget pages should now be accessible:\n";
        echo "  - http://localhost:8000/admin/budget/dashboard\n";
        echo "  - http://localhost:8000/admin/budget/reports\n";
        echo "  - http://localhost:8000/admin/budget/analytics\n";
        echo "  - http://localhost:8000/admin/budget/alerts\n";
    } else {
        echo "\n❌ Accountant role needs more budget permissions\n";
    }
} else {
    echo "Accountant role not found!\n";
}

echo "\nTest completed!\n";
