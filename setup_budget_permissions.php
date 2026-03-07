<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Setting up budget permissions for accountant role...\n";

// Budget permissions to create
$budgetPermissions = [
    ['name' => 'view_budgets', 'display_name' => 'View Budgets', 'category' => 'Budget', 'description' => 'View budget information and reports'],
    ['name' => 'create_budgets', 'display_name' => 'Create Budgets', 'category' => 'Budget', 'description' => 'Create new budgets'],
    ['name' => 'edit_budgets', 'display_name' => 'Edit Budgets', 'category' => 'Budget', 'description' => 'Edit existing budgets'],
    ['name' => 'delete_budgets', 'display_name' => 'Delete Budgets', 'category' => 'Budget', 'description' => 'Delete budgets'],
    ['name' => 'approve_budgets', 'display_name' => 'Approve Budgets', 'category' => 'Budget', 'description' => 'Approve budgets for implementation'],
    ['name' => 'submit_budgets_for_approval', 'display_name' => 'Submit Budgets for Approval', 'category' => 'Budget', 'description' => 'Submit budgets for approval workflow'],
    ['name' => 'reject_budgets', 'display_name' => 'Reject Budgets', 'category' => 'Budget', 'description' => 'Reject submitted budgets'],
    ['name' => 'archive_budgets', 'display_name' => 'Archive Budgets', 'category' => 'Budget', 'description' => 'Archive completed or expired budgets'],
    ['name' => 'monitor_budgets', 'display_name' => 'Monitor Budgets', 'category' => 'Budget', 'description' => 'Monitor budget utilization and performance'],
    ['name' => 'create_budget_alerts', 'display_name' => 'Create Budget Alerts', 'category' => 'Budget', 'description' => 'Set up budget alerts and notifications'],
    ['name' => 'view_budget_analytics', 'display_name' => 'View Budget Analytics', 'category' => 'Budget', 'description' => 'Access advanced budget analytics and insights'],
    ['name' => 'export_budget_data', 'display_name' => 'Export Budget Data', 'category' => 'Budget', 'description' => 'Export budget data to various formats'],
    ['name' => 'manage_budget_categories', 'display_name' => 'Manage Budget Categories', 'category' => 'Budget', 'description' => 'Manage budget categories and classifications'],
    ['name' => 'manage_budget_departments', 'display_name' => 'Manage Budget Departments', 'category' => 'Budget', 'description' => 'Manage department-specific budgets'],
];

// Find the accountant role
$accountantRole = Role::where('name', 'accountant')->first();

if (!$accountantRole) {
    echo "Error: Accountant role not found.\n";
    exit(1);
}

echo "Found accountant role: {$accountantRole->display_name}\n";

// Create permissions and assign to accountant role
foreach ($budgetPermissions as $permissionData) {
    // Check if permission already exists
    $permission = Permission::where('name', $permissionData['name'])->first();

    if (!$permission) {
        // Create the permission
        $permission = Permission::create([
            'name' => $permissionData['name'],
            'display_name' => $permissionData['display_name'],
            'category' => $permissionData['category'],
            'description' => $permissionData['description'],
            'guard_name' => 'web'
        ]);
        echo "Created permission: {$permissionData['name']}\n";
    } else {
        echo "Permission already exists: {$permissionData['name']}\n";
    }

    // Check if accountant role already has this permission
    if (!$accountantRole->hasPermission($permission)) {
        // Assign permission to accountant role
        $accountantRole->permissions()->attach($permission->id);
        echo "  -> Assigned to accountant role\n";
    } else {
        echo "  -> Already assigned to accountant role\n";
    }
}

echo "\n✓ Budget permissions setup completed!\n";
echo "The accountant role now has access to all budget management features.\n";
echo "\nBudget pages should now be accessible:\n";
echo "  - http://localhost:8000/admin/budget/dashboard\n";
echo "  - http://localhost:8000/admin/budget/reports\n";
echo "  - http://localhost:8000/admin/budget/analytics\n";
echo "  - http://localhost:8000/admin/budget/alerts\n";

echo "\nDone!\n";
