<?php
// Quick test script — run with: php test_tinker.php
chdir(__DIR__);

// Use artisan as a subprocess
$commands = [
    // 1. Check DB for all IPTV keys
    'App\Models\Setting::where("group","iptv")->get(["key","value"])->map(fn($s)=>$s->key."=".$s->value)->implode(", ")',
    // 2. Count all IPTV keys
    'App\Models\Setting::where("group","iptv")->count()',
    // 3. Check hotel info endpoint response code would be 200
    'App\Models\Setting::where("group","iptv")->pluck("value","key")->toArray()',
];

foreach ($commands as $i => $cmd) {
    $escaped = addslashes($cmd);
    $out = shell_exec('php artisan tinker --execute="echo json_encode(' . $escaped . ');" 2>&1');
    echo "CMD " . ($i+1) . ": " . trim($out) . PHP_EOL;
}
