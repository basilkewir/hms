<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$startOfMonth = now()->startOfMonth();
$endOfMonth   = now()->endOfMonth();
$startOfPrev  = now()->subMonth()->startOfMonth();
$endOfPrev    = now()->subMonth()->endOfMonth();

echo "Testing accountant/reports/revenue route...\n";

try {
    $totalRevenue = (float) App\Models\Payment::where('status','completed')->whereBetween('created_at',[$startOfMonth,$endOfMonth])->sum('amount');
    echo "totalRevenue: $" . number_format($totalRevenue,2) . "\n";
} catch (\Throwable $e) { echo "totalRevenue ERROR: " . $e->getMessage() . "\n"; }

try {
    $roomRevenue = (float) App\Models\Payment::where('status','completed')->whereHas('reservation')->whereBetween('created_at',[$startOfMonth,$endOfMonth])->sum('amount');
    echo "roomRevenue: $" . number_format($roomRevenue,2) . "\n";
} catch (\Throwable $e) { echo "roomRevenue ERROR: " . $e->getMessage() . "\n"; }

try {
    $posRevenue = (float) App\Models\Sale::whereBetween('created_at',[$startOfMonth,$endOfMonth])->sum('total_amount');
    echo "posRevenue: $" . number_format($posRevenue,2) . "\n";
} catch (\Throwable $e) { echo "posRevenue ERROR: " . $e->getMessage() . "\n"; }

try {
    $revenueByCategory = [
        ['category' => 'Room Revenue', 'amount' => $roomRevenue ?? 0],
        ['category' => 'POS Sales',    'amount' => $posRevenue ?? 0],
    ];
    echo "revenueByCategory: " . count($revenueByCategory) . " items\n";
} catch (\Throwable $e) { echo "revenueByCategory ERROR: " . $e->getMessage() . "\n"; }

try {
    $posByMethod = App\Models\Sale::select('payment_method', \Illuminate\Support\Facades\DB::raw('SUM(total_amount) as total'))
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->groupBy('payment_method')
        ->pluck('total', 'payment_method')
        ->toArray();
    $posSalesByCategory = collect($posByMethod)
        ->map(fn($v, $k) => ['category' => ucfirst(str_replace('_', ' ', $k ?: 'Other')), 'amount' => (float) $v])
        ->values()->toArray();
    echo "posSalesByCategory: " . count($posSalesByCategory) . " items\n";
    foreach ($posSalesByCategory as $p) echo "  - " . $p['category'] . ": $" . number_format($p['amount'],2) . "\n";
} catch (\Throwable $e) { echo "posSalesByCategory ERROR: " . $e->getMessage() . "\n"; }

echo "ALL OK\n";
