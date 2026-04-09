<?php
// ============================================================
// HMS API Full Communication Test
// Run: php test_api_full.php
// ============================================================
chdir(__DIR__);
define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

// Create the application
$app = require_once __DIR__ . '/bootstrap/app.php';

/** @var \Illuminate\Foundation\Http\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$pass = 0;
$fail = 0;

function test($name, $condition, $detail = '') {
    global $pass, $fail;
    if ($condition) {
        echo "  ✅ PASS: $name" . ($detail ? " — $detail" : "") . PHP_EOL;
        $pass++;
    } else {
        echo "  ❌ FAIL: $name" . ($detail ? " — $detail" : "") . PHP_EOL;
        $fail++;
    }
}

function apiGet($kernel, $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    return ['status' => $response->getStatusCode(), 'body' => json_decode($response->getContent(), true)];
}

function apiPost($kernel, $uri, $payload) {
    $request = Illuminate\Http\Request::create($uri, 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT'  => 'application/json',
    ], json_encode($payload));
    $response = $kernel->handle($request);
    return ['status' => $response->getStatusCode(), 'body' => json_decode($response->getContent(), true)];
}

// ── 1. Ping ────────────────────────────────────────────────────────────
echo PHP_EOL . "=== 1. Ping / Connectivity ===" . PHP_EOL;
$r = apiGet($kernel, '/api/android/ping');
test('ping returns 200',        $r['status'] === 200);
test('ping success=true',       $r['body']['success'] === true);
test('ping has server_time',    !empty($r['body']['server_time']));

// ── 2. Hotel Info (public) ────────────────────────────────────────────
echo PHP_EOL . "=== 2. Hotel Info (public endpoint) ===" . PHP_EOL;
$r = apiGet($kernel, '/api/android/hotel-info');
test('hotel-info returns 200',  $r['status'] === 200);
test('hotel-info success=true', $r['body']['success'] === true);
test('hotel-info has name',     isset($r['body']['data']['name']));
test('hotel-info has server_time', !empty($r['body']['data']['server_time']));

// ── 3. Invalid token → 401 ────────────────────────────────────────────
echo PHP_EOL . "=== 3. Auth Rejection (invalid token) ===" . PHP_EOL;
$r = apiGet($kernel, '/api/android/settings?device_id=fake&registration_token=invalid');
test('invalid token → 401',     $r['status'] === 401);
test('invalid token success=false', $r['body']['success'] === false);

// ── 4. Device Registration ────────────────────────────────────────────
echo PHP_EOL . "=== 4. Device Registration ===" . PHP_EOL;
// Clean up any pre-existing test device
\App\Models\IptvDevice::where('device_id', 'phptest-001')->forceDelete();

$r = apiPost($kernel, '/api/android/register', [
    'device_id'    => 'phptest-001',
    'device_name'  => 'PHP Test TV 101',
    'device_type'  => 'android_tv',
    'mac_address'  => 'AA:BB:CC:00:11:22',
    'app_version'  => '1.0-test',
]);
test('register returns 200',      $r['status'] === 200,       'HTTP ' . $r['status']);
test('register success=true',     $r['body']['success'] === true);
test('register returns token',    !empty($r['body']['registration_token']));
test('register returns settings', isset($r['body']['settings']));

$token = $r['body']['registration_token'] ?? null;

// ── 5. Settings Pull ──────────────────────────────────────────────────
echo PHP_EOL . "=== 5. Settings Pull ===" . PHP_EOL;
$r = apiGet($kernel, '/api/android/settings?device_id=phptest-001&registration_token=' . urlencode($token));
test('settings returns 200',              $r['status'] === 200);
test('settings success=true',             $r['body']['success'] === true);
test('settings has xtream_url',           array_key_exists('xtream_url',            $r['body']['settings']));
test('settings has hotel_name',           array_key_exists('hotel_name',            $r['body']['settings']));
test('settings has hotel_welcome_message', array_key_exists('hotel_welcome_message', $r['body']['settings']));
test('settings has hotel_primary_color',  array_key_exists('hotel_primary_color',   $r['body']['settings']));
test('settings has weather_enabled',      array_key_exists('weather_enabled',       $r['body']['settings']));
test('settings has weather_city',         array_key_exists('weather_city',          $r['body']['settings']));
test('settings has weather_api_key',      array_key_exists('weather_api_key',       $r['body']['settings']));
test('settings has weather_units',        array_key_exists('weather_units',         $r['body']['settings']));
test('settings has ui_theme',             array_key_exists('ui_theme',              $r['body']['settings']));
test('settings has show_epg',             array_key_exists('show_epg',             $r['body']['settings']));
test('settings has show_clock',           array_key_exists('show_clock',            $r['body']['settings']));
test('settings has admin_pin',            array_key_exists('admin_pin',             $r['body']['settings']));
test('settings has auto_launch_seconds',  array_key_exists('auto_launch_seconds',   $r['body']['settings']));
test('weather_units default is metric',   ($r['body']['settings']['weather_units'] ?? '') === 'metric');
test('hotel_primary_color default',       ($r['body']['settings']['hotel_primary_color'] ?? '') === '#FFD700');
test('admin_pin default',                 ($r['body']['settings']['admin_pin'] ?? '') === '1234');

// ── 6. Heartbeat ──────────────────────────────────────────────────────
echo PHP_EOL . "=== 6. Heartbeat ===" . PHP_EOL;
$r = apiPost($kernel, '/api/android/heartbeat', [
    'device_id'          => 'phptest-001',
    'registration_token' => $token,
    'status'             => 'online',
    'app_version'        => '1.0-test',
    'settings_version'   => 0,
]);
test('heartbeat returns 200',       $r['status'] === 200, 'HTTP ' . $r['status']);
test('heartbeat success=true',      $r['body']['success'] === true);
test('heartbeat has commands array', isset($r['body']['commands']));
test('heartbeat has server_time',   !empty($r['body']['server_time']));

// ── 7. DB: IPTV group verification ────────────────────────────────────
echo PHP_EOL . "=== 7. DB: IPTV settings group ===" . PHP_EOL;
$iptvSettings = \App\Models\Setting::where('group', 'iptv')->pluck('value', 'key')->toArray();
$requiredKeys = [
    'xtream_url', 'xtream_username', 'xtream_password', 'xtream_use_https',
    'hotel_welcome_message', 'hotel_primary_color',
    'weather_api_key', 'weather_city', 'weather_units', 'weather_enabled',
    'iptv_ui_theme', 'iptv_show_epg', 'iptv_show_clock', 'iptv_show_room_number',
    'iptv_auto_launch_seconds', 'iptv_enable_vod', 'iptv_enable_series', 'iptv_enable_radio',
    'iptv_parental_pin', 'admin_pin',
];
foreach ($requiredKeys as $key) {
    test("DB has iptv.$key", array_key_exists($key, $iptvSettings));
}
echo "  Total IPTV keys in DB: " . count($iptvSettings) . PHP_EOL;

// ── Cleanup ───────────────────────────────────────────────────────────
\App\Models\IptvDevice::where('device_id', 'phptest-001')->forceDelete();

// ── Summary ────────────────────────────────────────────────────────────
echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "  PASSED: $pass" . PHP_EOL;
echo "  FAILED: $fail" . PHP_EOL;
if ($fail === 0) {
    echo PHP_EOL . "  🎉 ALL TESTS PASSED — HMS API communication fully verified!" . PHP_EOL;
} else {
    echo PHP_EOL . "  ⚠️  $fail test(s) failed — review above" . PHP_EOL;
}
echo PHP_EOL;
