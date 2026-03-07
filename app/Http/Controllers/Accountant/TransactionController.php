<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\GuestFolio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        $today = Carbon::today();
        $todayRevenue = Payment::where(function($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'paid')
                      ->orWhereNull('status');
            })
            ->whereDate('processed_at', $today)
            ->sum(DB::raw('COALESCE(local_amount, amount)'));
        $todayPosRevenue = Sale::where(function($query) {
                $query->where('payment_status', 'completed')
                      ->orWhere('payment_status', 'paid')
                      ->orWhere('payment_status', 'approved')
                      ->orWhereNull('payment_status');
            })
            ->whereDate('sale_date', $today)
            ->sum('total_amount');

        // Debug logging
        \Log::info('Today revenue from payments: ' . $todayRevenue);
        \Log::info('Today POS revenue: ' . $todayPosRevenue);
        
        // Check actual data counts
        $paymentCount = Payment::count();
        $saleCount = Sale::count();
        \Log::info('Total payments count: ' . $paymentCount);
        \Log::info('Total sales count: ' . $saleCount);

        $transactionStats = [
            'todayRevenue' => round($todayRevenue + $todayPosRevenue, 2),
            'totalTransactions' => Payment::count() + Sale::count(),
            'pending' => Payment::where('status', 'pending')->count()
                + Sale::where('payment_status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count()
                + Sale::where('payment_status', 'failed')->count(),
        ];

        // Apply filters to queries
        $paymentQuery = $this->basePaymentQuery();
        $saleQuery = $this->baseSaleQuery();
        $folioQuery = $this->folioPaymentsQuery();

        // Apply search filter
        if ($request->get('search')) {
            $search = $request->get('search');
            $paymentQuery->where(function($q) use ($search) {
                $q->where('payment_id', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $saleQuery->where(function($q) use ($search) {
                $q->where('sale_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $folioQuery->where(function($q) use ($search) {
                $q->where('folio_number', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->get('status')) {
            $status = $request->get('status');
            $paymentQuery->where(function($query) use ($status) {
                $query->where('status', $status)
                      ->orWhere('payment_status', $status);
            });
            $saleQuery->where('payment_status', $status);
        }

        // Apply date range filter
        if ($request->get('date_range')) {
            $dateRange = $request->get('date_range');
            $now = Carbon::now();

            switch ($dateRange) {
                case 'today':
                    $paymentQuery->whereDate('processed_at', $now->toDateString());
                    $saleQuery->whereDate('sale_date', $now->toDateString());
                    $folioQuery->whereDate('closed_at', $now->toDateString());
                    break;
                case 'week':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $paymentQuery->whereMonth('processed_at', $now->month)->whereYear('processed_at', $now->year);
                    $saleQuery->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                    $folioQuery->whereMonth('closed_at', $now->month)->whereYear('closed_at', $now->year);
                    break;
                case 'quarter':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $paymentQuery->whereYear('processed_at', $now->year);
                    $saleQuery->whereYear('sale_date', $now->year);
                    $folioQuery->whereYear('closed_at', $now->year);
                    break;
            }
        }

        // Get paginated results
        $limit = $perPage / 3; // Divide limit among 3 query types
        $recentPayments = $paymentQuery
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($payment) => $this->mapPayment($payment));

        $recentSales = $saleQuery
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($sale) => $this->mapSale($sale));

        $recentFolios = $folioQuery
            ->orderByDesc('closed_at')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn ($folio) => $this->mapFolioPayment($folio));

        // Merge and sort all transactions
        $allTransactions = collect($recentPayments)
            ->merge($recentSales)
            ->merge($recentFolios)
            ->sortByDesc('created_at')
            ->values();

        // Create manual pagination
        $total = $this->getTotalTransactionsCount($request);
        $currentPageItems = $allTransactions->forPage($page, $perPage);

        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return Inertia::render('Accountant/Transactions/Index', [
            'user' => $user,
            'transactionStats' => $transactionStats,
            'recentTransactions' => $transactions,
            'filters' => [
                'search' => $request->get('search', ''),
                'status' => $request->get('status', ''),
                'date_range' => $request->get('date_range', 'all'),
                'per_page' => $perPage,
            ]
        ]);
    }

    private function getTotalTransactionsCount(Request $request)
    {
        $paymentQuery = $this->basePaymentQuery();
        $saleQuery = $this->baseSaleQuery();
        $folioQuery = $this->folioPaymentsQuery();

        // Apply same filters as in index method
        if ($request->get('search')) {
            $search = $request->get('search');
            $paymentQuery->where(function($q) use ($search) {
                $q->where('payment_id', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $saleQuery->where(function($q) use ($search) {
                $q->where('sale_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $folioQuery->where(function($q) use ($search) {
                $q->where('folio_number', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%");
            });
        }

        if ($request->get('status')) {
            $status = $request->get('status');
            $paymentQuery->where('status', $status);
            $saleQuery->where('payment_status', $status);
        }

        if ($request->get('date_range')) {
            $dateRange = $request->get('date_range');
            $now = Carbon::now();

            switch ($dateRange) {
                case 'today':
                    $paymentQuery->whereDate('processed_at', $now->toDateString());
                    $saleQuery->whereDate('sale_date', $now->toDateString());
                    $folioQuery->whereDate('closed_at', $now->toDateString());
                    break;
                case 'week':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $paymentQuery->whereMonth('processed_at', $now->month)->whereYear('processed_at', $now->year);
                    $saleQuery->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                    $folioQuery->whereMonth('closed_at', $now->month)->whereYear('closed_at', $now->year);
                    break;
                case 'quarter':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $paymentQuery->whereYear('processed_at', $now->year);
                    $saleQuery->whereYear('sale_date', $now->year);
                    $folioQuery->whereYear('closed_at', $now->year);
                    break;
            }
        }

        return $paymentQuery->count() + $saleQuery->count() + $folioQuery->count();
    }

    public function export(Request $request)
    {
        // Apply same filters as index method
        $paymentQuery = $this->basePaymentQuery();
        $saleQuery = $this->baseSaleQuery();
        $folioQuery = $this->folioPaymentsQuery();

        // Apply search filter
        if ($request->get('search')) {
            $search = $request->get('search');
            $paymentQuery->where(function($q) use ($search) {
                $q->where('payment_id', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $saleQuery->where(function($q) use ($search) {
                $q->where('sale_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $folioQuery->where(function($q) use ($search) {
                $q->where('folio_number', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->get('status')) {
            $status = $request->get('status');
            $paymentQuery->where(function($query) use ($status) {
                $query->where('status', $status)
                      ->orWhere('payment_status', $status);
            });
            $saleQuery->where('payment_status', $status);
        }

        // Apply date range filter
        if ($request->get('date_range')) {
            $dateRange = $request->get('date_range');
            $now = Carbon::now();

            switch ($dateRange) {
                case 'today':
                    $paymentQuery->whereDate('processed_at', $now->toDateString());
                    $saleQuery->whereDate('sale_date', $now->toDateString());
                    $folioQuery->whereDate('closed_at', $now->toDateString());
                    break;
                case 'week':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $paymentQuery->whereMonth('processed_at', $now->month)->whereYear('processed_at', $now->year);
                    $saleQuery->whereMonth('sale_date', $now->month)->whereYear('sale_date', $now->year);
                    $folioQuery->whereMonth('closed_at', $now->month)->whereYear('closed_at', $now->year);
                    break;
                case 'quarter':
                    $paymentQuery->whereBetween('processed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $saleQuery->whereBetween('sale_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    $folioQuery->whereBetween('closed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $paymentQuery->whereYear('processed_at', $now->year);
                    $saleQuery->whereYear('sale_date', $now->year);
                    $folioQuery->whereYear('closed_at', $now->year);
                    break;
            }
        }

        // Get all transactions for export (no limit)
        $payments = $paymentQuery
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                $guestName = $payment->folio?->guest?->full_name;
                $reference = $payment->folio?->folio_number
                    ?? ($payment->reservation_id ? 'RES-' . $payment->reservation_id : null);

                $isRefund = ($payment->refunded_amount ?? 0) > 0;

                return [
                    'type' => 'Payment',
                    'id' => $payment->payment_number ?? $payment->transaction_id ?? ('PAY-' . $payment->id),
                    'guest' => $guestName ?: 'Walk-in',
                    'reference' => $reference ?: 'Direct Payment',
                    'amount' => $isRefund ? (float) $payment->refunded_amount : (float) ($payment->local_amount ?? $payment->amount ?? 0),
                    'method' => $payment->payment_method ?? 'unknown',
                    'status' => $payment->status ?? 'completed',
                    'date' => optional($payment->processed_at)->toDateTimeString() ?? optional($payment->created_at)->toDateTimeString(),
                ];
            });

        $sales = $this->baseSaleQuery()
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function ($sale) {
                $guestName = $sale->guest?->full_name
                    ?? $sale->customer?->name
                    ?? $sale->customer_name
                    ?? 'Walk-in';
                $reference = $sale->reservation_id ? 'RES-' . $sale->reservation_id
                    : ($sale->room?->room_number ? 'Room ' . $sale->room->room_number : null);

                return [
                    $sale->sale_number ?? ('POS-' . $sale->id),
                    $guestName,
                    $reference ?: 'POS Sale',
                    'payment',
                    (float) ($sale->total_amount ?? 0),
                    $sale->payment_method ?? 'unknown',
                    $sale->payment_status ?? 'completed',
                    optional($sale->sale_date)->toDateTimeString()
                        ?? optional($sale->created_at)->toDateTimeString(),
                ];
            });

        $folios = $this->folioPaymentsQuery()
            ->orderByDesc('closed_at')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get()
            ->map(function ($folio) {
                $guestName = $folio->guest?->full_name ?: 'Guest';
                $reference = $folio->reservation_id ? 'RES-' . $folio->reservation_id : 'Room Payment';

                return [
                    $folio->folio_number ?? ('FOL-' . $folio->id),
                    $guestName,
                    $reference,
                    'payment',
                    (float) ($folio->paid_amount ?? 0),
                    'room_charge',
                    'completed',
                    optional($folio->closed_at)->toDateTimeString()
                        ?? optional($folio->updated_at)->toDateTimeString(),
                ];
            });

        $rows = collect($payments)
            ->merge($sales)
            ->merge($folios)
            ->values();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, [
                'Transaction ID',
                'Guest',
                'Reference',
                'Type',
                'Amount',
                'Payment Method',
                'Status',
                'Processed At'
            ]);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'transactions.csv');
    }

    public function process(Request $request, Payment $payment)
    {
        if ($payment->status === 'pending') {
            $payment->status = 'completed';
            $payment->processed_at = Carbon::now();
            $payment->processed_by = $request->user()->id;
            $payment->save();
        }

        return redirect()->route('accountant.transactions.index');
    }

    public function payments(Request $request)
    {
        $user = $request->user()->load('roles');

        $payments = $this->completedPaymentsQuery()
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($payment) => $this->mapPayment($payment));

        $sales = $this->completedSalesQuery()
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($sale) => $this->mapSale($sale));

        $folioPayments = $this->folioPaymentsQuery()
            ->orderByDesc('closed_at')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(fn ($folio) => $this->mapFolioPayment($folio));

        $rows = collect($payments)
            ->merge($sales)
            ->merge($folioPayments)
            ->sortByDesc('created_at')
            ->values()
            ->take(50);

        return Inertia::render('Accountant/Transactions/Payments', [
            'user' => $user,
            'payments' => $rows,
        ]);
    }

    public function refunds(Request $request)
    {
        $user = $request->user()->load('roles');

        $refunds = $this->basePaymentQuery()
            ->where('refunded_amount', '>', 0)
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($payment) => $this->mapPayment($payment, true));

        return Inertia::render('Accountant/Transactions/Refunds', [
            'user' => $user,
            'refunds' => $refunds,
        ]);
    }

    public function createRefund(Request $request)
    {
        $user = $request->user()->load('roles');

        // Get all completed payments for refund selection
        $paymentsQuery = $this->completedPaymentsQuery()
            ->with(['folio.guest'])
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->limit(100);
        
        $payments = $paymentsQuery->get()
            ->map(fn ($payment) => $this->mapPayment($payment));

        // Also get completed sales and folio payments for more options
        $sales = $this->completedSalesQuery()
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($sale) => $this->mapSale($sale));

        $folioPayments = $this->folioPaymentsQuery()
            ->orderByDesc('closed_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($folio) => $this->mapFolioPayment($folio));

        // Combine all payment types for refund selection
        $allRefundableItems = collect([])
            ->merge($payments)
            ->merge($sales)
            ->merge($folioPayments);

        return Inertia::render('Accountant/Transactions/CreateRefund', [
            'user' => $user,
            'payments' => $allRefundableItems,
        ]);
    }

    public function processRefund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . ($payment->local_amount - $payment->refunded_amount),
            'refund_reason' => 'required|string|max:500',
            'refund_method' => 'required|in:same_method,cash,bank_transfer',
        ]);

        // Check if payment is eligible for refund
        if ($payment->status !== 'completed') {
            return redirect()->back()->with('error', 'Only completed payments can be refunded.');
        }

        $refundableAmount = $payment->local_amount - $payment->refunded_amount;
        if ($validated['refund_amount'] > $refundableAmount) {
            return redirect()->back()->with('error', 'Refund amount cannot exceed the remaining refundable amount.');
        }

        // Update payment with refund information
        $payment->refunded_amount = ($payment->refunded_amount ?? 0) + $validated['refund_amount'];
        $payment->refunded_at = now();
        $payment->refunded_by = $request->user()->id;
        $payment->refund_reason = $validated['refund_reason'];
        $payment->refund_method = $validated['refund_method'];

        // If full refund, mark payment as refunded
        if ($payment->refunded_amount >= $payment->local_amount) {
            $payment->status = 'refunded';
        }

        $payment->save();

        // Update reservation balance if this payment was for a reservation
        if ($payment->reservation_id) {
            $reservation = $payment->reservation;
            $reservation->balance_amount = max(0, $reservation->balance_amount - $validated['refund_amount']);
            $reservation->save();
        }

        return redirect()->route('accountant.transactions.refunds')
            ->with('success', 'Refund processed successfully.');
    }

    public function pending(Request $request)
    {
        $user = $request->user()->load('roles');

        $pendingPayments = $this->basePaymentQuery()
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('status')
                            ->whereNull('processed_at');
                    });
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($payment) => $this->mapPayment($payment));

        $pendingSales = $this->baseSaleQuery()
            ->where('payment_status', 'pending')
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($sale) => $this->mapSale($sale));

        $rows = collect($pendingPayments)
            ->merge($pendingSales)
            ->sortByDesc('created_at')
            ->values()
            ->take(50);

        return Inertia::render('Accountant/Transactions/Pending', [
            'user' => $user,
            'pendingPayments' => $rows,
        ]);
    }

    private function mapPayment(Payment $payment, ?bool $forceRefund = null): array
    {
        $guestName = $payment->folio?->guest?->full_name;
        $reference = $payment->folio?->folio_number
            ?? ($payment->reservation_id ? 'RES-' . $payment->reservation_id : null);
        $isRefund = $forceRefund ?? (($payment->refunded_amount ?? 0) > 0);

        return [
            'id' => $payment->id,
            'payment_number' => $payment->payment_number
                ?? $payment->transaction_id
                ?? ('PAY-' . $payment->id),
            'transaction_id' => $payment->payment_number
                ?? $payment->transaction_id
                ?? ('PAY-' . $payment->id),
            'guest_name' => $guestName ?: 'Walk-in',
            'reference' => $reference ?: 'Direct Payment',
            'type' => $isRefund ? 'refund' : 'payment',
            'amount' => $isRefund
                ? (float) ($payment->refunded_amount ?? 0)
                : (float) ($payment->local_amount ?? $payment->amount ?? 0),
            'local_amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
            'payment_method' => $payment->payment_method ?? 'unknown',
            'status' => $payment->status ?? 'completed',
            'processed_at' => optional($payment->processed_at)->toDateTimeString(),
            'created_at' => optional($payment->created_at)->toDateTimeString(),
            'refunded_amount' => (float) ($payment->refunded_amount ?? 0),
        ];
    }

    private function mapSale(Sale $sale): array
    {
        $guestName = $sale->guest?->full_name
            ?? $sale->customer?->name
            ?? $sale->customer_name
            ?? 'Walk-in';
        $reference = $sale->reservation_id ? 'RES-' . $sale->reservation_id
            : ($sale->room?->room_number ? 'Room ' . $sale->room->room_number : null);

        return [
            'id' => 'sale-' . $sale->id,
            'transaction_id' => $sale->sale_number ?? ('POS-' . $sale->id),
            'guest_name' => $guestName,
            'reference' => $reference ?: 'POS Sale',
            'type' => 'payment',
            'amount' => (float) ($sale->total_amount ?? 0),
            'payment_method' => $sale->payment_method ?? 'unknown',
            'status' => $sale->payment_status ?? 'completed',
            'created_at' => optional($sale->sale_date)->toDateTimeString()
                ?? optional($sale->created_at)->toDateTimeString(),
        ];
    }

    private function mapFolioPayment(GuestFolio $folio): array
    {
        $guestName = $folio->guest?->full_name ?: 'Guest';
        $reference = $folio->reservation_id ? 'RES-' . $folio->reservation_id : 'Room Payment';

        return [
            'id' => 'folio-' . $folio->id,
            'payment_number' => $folio->folio_number ?? ('FOL-' . $folio->id),
            'transaction_id' => $folio->folio_number ?? ('FOL-' . $folio->id),
            'guest_name' => $guestName,
            'reference' => $reference,
            'type' => 'payment',
            'amount' => (float) ($folio->paid_amount ?? 0),
            'local_amount' => (float) ($folio->paid_amount ?? 0),
            'payment_method' => 'room_charge',
            'status' => 'completed',
            'processed_at' => optional($folio->closed_at)->toDateTimeString(),
            'created_at' => optional($folio->closed_at)->toDateTimeString() ?? optional($folio->updated_at)->toDateTimeString(),
            'refunded_amount' => 0,
        ];
    }

    private function basePaymentQuery()
    {
        return Payment::with(['folio.guest']);
    }

    private function baseSaleQuery()
    {
        return Sale::with(['guest', 'customer', 'room', 'reservation']);
    }

    private function folioPaymentsQuery()
    {
        return GuestFolio::with(['guest', 'room', 'reservation'])
            ->where('paid_amount', '>', 0)
            ->whereDoesntHave('payments');
    }

    private function completedPaymentsQuery()
    {
        return $this->basePaymentQuery()
            ->where(function ($query) {
                $query->whereIn('status', ['completed', 'paid', 'approved'])
                    ->orWhereNull('status')
                    ->orWhereNotNull('processed_at');
            });
    }

    private function completedSalesQuery()
    {
        return $this->baseSaleQuery()
            ->where(function ($query) {
                $query->where('payment_status', 'completed')
                      ->orWhere('payment_status', 'paid')
                      ->orWhere('payment_status', 'approved')
                      ->orWhereNull('payment_status');
            });
    }
}
