<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the application so service providers are registered
$app->boot();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
Facade::setFacadeApplication($app);

echo "=== TEST: Device Registration & Full Settings Payload ===" . PHP_EOL . PHP_EOL;

// Register a test device
$regBody = json_encode([
    'device_id'    => 'test-device-001',
    'device_name'  => 'Test Room 101',
    'device_type'  => 'android_tv',
    'mac_address'  => 'AA:BB:CC:DD:EE:01',
]);
$request = Illuminate\Http\Request::create('/api/android/register', 'POST', [], [], [], [
    'CONTENT_TYPE' => 'application/json',
    'HTTP_ACCEPT'  => 'application/json',
], $regBody);
$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);

if (!($data['success'] ?? false)) {
    echo "REGISTRATION FAILED: " . $response->getContent() . PHP_EOL;
    exit(1);
}

$token = $data['registration_token'];
echo "✅ Device registered OK" . PHP_EOL;
echo "   Token: " . substr($token, 0, 16) . "..." . PHP_EOL . PHP_EOL;

// Fetch settings
$request2 = Illuminate\Http\Request::create(
    '/api/android/settings?device_id=test-device-001&registration_token=' . urlencode($token),
    'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']
);
$response2 = $kernel->handle($request2);
$settingsData = json_decode($response2->getContent(), true);

if (!($settingsData['success'] ?? false)) {
    echo "SETTINGS FETCH FAILED: " . $response2->getContent() . PHP_EOL;
    exit(1);
}

echo "✅ Settings fetch OK (version " . $settingsData['settings_version'] . ")" . PHP_EOL;
$s = $settingsData['settings'];
echo PHP_EOL . "--- Xtream ---" . PHP_EOL;
echo "  xtream_url:      " . ($s['xtream_url'] ?: '[empty]') . PHP_EOL;
echo "  xtream_username: " . ($s['xtream_username'] ?: '[empty]') . PHP_EOL;
echo "  xtream_use_https: " . json_encode($s['xtream_use_https']) . PHP_EOL;
echo PHP_EOL . "--- Hotel Branding ---" . PHP_EOL;
echo "  hotel_name:            " . ($s['hotel_name'] ?: '[empty]') . PHP_EOL;
echo "  hotel_welcome_message: " . ($s['hotel_welcome_message'] ?: '[empty]') . PHP_EOL;
echo "  hotel_primary_color:   " . ($s['hotel_primary_color'] ?: '[empty]') . PHP_EOL;
echo PHP_EOL . "--- Weather ---" . PHP_EOL;
echo "  weather_enabled: " . json_encode($s['weather_enabled']) . PHP_EOL;
echo "  weather_city:    " . ($s['weather_city'] ?: '[not set]') . PHP_EOL;
echo "  weather_units:   " . ($s['weather_units'] ?: 'metric') . PHP_EOL;
echo "  weather_api_key: " . (empty($s['weather_api_key']) ? '[not set]' : '***hidden***') . PHP_EOL;
echo PHP_EOL . "--- TV Behaviour ---" . PHP_EOL;
echo "  ui_theme:             " . ($s['ui_theme'] ?? 'dark') . PHP_EOL;
echo "  show_epg:             " . json_encode($s['show_epg'] ?? true) . PHP_EOL;
echo "  show_clock:           " . json_encode($s['show_clock'] ?? true) . PHP_EOL;
echo "  auto_launch_seconds:  " . ($s['auto_launch_seconds'] ?? 15) . PHP_EOL;
echo "  enable_vod:           " . json_encode($s['enable_vod'] ?? true) . PHP_EOL;
echo "  enable_series:        " . json_encode($s['enable_series'] ?? true) . PHP_EOL;
echo "  enable_radio:         " . json_encode($s['enable_radio'] ?? true) . PHP_EOL;
echo PHP_EOL . "--- Security ---" . PHP_EOL;
echo "  admin_pin:    " . ($s['admin_pin'] ?? '[not set]') . PHP_EOL;
echo "  parental_pin: " . (empty($s['parental_pin']) ? '[disabled]' : '***') . PHP_EOL;
echo "  room_number:  " . ($s['room_number'] ?: '[not assigned]') . PHP_EOL;

// Cleanup test device
DB::table('iptv_devices')->where('device_id', 'test-device-001')->delete();
echo PHP_EOL . "✅ Test device cleaned up" . PHP_EOL;
echo PHP_EOL . "=== ALL TESTS PASSED ===" . PHP_EOL;
