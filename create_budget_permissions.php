<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Creating budget permissions and updating accountant role...\n";

// Get budget permissions from the default permissions
$defaultPermissions = Permission::getDefaultPermissions();
$budgetPermissions = [];

foreach ($defaultPermissions as $name => $permissionData) {
    if ($permissionData['category'] === 'Budget') {
        $budgetPermissions[$name] = $permissionData;
    }
}

echo "Found " . count($budgetPermissions) . " budget permissions:\n";

// Create permissions if they don't exist
foreach ($budgetPermissions as $name => $permissionData) {
    $permission = Permission::firstOrCreate([
        'name' => $name,
        'display_name' => $permissionData['display_name'],
        'category' => $permissionData['category'],
        'description' => $permissionData['description'],
        'guard_name' => 'web'
    ]);

    if ($permission->wasRecentlyCreated) {
        echo "Created permission: {$name}\n";
    } else {
        echo "Permission already exists: {$name}\n";
    }
}

// Find the accountant role
$accountantRole = Role::where('name', 'accountant')->first();

if (!$accountantRole) {
    echo "Error: Accountant role not found.\n";
    exit(1);
}

echo "\nFound accountant role: {$accountantRole->display_name}\n";

// Add all budget permissions to accountant role
foreach ($budgetPermissions as $name => $permissionData) {
    $permission = Permission::where('name', $name)->first();
    if ($permission && !$accountantRole->hasPermission($permission)) {
        $accountantRole->givePermission($permission);
        echo "Added permission to accountant: {$name}\n";
    } else if ($permission) {
        echo "Accountant already has permission: {$name}\n";
    }
}

echo "\nBudget permissions setup completed!\n";

// Verify the permissions were added
$accountantRole->load('permissions');
$budgetPerms = $accountantRole->permissions->filter(function($perm) {
    return $perm->category === 'Budget';
});

echo "\nAccountant role now has {$budgetPerms->count()} budget permissions:\n";
foreach ($budgetPerms as $permission) {
    echo "  - {$permission->name} ({$permission->display_name})\n";
}

echo "\nDone!\n";
