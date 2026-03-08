<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$start = now()->startOfMonth()->toDateString();
$end   = now()->toDateString();

try {
    $byRole = App\Models\Role::withCount('users')->orderByDesc('users_count')->get();
    echo 'byRole: ' . $byRole->count() . PHP_EOL;
    foreach ($byRole as $r) {
        echo '  - ' . $r->name . ': ' . $r->users_count . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'byRole ERROR: ' . $e->getMessage() . PHP_EOL;
}

try {
    $recentHires = App\Models\User::with('roles')->orderByDesc('created_at')->limit(10)->get(['id','name','email','created_at']);
    echo 'recentHires: ' . $recentHires->count() . PHP_EOL;
} catch (\Throwable $e) {
    echo 'recentHires ERROR: ' . $e->getMessage() . PHP_EOL;
}

try {
    if (class_exists('App\Models\EmployeeShift')) {
        $s = App\Models\EmployeeShift::whereBetween('shift_date', [$start, $end])->count();
        echo 'shiftStats: ' . $s . PHP_EOL;
    } else {
        echo 'EmployeeShift class not found' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'shiftStats ERROR: ' . $e->getMessage() . PHP_EOL;
}

echo 'ALL DONE' . PHP_EOL;
