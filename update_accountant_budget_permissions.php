<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Updating accountant role permissions for budget access...\n";

// Find the accountant role
$accountantRole = Role::where('name', 'accountant')->first();

if (!$accountantRole) {
    echo "Error: Accountant role not found.\n";
    exit(1);
}

echo "Found accountant role: {$accountantRole->display_name}\n";

// Get all budget-related permissions
$budgetPermissions = Permission::where('name', 'like', '%budget%')->get();

echo "Found " . $budgetPermissions->count() . " budget-related permissions:\n";
foreach ($budgetPermissions as $permission) {
    echo "  - {$permission->name}\n";
}

// Add budget permissions to accountant role
foreach ($budgetPermissions as $permission) {
    if (!$accountantRole->hasPermission($permission)) {
        $accountantRole->givePermission($permission);
        echo "Added permission: {$permission->name}\n";
    } else {
        echo "Already has permission: {$permission->name}\n";
    }
}

echo "\nAccountant role permissions updated successfully!\n";

// Verify the permissions were added
$accountantRole->load('permissions');
echo "\nAccountant role now has " . $accountantRole->permissions->count() . " permissions:\n";
foreach ($accountantRole->permissions as $permission) {
    echo "  - {$permission->name}\n";
}

echo "\nDone!\n";
