<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$start = now()->startOfMonth()->toDateString();
$end   = now()->toDateString();

echo "Testing manager/reports/staff route logic..." . PHP_EOL;

$totalStaff  = (int) App\Models\User::count();
$newToday    = (int) App\Models\User::whereDate('created_at', now()->toDateString())->count();
$newMonth    = (int) App\Models\User::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->count();
echo "stats: total=$totalStaff, newToday=$newToday, newMonth=$newMonth" . PHP_EOL;

$byRole = App\Models\Role::withCount('users')->orderByDesc('users_count')->get()
    ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => $r->users_count]);
echo "byRole: " . $byRole->count() . " roles" . PHP_EOL;

$recentHires = App\Models\User::with('roles')
    ->orderByDesc('created_at')->limit(10)
    ->get(['id', 'first_name', 'last_name', 'email', 'created_at'])
    ->map(fn($u) => [
        'id' => $u->id, 'name' => trim($u->first_name . ' ' . $u->last_name),
        'email' => $u->email, 'roles' => $u->roles->pluck('name')->toArray(), 'created_at' => $u->created_at,
    ]);
echo "recentHires: " . $recentHires->count() . " users" . PHP_EOL;

$shiftStats = [
    'total_shifts'     => (int) App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->count(),
    'completed_shifts' => (int) App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->where('is_active', true)->count(),
];
echo "shiftStats: total=" . $shiftStats['total_shifts'] . " active=" . $shiftStats['completed_shifts'] . PHP_EOL;

echo "ALL OK - page should load successfully now." . PHP_EOL;
