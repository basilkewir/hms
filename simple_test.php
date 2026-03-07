<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Testing budget permissions...\n";

// Check if permissions exist
$budgetPermissions = Permission::where('category', 'Budget')->get();
echo "Found {$budgetPermissions->count()} budget permissions\n";

// Check accountant role
$accountantRole = Role::where('name', 'accountant')->first();
if ($accountantRole) {
    echo "Accountant role found: {$accountantRole->display_name}\n";

    // Test specific permissions
    $testPerms = ['view_budgets', 'create_budgets', 'edit_budgets', 'approve_budgets'];
    $hasPerms = 0;

    foreach ($testPerms as $permName) {
        $hasPerm = $accountantRole->hasPermission($permName);
        $status = $hasPerm ? '✓' : '✗';
        echo "  {$status} {$permName}\n";
        if ($hasPerm) $hasPerms++;
    }

    if ($hasPerms >= 3) {
        echo "\n🎉 SUCCESS: Accountant has budget permissions!\n";
        echo "Budget pages should now be accessible:\n";
        echo "  - http://localhost:8000/admin/budget/dashboard\n";
        echo "  - http://localhost:8000/admin/budget/reports\n";
        echo "  - http://localhost:8000/admin/budget/analytics\n";
        echo "  - http://localhost:8000/admin/budget/alerts\n";
    } else {
        echo "\n❌ Accountant needs more budget permissions\n";
    }
} else {
    echo "Accountant role not found!\n";
}

echo "\nTest completed!\n";
