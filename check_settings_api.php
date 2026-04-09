<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\IptvDevice;
use App\Models\Setting;

// 1. Get first active device
$device = IptvDevice::where('is_active', true)->first();
if (!$device) {
    echo "❌ No active registered device found\n";
    exit(1);
}

echo "✅ Device found:\n";
echo "   device_id         : {$device->device_id}\n";
echo "   registration_token: {$device->registration_token}\n";
echo "   settings_version  : {$device->settings_version}\n\n";

// 2. Call the settings endpoint directly via HTTP
$deviceId = $device->device_id;
$token    = $device->registration_token;
$baseUrl  = env('APP_URL', 'http://192.168.20.64:8001');
$url      = "{$baseUrl}/api/android/settings?device_id={$deviceId}&registration_token={$token}";

echo "Testing: GET {$url}\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);
$raw  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo "HTTP {$code}\n";
if ($err) echo "cURL error: {$err}\n";

$json = json_decode($raw, true);
if (!$json) {
    echo "❌ Invalid JSON response:\n{$raw}\n";
    exit(1);
}

echo "success          : " . ($json['success'] ? 'true' : 'false') . "\n";
echo "settings_version : " . ($json['settings_version'] ?? 'N/A') . "\n\n";

$s = $json['settings'] ?? [];
echo "=== Weather settings in response ===\n";
echo "  weather_enabled : " . json_encode($s['weather_enabled'] ?? null) . "\n";
echo "  weather_api_key : " . ($s['weather_api_key'] ?? '(empty)') . "\n";
echo "  weather_city    : " . ($s['weather_city'] ?? '(empty)') . "\n";
echo "  weather_units   : " . ($s['weather_units'] ?? '(empty)') . "\n\n";

echo "=== Xtream settings in response ===\n";
echo "  xtream_url      : " . ($s['xtream_url'] ?? '(empty)') . "\n";
echo "  xtream_username : " . ($s['xtream_username'] ?? '(empty)') . "\n\n";

// 3. Also check DB directly
echo "=== DB values ===\n";
$keys = ['weather_api_key', 'weather_city', 'weather_units', 'weather_enabled'];
foreach ($keys as $k) {
    $val = Setting::where('key', $k)->value('value');
    echo "  {$k}: " . ($val ?? '(null)') . "\n";
}
