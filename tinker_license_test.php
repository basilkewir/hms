<?php
// Tinker script — run with: php artisan tinker --execute="..."
// Test live kewirdev.com API with correct HMAC

$licenseKey = 'ECF5FF1F-7BC3E75B-B8277390-510EF914';
$jwtSecret  = 'QveLxwQnbNvAMwAouKnu0lYr0S3JrvDnXysG0cR';
$server     = 'https://kewirdev.com/api/license';

$license  = App\Models\License::whereNotNull('license_key')->latest('id')->first();
$deviceId = $license?->hardware_fingerprint ?? ('device-' . substr(md5(php_uname('n')), 0, 16));

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
    'metadata'          => ['system_info' => php_uname(), 'php_version' => PHP_VERSION],
];

$jsonBody  = json_encode($payload);
$signature = hash_hmac('sha256', $jsonBody, $jwtSecret);

echo "Device ID : $deviceId\n";
echo "Signature : " . substr($signature, 0, 20) . "...\n\n";

$response = Illuminate\Support\Facades\Http::withOptions(['verify' => false, 'timeout' => 15])
    ->withHeaders([
        'Content-Type'        => 'application/json',
        'Accept'              => 'application/json',
        'User-Agent'          => 'HotelManagement/1.0.0 (Hotel Management System)',
        'X-License-Signature' => $signature,
    ])
    ->post($server . '/validate', $payload);

echo "HTTP: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";
