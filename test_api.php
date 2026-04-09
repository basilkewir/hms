<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test 1: Ping
$request = Illuminate\Http\Request::create('/api/android/ping', 'GET');
$response = $kernel->handle($request);
echo "=== PING ===" . PHP_EOL;
echo $response->getContent() . PHP_EOL . PHP_EOL;

// Test 2: Hotel info
$request2 = Illuminate\Http\Request::create('/api/android/hotel-info', 'GET');
$response2 = $kernel->handle($request2);
echo "=== HOTEL INFO ===" . PHP_EOL;
echo $response2->getContent() . PHP_EOL . PHP_EOL;

// Test 3: Settings (needs valid device_id + token — just confirm 401 shape)
$request3 = Illuminate\Http\Request::create('/api/android/settings?device_id=test&registration_token=invalid', 'GET');
$response3 = $kernel->handle($request3);
echo "=== SETTINGS (invalid token — expect 401) ===" . PHP_EOL;
echo "HTTP " . $response3->getStatusCode() . ": " . $response3->getContent() . PHP_EOL . PHP_EOL;

// Test 4: Check DB for IPTV settings
echo "=== IPTV SETTINGS IN DB ===" . PHP_EOL;
$db = $app->make('db');
$keys = ['xtream_url','weather_api_key','weather_city','weather_units','weather_enabled','hotel_welcome_message','hotel_primary_color','admin_pin'];
$rows = $db->table('settings')->whereIn('key', $keys)->get(['key','value','group']);
foreach ($rows as $row) {
    $val = strlen($row->value) > 40 ? substr($row->value, 0, 40) . '…' : $row->value;
    echo "  [{$row->group}] {$row->key} = " . ($row->key === 'weather_api_key' ? '***hidden***' : $val) . PHP_EOL;
}
