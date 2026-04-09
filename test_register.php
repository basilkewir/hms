<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$payload = json_encode([
    'device_id'       => 'test_device_debug_001',
    'device_name'     => 'DebugPhone',
    'device_type'     => 'android_tv',
    'android_version' => '11',
    'app_version'     => '1.0',
]);

$request = Illuminate\Http\Request::create(
    '/api/android/register',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json'],
    $payload
);
$request->headers->set('Accept', 'application/json');
$request->headers->set('Content-Type', 'application/json');

$response = $kernel->handle($request);
echo $response->getContent();
echo "\n\nHTTP Status: " . $response->getStatusCode() . "\n";
