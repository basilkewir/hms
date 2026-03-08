<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$license = App\Models\License::whereNotNull('license_key')->latest('id')->first();
if (!$license) {
    echo "No license record found." . PHP_EOL;
    exit(1);
}

$ld = is_array($license->license_data) ? $license->license_data : [];
$current = $ld['device_id'] ?? null;
echo "Current device_id type: " . gettype($current) . " value: " . var_export($current, true) . PHP_EOL;

// Fix: overwrite with the correct string device fingerprint (not the kewirdev integer row-ID)
$ld['device_id'] = 'device-2b47fdc6e7adebca';

$license->update(['license_data' => $ld]);
echo "Fixed device_id: " . $ld['device_id'] . PHP_EOL;
echo "Done." . PHP_EOL;
