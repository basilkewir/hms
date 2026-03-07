<?php
require_once 'vendor/autoload.php';

use App\Models\WorkShift;

echo "Testing WorkShift database connection...\n";

try {
    $count = WorkShift::count();
    echo "Work shifts count: $count\n";
    echo "Database connection: OK\n";
    
    // Test with relationships
    $shifts = WorkShift::with(['employeeShifts'])->limit(2)->get();
    echo "Sample shifts with relationships: " . $shifts->count() . "\n";
    
    foreach ($shifts as $shift) {
        echo "Shift ID: {$shift->id}, Name: " . ($shift->name ?? 'N/A') . "\n";
        echo "  Employee count: " . ($shift->employeeShifts ? $shift->employeeShifts->count() : 0) . "\n";
    }
    
    echo "Database test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
