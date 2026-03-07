<?php
/**
 * Budget System Verification Script
 * Run this to verify the budget system is properly configured
 */

echo "=================================================\n";
echo "   BUDGET SYSTEM VERIFICATION SCRIPT\n";
echo "=================================================\n\n";

$results = [
    'permissions' => false,
    'admin_role' => false,
    'accountant_role' => false,
    'manager_role' => false,
    'routes' => false,
    'controller' => false,
    'views' => false,
];

// Check permissions
echo "1. Checking budget permissions...\n";
$permissions = \App\Models\Permission::where('name', 'like', '%budget%')->get();
if ($permissions->count() > 0) {
    echo "   ✓ Found {$permissions->count()} budget permissions:\n";
    foreach ($permissions as $p) {
        echo "     - {$p->name}\n";
    }
    $results['permissions'] = true;
} else {
    echo "   ✗ No budget permissions found!\n";
}

// Check admin role has budget permissions
echo "\n2. Checking admin role permissions...\n";
$adminRole = \App\Models\Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminBudgetPerms = $adminRole->permissions()->where('name', 'like', '%budget%')->count();
    echo "   ✓ Admin has {$adminBudgetPerms} budget permissions\n";
    $results['admin_role'] = $adminBudgetPerms > 0;
}

// Check accountant role has budget permissions
echo "\n3. Checking accountant role permissions...\n";
$accountantRole = \App\Models\Role::where('name', 'accountant')->first();
if ($accountantRole) {
    $accountantBudgetPerms = $accountantRole->permissions()->where('name', 'like', '%budget%')->count();
    echo "   ✓ Accountant has {$accountantBudgetPerms} budget permissions\n";
    $results['accountant_role'] = $accountantBudgetPerms > 0;
}

// Check manager role has budget permissions
echo "\n4. Checking manager role permissions...\n";
$managerRole = \App\Models\Role::where('name', 'manager')->first();
if ($managerRole) {
    $managerBudgetPerms = $managerRole->permissions()->where('name', 'like', '%budget%')->count();
    echo "   ✓ Manager has {$managerBudgetPerms} budget permissions\n";
    $results['manager_role'] = $managerBudgetPerms > 0;
}

// Check routes
echo "\n5. Checking budget routes...\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$budgetRoutes = [];
foreach ($routes as $route) {
    if (strpos($route->uri(), 'admin/budget') !== false) {
        $budgetRoutes[] = $route->uri();
    }
}
if (count($budgetRoutes) > 0) {
    echo "   ✓ Found " . count($budgetRoutes) . " budget routes:\n";
    foreach (array_slice($budgetRoutes, 0, 10) as $route) {
        echo "     - {$route}\n";
    }
    if (count($budgetRoutes) > 10) {
        echo "     ... and " . (count($budgetRoutes) - 10) . " more\n";
    }
    $results['routes'] = true;
} else {
    echo "   ✗ No budget routes found!\n";
}

// Check controller
echo "\n6. Checking BudgetController...\n";
$controllerPath = app_path('Http/Controllers/Admin/BudgetController.php');
if (file_exists($controllerPath)) {
    echo "   ✓ BudgetController exists\n";

    $controllerMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'dashboard', 'reports'];
    $foundMethods = [];
    $content = file_get_contents($controllerPath);
    foreach ($controllerMethods as $method) {
        if (strpos($content, "function {$method}(") !== false) {
            $foundMethods[] = $method;
        }
    }
    echo "   ✓ Found " . count($foundMethods) . " methods: " . implode(', ', $foundMethods) . "\n";
    $results['controller'] = count($foundMethods) > 5;
} else {
    echo "   ✗ BudgetController not found!\n";
}

// Check views
echo "\n7. Checking budget views...\n";
$viewPath = resource_path('js/Pages/Admin/Budgets');
$views = ['Index.vue', 'Create.vue', 'Show.vue', 'Edit.vue', 'Dashboard.vue', 'Reports.vue'];
$foundViews = [];
foreach ($views as $view) {
    if (file_exists($viewPath . '/' . $view)) {
        $foundViews[] = $view;
    }
}
if (count($foundViews) > 0) {
    echo "   ✓ Found " . count($foundViews) . " view files:\n";
    foreach ($foundViews as $view) {
        echo "     - {$view}\n";
    }
    $results['views'] = count($foundViews) >= 4;
} else {
    echo "   ✗ No budget views found!\n";
}

// Summary
echo "\n=================================================\n";
echo "   VERIFICATION SUMMARY\n";
echo "=================================================\n";

$passed = array_sum($results);
$total = count($results);

echo "Passed: {$passed}/{$total}\n\n";

if ($passed === $total) {
    echo "🎉 ALL CHECKS PASSED! Budget system is properly configured.\n\n";
    echo "You can now:\n";
    echo "1. Access /admin/budget to view the budget index\n";
    echo "2. Access /admin/budget/dashboard for the budget dashboard\n";
    echo "3. Access /admin/budget/reports for budget reports\n";
    echo "4. Create, edit, and manage budgets with proper permissions\n";
} else {
    echo "⚠️  Some checks failed. Please review the results above.\n";
    echo "\nTo fix missing permissions, run:\n";
    echo "  php artisan db:seed --class=BudgetPermissionsSeeder\n";
}

echo "\n=================================================\n";
