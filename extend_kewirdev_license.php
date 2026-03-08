<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$key = 'ECF5FF1F-7BC3E75B-B8277390-510EF914';
$newExpiry = (new DateTime('+2 years'))->format('Y-m-d H:i:s');

$rows = App\Models\License::where('license_key', $key)->update([
    'expires_at' => $newExpiry,
    'status' => 'active',
]);

echo 'Rows updated: ' . $rows . PHP_EOL;

$l = App\Models\License::where('license_key', $key)->first();
echo 'New expiry: ' . $l->expires_at . PHP_EOL;
echo 'Status: ' . $l->status . PHP_EOL;
echo 'License type: ' . $l->license_type . PHP_EOL;
