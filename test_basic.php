<?php
require_once 'vendor/autoload.php';

echo "Testing WorkShift model...\n";

try {
    // Test basic model instantiation
    $workShift = new \App\Models\WorkShift();
    echo "WorkShift model instantiated successfully\n";
    
    // Test database connection
    $connection = $workShift->getConnection();
    echo "Database connection: " . ($connection ? "OK" : "NULL") . "\n";
    
    // Test table access
    if ($connection) {
        $table = $workShift->getTable();
        echo "Table name: " . $table . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
