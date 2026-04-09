<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Use the token from the registration test above
$request = Illuminate\Http\Request::create(
    '/api/android/settings?device_id=test_device_debug_001&registration_token=KgAWvp9knxTtXJb181MDW12ROri0XyaA',
    'GET'
);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
echo $response->getContent();
echo "\n\nHTTP Status: " . $response->getStatusCode() . "\n";
