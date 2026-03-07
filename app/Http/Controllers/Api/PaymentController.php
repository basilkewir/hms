<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\GuestFolio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $method = $request->get('method', '');
        $dateRange = $request->get('date_range', 'all');

        // Get completed payments for refund selection
        $payments = Payment::with(['folio.guest'])
            ->where('status', 'completed')
            ->where('local_amount', '>', 0)
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at');

        // Apply search filter
        if ($search) {
            $payments->where(function($query) use ($search) {
                $query->where('payment_number', 'like', "%{$search}%")
                      ->orWhere('transaction_id', 'like', "%{$search}%")
                      ->orWhereHas('folio.guest', function($q) use ($search) {
                          $q->where('full_name', 'like', "%{$search}%");
                      })
                      ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Apply payment method filter
        if ($method) {
            $payments->where('payment_method', $method);
        }

        // Apply date range filter
        if ($dateRange !== 'all') {
            $now = Carbon::now();
            switch ($dateRange) {
                case 'today':
                    $payments->whereDate('processed_at', $now->toDateString());
                    break;
                case 'week':
                    $payments->whereBetween('processed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $payments->whereMonth('processed_at', $now->month)->whereYear('processed_at', $now->year);
                    break;
            }
        }

        $payments = $payments->limit(100)->get();

        // Format payments for frontend
        $formattedPayments = $payments->map(function ($payment) {
            $guestName = $payment->folio?->guest?->full_name;
            $reference = $payment->folio?->folio_number
                ?? ($payment->reservation_id ? 'RES-' . $payment->reservation_id : null);

            return [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'transaction_id' => $payment->transaction_id,
                'guest_name' => $guestName ?: 'Walk-in',
                'reference' => $reference ?: 'Direct Payment',
                'local_amount' => $payment->local_amount,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
                'refunded_amount' => $payment->refunded_amount ?? 0,
                'processed_at' => $payment->processed_at,
                'created_at' => $payment->created_at,
            ];
        });

        return response()->json([
            'payments' => $formattedPayments,
            'total' => $formattedPayments->count()
        ]);
    }
}
