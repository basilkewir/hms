<?php
/**
 * diag_register.php
 * Tests the exact URL the Android app builds for POST /api/android/register
 * and shows what comes back — including any redirects or HTML errors.
 */

$urls = [
    'http://127.0.0.1:8001',        // localhost
    'http://192.168.20.64:8001',    // LAN IP (from .env APP_URL)
];

$payload = json_encode([
    'device_id'       => 'diag_android_test_' . time(),
    'device_name'     => 'Diagnostic TV',
    'device_type'     => 'android_tv',
    'android_version' => '12',
    'app_version'     => '1.0',
    // no mac_address — same as Java app sends
]);

foreach ($urls as $base) {
    $endpoint = $base . '/api/android/register';
    echo "\n══════════════════════════════════════════════════\n";
    echo "Testing: $endpoint\n";
    echo "══════════════════════════════════════════════════\n";

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,   // show redirects instead of following
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);

    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "❌ cURL error: $err\n";
        continue;
    }

    echo "HTTP Status: $code\n";

    // Split headers from body
    [$headers, $body] = array_pad(explode("\r\n\r\n", $raw, 2), 2, '');

    // Show relevant headers only
    foreach (explode("\r\n", $headers) as $h) {
        if (preg_match('/^(HTTP|Location|Content-Type|X-Rate)/i', $h)) {
            echo "  $h\n";
        }
    }

    $body = trim($body);
    echo "\nBody (first 400 chars):\n";
    echo substr($body, 0, 400) . "\n";

    $decoded = json_decode($body, true);
    if ($decoded === null) {
        echo "\n❌ NOT valid JSON — server returned: " . substr(ltrim($body), 0, 60) . "\n";
    } else {
        echo "\n✅ Valid JSON. success=" . ($decoded['success'] ? 'true' : 'false') . "\n";
        if (!empty($decoded['registration_token'])) {
            echo "   token=" . substr($decoded['registration_token'], 0, 20) . "...\n";
        }
        if (!empty($decoded['message'])) {
            echo "   message=" . $decoded['message'] . "\n";
        }
    }
}

echo "\n\nDone.\n";


$payload = json_encode([
    'device_id'       => 'diag_android_test',
    'device_name'     => 'Diagnostic TV',
    'device_type'     => 'android_tv',
    'android_version' => '12',
    'app_version'     => '1.0',
    // no mac_address — same as Java app
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_HEADER         => true,   // include response headers
    CURLOPT_FOLLOWLOCATION => false,  // don't follow redirects — show the redirect itself
    CURLOPT_TIMEOUT        => 10,
]);

$raw  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo "HTTP Status : $code\n";
if ($err) echo "cURL error  : $err\n";

// Split headers from body
$parts = explode("\r\n\r\n", $raw, 2);
$headers = $parts[0] ?? '';
$body    = $parts[1] ?? '';

echo "\n--- Response Headers ---\n$headers\n";
echo "\n--- Response Body (first 500 chars) ---\n";
echo substr($body, 0, 500) . "\n";

// Is it JSON?
$decoded = json_decode($body, true);
echo "\n--- JSON decode result ---\n";
if ($decoded === null) {
    echo "NOT valid JSON (json_last_error: " . json_last_error_msg() . ")\n";
    echo "Body starts with: " . substr(ltrim($body), 0, 30) . "\n";
} else {
    echo "Valid JSON: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
}
