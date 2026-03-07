<?php
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Testing WorkShift database connection...\n";

try {
    // Test database connection
    $connection = \Illuminate\Support\Facades\DB::connection();
    echo "Database connection: " . ($connection ? "OK" : "NULL") . "\n";
    
    // Test if work_shifts table exists
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('work_shifts');
    echo "work_shifts table exists: " . ($tableExists ? "YES" : "NO") . "\n";
    
    if ($tableExists) {
        // Test fetching work shifts
        $shifts = \Illuminate\Support\Facades\DB::table('work_shifts')->get();
        echo "Total work shifts in database: " . $shifts->count() . "\n";
        
        // Show sample data
        foreach ($shifts as $shift) {
            echo "Shift ID: {$shift->id}, Name: " . ($shift->name ?? 'N/A') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
