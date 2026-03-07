<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$budgets = App\Models\Budget::with(['category', 'department'])->get();
foreach($budgets as $b) {
    echo $b->name . " - " . $b->amount . " - " . $b->status . "\n";
}
echo "\nTotal: " . $budgets->count() . " budgets\n";
