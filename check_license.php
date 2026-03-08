<?php

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix: restore license status to 'active' since it was wrongly set to 'inactive'
// by the broken isSystemLicensed() that called GET /info without a Bearer token
$license = App\Models\License::whereNotNull('license_key')->latest('id')->first();
if ($license) {
    echo 'ID: ' . $license->id . PHP_EOL;
    echo 'Key: ' . $license->license_key . PHP_EOL;
    echo 'Status (before): ' . $license->status . PHP_EOL;
    $data = $license->license_data;
    echo 'Data status: ' . ($data['status'] ?? 'NONE') . PHP_EOL;
    echo 'Expires at: ' . ($data['expires_at'] ?? 'NONE') . PHP_EOL;
    echo 'Has token: ' . (isset($data['token']) ? 'YES' : 'NO') . PHP_EOL;

    // Restore to active
    if ($license->status !== 'active') {
        $license->update(['status' => 'active']);
        echo 'Status updated to: active' . PHP_EOL;
    } else {
        echo 'Status is already active' . PHP_EOL;
    }
} else {
    echo 'No license found' . PHP_EOL;
}
