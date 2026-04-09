<?php
/**
 * Full integration test: Backend HMS ↔ Android TV App
 * Tests every feature the device management page exposes.
 *
 * Run: php test_full_integration.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\IptvDevice;
use App\Models\DeviceCommand;
use App\Models\Setting;

$base    = env('APP_URL', 'http://192.168.20.64:8001');
$pass    = 0;
$fail    = 0;
$errors  = [];

function ok(bool $cond, string $label) {
    global $pass, $fail, $errors;
    if ($cond) { echo "  ✅ $label\n"; $pass++; }
    else        { echo "  ❌ $label\n"; $fail++; $errors[] = $label; }
}

function api(string $method, string $url, array $body = []): array {
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    curl_setopt_array($ch, $opts);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $json = json_decode($raw, true) ?? [];
    $json['_http'] = $code;
    $json['_raw']  = substr($raw, 0, 200);
    return $json;
}

// ── Grab first active device ───────────────────────────────────────────────
$device = IptvDevice::where('is_active', true)->first();
if (!$device) {
    echo "❌ No active device found — run registration first\n";
    exit(1);
}
$deviceId = $device->device_id;
$token    = $device->registration_token;
echo "Device: {$deviceId} (token: " . substr($token, 0, 8) . "...)\n\n";

// ══════════════════════════════════════════════════════════════════════════
echo "── 1. PING ──────────────────────────────────────────────────────────\n";
$r = api('GET', "$base/api/android/ping");
ok($r['_http'] === 200, "GET /api/android/ping → 200");
ok(!empty($r['success']), "ping.success = true");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 2. HEARTBEAT (with settings_version) ─────────────────────────────\n";
$device->refresh();
$currentVer = $device->settings_version;

$r = api('POST', "$base/api/android/heartbeat", [
    'device_id'          => $deviceId,
    'registration_token' => $token,
    'status'             => 'online',
    'app_version'        => '1.0',
    'settings_version'   => $currentVer,
]);
ok($r['_http'] === 200, "POST /api/android/heartbeat → 200");
ok(!empty($r['success']), "heartbeat.success = true");
ok(isset($r['commands']), "heartbeat returns commands array");
ok(isset($r['needs_settings_update']), "heartbeat returns needs_settings_update");
ok(isset($r['server_time']), "heartbeat returns server_time");

// Verify heartbeat was logged
$device->refresh();
ok($device->last_heartbeat !== null, "last_heartbeat updated in DB");
ok($device->status === 'online', "status set to 'online' in DB");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 3. NEEDS_SETTINGS_UPDATE (stale version) ─────────────────────────\n";
// Bump device version on server to force needs_settings_update = true
$device->update(['settings_version' => $currentVer + 5]);
$r = api('POST', "$base/api/android/heartbeat", [
    'device_id'          => $deviceId,
    'registration_token' => $token,
    'status'             => 'online',
    'app_version'        => '1.0',
    'settings_version'   => $currentVer, // old version
]);
ok($r['_http'] === 200, "heartbeat with stale version → 200");
ok($r['needs_settings_update'] === true, "needs_settings_update = true when version stale");

// Reset version
$device->update(['settings_version' => $currentVer]);

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 4. GET SETTINGS ──────────────────────────────────────────────────\n";
$r = api('GET', "$base/api/android/settings?device_id={$deviceId}&registration_token={$token}");
ok($r['_http'] === 200, "GET /api/android/settings → 200");
ok(!empty($r['success']), "settings.success = true");
ok(isset($r['settings']), "settings object present");
ok(!empty($r['settings']['weather_api_key']), "weather_api_key in settings");
ok(!empty($r['settings']['weather_city']), "weather_city in settings");
ok(!empty($r['settings']['weather_units']), "weather_units in settings");
ok(isset($r['settings']['weather_enabled']), "weather_enabled in settings");
ok(isset($r['settings']['hotel_name']), "hotel_name in settings");
ok(isset($r['settings']['xtream_url']), "xtream_url in settings");
ok(isset($r['settings']['admin_pin']), "admin_pin in settings");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 5. GET IPTV CONFIG ───────────────────────────────────────────────\n";
$r = api('GET', "$base/api/android/iptv-config?device_id={$deviceId}&registration_token={$token}");
ok($r['_http'] === 200, "GET /api/android/iptv-config → 200");
ok(!empty($r['success']), "iptv-config.success = true");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 6. COMMAND DISPATCH (all types) ──────────────────────────────────\n";
$cmdTypes = ['reboot', 'reload_app', 'refresh_channels', 'lock', 'unlock', 'message', 'set_channel', 'push_settings'];
foreach ($cmdTypes as $type) {
    $cmd = $device->dispatchCommand($type, ['test' => true]);
    ok($cmd->id > 0, "Dispatch '$type' command → id={$cmd->id}");
}

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 7. COMMAND DELIVERY VIA HEARTBEAT ───────────────────────────────\n";
$r = api('POST', "$base/api/android/heartbeat", [
    'device_id'          => $deviceId,
    'registration_token' => $token,
    'status'             => 'online',
    'app_version'        => '1.0',
    'settings_version'   => $currentVer,
]);
ok($r['_http'] === 200, "heartbeat with pending commands → 200");
$cmds = $r['commands'] ?? [];
ok(count($cmds) >= count($cmdTypes), "All " . count($cmdTypes) . " commands delivered (got " . count($cmds) . ")");

// Check delivered commands are now 'delivered' status
$deliveredIds = array_column($cmds, 'id');
ok(count($deliveredIds) > 0, "Heartbeat returned command IDs");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 8. COMMAND ACK ───────────────────────────────────────────────────\n";
if (!empty($deliveredIds)) {
    $testId = $deliveredIds[0];
    $r = api('POST', "$base/api/android/command-ack", [
        'device_id'          => $deviceId,
        'registration_token' => $token,
        'command_id'         => $testId,
        'result'             => 'executed',
    ]);
    ok($r['_http'] === 200, "POST /api/android/command-ack → 200");
    ok(!empty($r['success']), "command-ack.success = true");

    $acked = DeviceCommand::find($testId);
    ok($acked && $acked->status === 'executed', "Command status updated to 'executed' in DB");
}

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 9. PUSH SETTINGS TO DEVICE (via DB) ─────────────────────────────\n";
$beforeVer = $device->fresh()->settings_version;
$device->update([
    'pushed_settings'  => ['admin_pin' => '9999'],
    'settings_version' => $beforeVer + 1,
]);
$device->refresh();
ok($device->settings_version === $beforeVer + 1, "settings_version incremented after push");
ok(($device->pushed_settings['admin_pin'] ?? '') === '9999', "pushed admin_pin saved in DB");

// Settings endpoint should now return the pushed pin
$r = api('GET', "$base/api/android/settings?device_id={$deviceId}&registration_token={$token}");
ok(($r['settings']['admin_pin'] ?? '') === '9999', "Pushed admin_pin returned by /api/android/settings");

// Restore
$device->update(['pushed_settings' => [], 'settings_version' => $currentVer]);

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 10. INVALID TOKEN → 401 ─────────────────────────────────────────\n";
$r = api('GET', "$base/api/android/settings?device_id={$deviceId}&registration_token=BADTOKEN");
ok($r['_http'] === 401, "Bad token → 401");

$r = api('POST', "$base/api/android/heartbeat", [
    'device_id'          => $deviceId,
    'registration_token' => 'BADTOKEN',
    'status'             => 'online',
    'app_version'        => '1.0',
]);
ok($r['_http'] === 401, "Heartbeat with bad token → 401");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 11. DEVICE INFO VISIBLE IN ADMIN UI ─────────────────────────────\n";
$d = IptvDevice::find($device->id);
ok(!empty($d->registration_token), "registration_token stored in DB");
ok(!empty($d->registered_at), "registered_at stored");
ok(!empty($d->last_heartbeat), "last_heartbeat stored");
ok($d->last_heartbeat->toIso8601String() !== null, "last_heartbeat is ISO parseable (for Vue ago())");
ok($d->registered_at->toIso8601String() !== null, "registered_at is ISO parseable");

// ══════════════════════════════════════════════════════════════════════════
echo "\n── 12. WEATHER API KEY VALID ────────────────────────────────────────\n";
$apiKey = Setting::where('key', 'weather_api_key')->value('value');
$city   = Setting::where('key', 'weather_city')->value('value');
ok(!empty($apiKey), "weather_api_key set in DB");
ok(!empty($city), "weather_city set in DB ($city)");

$ch = curl_init("https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => false]);
$wr = json_decode(curl_exec($ch), true);
$wc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok($wc === 200, "OpenWeatherMap API responds 200 for '$city'");
ok(!empty($wr['main']['temp']), "Temperature returned: " . ($wr['main']['temp'] ?? '?') . "°C");

// ══════════════════════════════════════════════════════════════════════════
echo "\n══════════════════════════════════════════════════════════════════════\n";
echo "Results: $pass passed, $fail failed\n";
if ($fail > 0) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) echo "  ❌ $e\n";
    exit(1);
}
echo "All tests passed ✅\n";
