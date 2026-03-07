<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

// Find or create the accountant role
$accountantRole = Role::firstOrCreate(
    ['name' => 'accountant'],
    [
        'display_name' => 'Accountant',
        'description' => 'Financial management and reporting',
        'is_active' => true
    ]
);

// Accountant permissions
$accountantPermissions = [
    'manage_financials',
    'view_reports',
    'manage_expenses',
    'manage_payroll',
    'view_reservations',
    'process_payments',
    'view_pos_reports',
    'manage_purchases',
    'process_refunds',
    'view_refunds',
    'manage_refunds',
    'view_budgets',
    'approve_budgets',
    'monitor_budgets',
    'view_budget_analytics',
    'export_budget_data',
    'view_budget_reports'
];

echo "Updating accountant role permissions...\n";

foreach ($accountantPermissions as $permissionName) {
    $permission = Permission::firstOrCreate(
        ['name' => $permissionName],
        [
            'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
            'category' => 'financial',
            'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
        ]
    );

    // Check if permission is already assigned
    if (!$accountantRole->hasPermission($permission)) {
        $accountantRole->givePermission($permission);
        echo "Assigned permission '{$permissionName}' to accountant role\n";
    } else {
        echo "Permission '{$permissionName}' already assigned to accountant role\n";
    }
}

echo "Accountant role permissions updated successfully!\n";
