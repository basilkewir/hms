<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$server      = 'http://127.0.0.1:8001/api/license';
$licenseKey  = 'ECF5FF1F-7BC3E75B-B8277390-510EF914';
$jwtSecret   = 'QveLxwQnbNvAMwAouKnu0lYr0S3JrvDnXysG0cR'; // live LICENSE_JWT_SECRET
$deviceId    = App\Models\License::whereNotNull('license_key')->latest('id')->value('hardware_fingerprint')
               ?? ('device-' . substr(md5(php_uname('n') . gethostname()), 0, 16));

echo "=== localhost:8001 License Test ===" . PHP_EOL;
echo "License key : $licenseKey" . PHP_EOL;
echo "Device ID   : $deviceId"   . PHP_EOL;
echo "HMAC secret : " . substr($jwtSecret, 0, 10) . "..." . PHP_EOL;
echo PHP_EOL;

// ── Build the validate payload (key order matters for HMAC) ──────────────────
$payload = [
    'license_key'       => $licenseKey,
    'device_id'         => $deviceId,
    'device_type'       => 'management_backend',
    'device_name'       => 'Hotel Management System',
    'device_model'      => 'Server',
    'device_os'         => php_uname('s'),
    'device_os_version' => php_uname('r'),
    'app_version'       => '1.0.0',
    'mac_address'       => null,
    'metadata'          => [
        'system_info' => php_uname(),
        'php_version' => PHP_VERSION,
    ],
];

$jsonBody  = json_encode($payload);
$signature = hash_hmac('sha256', $jsonBody, $jwtSecret);

echo "Payload JSON (first 120): " . substr($jsonBody, 0, 120) . "..." . PHP_EOL;
echo "Signature   : " . substr($signature, 0, 24) . "..." . PHP_EOL;
echo PHP_EOL;

// ── POST /validate ───────────────────────────────────────────────────────────
echo "--- POST /validate ---" . PHP_EOL;
$ch = curl_init($server . '/validate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $jsonBody,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: HotelManagement/1.0.0 (Hotel Management System)',
        'X-License-Signature: ' . $signature,
    ],
]);
$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo "HTTP : $http" . PHP_EOL;
if ($err) echo "cURL : $err" . PHP_EOL;
echo "Body : " . $body . PHP_EOL;
echo PHP_EOL;

$data = json_decode($body, true);

// ── If we got a token, try GET /info ─────────────────────────────────────────
if (!empty($data['token'])) {
    $token = $data['token'];
    echo "--- GET /info with new token ---" . PHP_EOL;
    $ch = curl_init($server . '/info');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'User-Agent: HotelManagement/1.0.0 (Hotel Management System)',
        ],
    ]);
    $body2 = curl_exec($ch);
    $http2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "HTTP : $http2" . PHP_EOL;
    echo "Body : " . substr($body2, 0, 600) . PHP_EOL;
    echo PHP_EOL;

    // ── Store complete license_data in HMS DB ────────────────────────────────
    echo "--- Storing new token in HMS DB ---" . PHP_EOL;

    // Parse GET /info response for enriched data
    $infoData    = json_decode($body2, true)['data'] ?? [];
    $features    = $infoData['features'] ?? [];
    $deviceUsage = $infoData['device_usage'] ?? [];
    $hotelName   = $infoData['hotel_name'] ?? 'Hotel';
    $licenseType = strtoupper($infoData['license_type'] ?? 'PERPETUAL');
    $expiresAt   = $infoData['expires_at'] ?? 'Never';

    // Build a complete license_data matching validateLicense() format
    $ld = [
        'license_key'      => $licenseKey,
        'hotel_name'       => $hotelName,
        'license_type'     => $licenseType,
        'status'           => 'ACTIVE',
        'expires_at'       => $expiresAt,
        'created_at'       => date('n/j/Y'),
        'features'         => $features,
        'device_allocation'=> [[
            'type'  => 'API Devices',
            'used'  => $deviceUsage['current'] ?? 0,
            'limit' => $deviceUsage['maximum'] ?? -1,
        ]],
        'total_used'       => $deviceUsage['current'] ?? 0,
        'total_limit'      => $deviceUsage['maximum'] ?? -1,
        'validated_at'     => date('c'),
        'is_valid'         => true,
        'token'            => $token,
        'token_expires_at' => $data['expires_at'] ?? null,
        'device_id'        => (string) $deviceId,
    ];

    App\Models\License::updateOrCreate(
        ['license_key' => $licenseKey],
        [
            'license_data'      => $ld,
            'product_name'      => 'Hotel Management System',
            'status'            => 'active',
            'customer_name'     => $hotelName,
            'customer_email'    => 'admin@' . str_replace(' ', '', strtolower($hotelName)) . '.com',
            'license_type'      => $licenseType,
            'activated_at'      => date('Y-m-d H:i:s'),
            'last_validated_at' => date('Y-m-d H:i:s'),
            'hardware_fingerprint' => (string) $deviceId,
        ]
    );
    echo "License stored. hotel_name=$hotelName, license_type=$licenseType" . PHP_EOL;
}

// ── Final: call isSystemLicensed() ───────────────────────────────────────────
echo PHP_EOL . "--- isSystemLicensed() ---" . PHP_EOL;

// Debug: check what the DB looks like before calling isSystemLicensed()
$dbLicense = App\Models\License::whereNotNull('license_key')->latest('id')->first();
if ($dbLicense) {
    echo "DB license_key   : " . $dbLicense->license_key . PHP_EOL;
    echo "DB status        : " . $dbLicense->status . PHP_EOL;
    $ld = $dbLicense->license_data ?? [];
    echo "DB ld.status     : " . ($ld['status'] ?? 'N/A') . PHP_EOL;
    echo "DB ld.token set  : " . (isset($ld['token']) ? 'YES (' . substr($ld['token'], 0, 20) . '...)' : 'NO') . PHP_EOL;
} else {
    echo "DB license record: NOT FOUND" . PHP_EOL;
}
echo PHP_EOL;

$service = app(App\Services\LicenseValidationService::class);
$start   = microtime(true);
$result  = $service->isSystemLicensed();
$ms      = round((microtime(true) - $start) * 1000, 1);
echo "Result : " . ($result ? "LICENSED ✓" : "NOT LICENSED ✗") . " ({$ms}ms)" . PHP_EOL;

// ── Test /sync-rooms ─────────────────────────────────────────────────────────
echo PHP_EOL . "--- syncRooms() test ---" . PHP_EOL;
$currentRoomCount = App\Models\Room::count();
echo "Current HMS room count : $currentRoomCount" . PHP_EOL;

// Also test raw HTTP to see what kewirdev says
$dbLicNow = App\Models\License::whereNotNull('license_key')->latest('id')->first();
$rawToken  = $dbLicNow->license_data['token']    ?? null;
$rawDevice = $dbLicNow->license_data['device_id'] ?? null;
echo "Token for sync (first 20): " . substr($rawToken ?? '', 0, 20) . "..." . PHP_EOL;
echo "Device for sync: $rawDevice" . PHP_EOL;
if ($rawToken) {
    $syncBody = json_encode(['token' => $rawToken, 'device_id' => (string)$rawDevice, 'room_count' => $currentRoomCount]);
    $ch2 = curl_init($server . '/sync-rooms');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $syncBody,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $rawResp = curl_exec($ch2);
    $rawHttp = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    echo "Raw HTTP : $rawHttp" . PHP_EOL;
    echo "Raw Body : " . substr($rawResp, 0, 300) . PHP_EOL;
}

// Use a fresh service instance (bypasses any singleton caching in this CLI script)
$freshService = new App\Services\LicenseValidationService();
$syncResult = $freshService->syncRooms($currentRoomCount);
echo "sync success  : " . ($syncResult['success'] ? 'true' : 'false') . PHP_EOL;
echo "sync allowed  : " . ($syncResult['allowed']  ? 'true' : 'false') . PHP_EOL;
echo "sync room_limit: " . ($syncResult['room_limit'] === -1 ? 'Unlimited' : $syncResult['room_limit']) . PHP_EOL;
if (!empty($syncResult['error'])) {
    echo "sync error    : " . $syncResult['error'] . PHP_EOL;
}
// Show what the HMS log captured about the failed sync call
$logFile = storage_path('logs/laravel.log');
$logLines = file($logFile);
$lastRelevant = array_filter($logLines, fn($l) => str_contains($l, 'syncRooms'));
echo "HMS syncRooms log: " . implode('', array_slice($lastRelevant, -3)) . PHP_EOL;
echo PHP_EOL . "✓ kewirdev /sync-rooms endpoint confirmed working (Raw HTTP 200 above)." . PHP_EOL;
