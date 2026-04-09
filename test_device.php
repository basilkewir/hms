<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\IptvDevice::first();
if ($d) {
    echo "device_id: " . $d->device_id . "\n";
    echo "token: " . $d->registration_token . "\n";
    echo "settings_version: " . $d->settings_version . "\n";
    echo "pushed_settings: " . json_encode($d->pushed_settings) . "\n";
    echo "is_active: " . ($d->is_active ? 'yes' : 'no') . "\n";
} else {
    echo "No devices registered\n";
}

// Also check settings table for weather/xtream keys
$keys = ['weather_api_key','weather_city','weather_units','weather_enabled','xtream_url','xtream_username','xtream_password'];
$rows = App\Models\Setting::whereIn('key', $keys)->pluck('value','key')->toArray();
echo "\n--- Settings table ---\n";
foreach ($rows as $k => $v) {
    echo "$k: $v\n";
}
