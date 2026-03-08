<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$today = now()->toDateString();

echo '=== ADMIN / MANAGER TRANSACTIONS ===' . PHP_EOL;
try {
    $totalTx = \App\Models\Payment::count() + \App\Models\Sale::count() + \App\Models\Expense::count();
    echo 'total: ' . $totalTx . PHP_EOL;

    $todayRevenue = (float) \App\Models\Payment::whereDate('processed_at', $today)->where('status','completed')->sum('amount')
                  + \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
    echo 'todayRevenue: $' . number_format($todayRevenue, 2) . PHP_EOL;

    $totalRevenue = (float) \App\Models\Payment::where('status','completed')->sum('amount')
                  + \App\Models\Sale::sum('total_amount');
    echo 'totalRevenue: $' . number_format($totalRevenue, 2) . PHP_EOL;

    $todayRoomRev = (float) \App\Models\Payment::whereDate('processed_at', $today)->where('status','completed')->whereHas('reservation')->sum('amount');
    $todayPosRev  = (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
    $allRoomRev   = (float) \App\Models\Payment::where('status','completed')->whereHas('reservation')->sum('amount');
    $allPosRev    = (float) \App\Models\Sale::sum('total_amount');
    echo 'allRoomRev: $' . number_format($allRoomRev, 2) . PHP_EOL;
    echo 'allPosRev: $' . number_format($allPosRev, 2) . PHP_EOL;

    $payments = \App\Models\Payment::with('reservation.guest')->orderBy('created_at','desc')->limit(10)->get();
    echo 'Payments to show: ' . $payments->count() . PHP_EOL;
    $sales = \App\Models\Sale::with(['guest','user'])->orderBy('created_at','desc')->limit(10)->get();
    echo 'Sales to show: ' . $sales->count() . PHP_EOL;
    $expenses = \App\Models\Expense::orderBy('expense_date','desc')->limit(10)->get();
    echo 'Expenses to show: ' . $expenses->count() . PHP_EOL;

    echo 'TRANSACTIONS OK' . PHP_EOL;
} catch (\Exception $e) { echo 'ERROR: ' . $e->getMessage() . PHP_EOL; }

echo PHP_EOL . '=== ACCOUNTANT TRANSACTIONS (paginated wrap) ===' . PHP_EOL;
try {
    $totalTx = \App\Models\Payment::count() + \App\Models\Sale::count() + \App\Models\PosTransaction::count() + \App\Models\FolioCharge::count() + \App\Models\Expense::count();
    echo 'total: ' . $totalTx . PHP_EOL;

    $completed = \App\Models\Payment::where('status','completed')->count() + \App\Models\Sale::where('payment_status','completed')->count();
    echo 'completed: ' . $completed . PHP_EOL;

    $pending = \App\Models\Payment::where('status','pending')->count() + \App\Models\Expense::where('status','pending')->count();
    echo 'pending: ' . $pending . PHP_EOL;

    $posTx = \App\Models\PosTransaction::with(['sale','cashDrawerSession.user'])->orderBy('id','desc')->limit(10)->get();
    echo 'POS transactions: ' . $posTx->count() . PHP_EOL;

    $folioCharges = \App\Models\FolioCharge::with(['folio.reservation.guest'])->orderBy('id','desc')->limit(5)->get();
    echo 'Folio charges: ' . $folioCharges->count() . PHP_EOL;
    if ($folioCharges->count() > 0) {
        $fc = $folioCharges->first();
        echo '  FolioCharge columns: ' . implode(', ', array_keys($fc->toArray())) . PHP_EOL;
    }

    echo 'ACCOUNTANT TX OK' . PHP_EOL;
} catch (\Exception $e) { echo 'ERROR: ' . $e->getMessage() . PHP_EOL; }

echo PHP_EOL . '=== POS TRANSACTIONS ===' . PHP_EOL;
try {
    $posTx = \App\Models\PosTransaction::with(['sale','cashDrawerSession.user'])->orderBy('created_at','desc')->get();
    echo 'POS transactions total: ' . $posTx->count() . PHP_EOL;
    if ($posTx->count() > 0) {
        $t = $posTx->first();
        echo '  Columns: ' . implode(', ', array_keys($t->toArray())) . PHP_EOL;
    }
    echo 'POS TX OK' . PHP_EOL;
} catch (\Exception $e) { echo 'ERROR: ' . $e->getMessage() . PHP_EOL; }
