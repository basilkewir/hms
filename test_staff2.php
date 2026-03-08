<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$start = now()->startOfMonth()->toDateString();
$end   = now()->toDateString();

try {
    $recentHires = App\Models\User::with('roles')
        ->orderByDesc('created_at')
        ->limit(10)
        ->get(['id', 'first_name', 'last_name', 'email', 'created_at'])
        ->map(fn($u) => [
            'id'         => $u->id,
            'name'       => trim($u->first_name . ' ' . $u->last_name),
            'email'      => $u->email,
            'roles'      => $u->roles->pluck('name')->toArray(),
            'created_at' => $u->created_at,
        ]);
    echo 'recentHires OK: ' . $recentHires->count() . PHP_EOL;
    foreach ($recentHires as $h) {
        echo '  - ' . $h['name'] . ' (' . implode(', ', $h['roles']) . ')' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'recentHires ERROR: ' . $e->getMessage() . PHP_EOL;
}

try {
    $shiftStats = [
        'total_shifts'     => (int) App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->count(),
        'completed_shifts' => (int) App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->where('status', 'completed')->count(),
    ];
    echo 'shiftStats OK: total=' . $shiftStats['total_shifts'] . ' completed=' . $shiftStats['completed_shifts'] . PHP_EOL;
} catch (\Throwable $e) {
    echo 'shiftStats ERROR: ' . $e->getMessage() . PHP_EOL;
}

echo 'ALL OK' . PHP_EOL;
