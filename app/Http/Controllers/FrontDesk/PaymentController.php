<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\GuestFolio;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $currentGuests = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->where('balance_amount', '>', 0)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->guest->full_name ?? 'N/A',
                'room' => $r->room->room_number ?? 'N/A',
                'checkOut' => $r->check_out_date->format('Y-m-d'),
                'total' => $r->balance_amount ?? $r->total_amount ?? 0,
            ]);
        
        $recentPayments = Payment::with(['reservation.guest'])
            ->where('processed_by', Auth::id())
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get()
            ->filter(fn($p) => $p->reservation && $p->reservation->guest)
            ->map(fn($p) => [
                'id' => $p->id,
                'guest' => $p->reservation->guest->full_name ?? 'N/A',
                'amount' => $p->amount,
                'method' => $p->payment_method,
                'status' => $p->status,
                'time' => $p->processed_at->diffForHumans(),
            ])
            ->values();
        
        return Inertia::render('FrontDesk/Payments/Process', [
            'user' => Auth::user()->load('roles'),
            'currentGuests' => $currentGuests,
            'recentPayments' => $recentPayments,
        ]);
    }
    
    public function history()
    {
        $payments = Payment::with(['reservation.guest', 'reservation.room'])
            ->where('processed_by', Auth::id())
            ->orderBy('processed_at', 'desc')
            ->paginate(20);
        
        return Inertia::render('FrontDesk/Payments/History', [
            'user' => Auth::user()->load('roles'),
            'payments' => $payments,
        ]);
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'guestId' => 'required|exists:reservations,id',
                'payment_method' => 'required|in:credit_card,debit_card,cash,bank_transfer',
                'amount' => 'required|numeric|min:0',
                'cardNumber' => 'nullable|string',
                'cardholderName' => 'nullable|string',
                'expiryDate' => 'nullable|string',
                'cvv' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
            
            // Find the reservation
            $reservation = Reservation::findOrFail($validated['guestId']);
            
            // Get or create guest folio
            $folio = GuestFolio::firstOrCreate(
                ['reservation_id' => $reservation->id],
                [
                    'guest_id' => $reservation->guest_id,
                    'room_id' => $reservation->room_id,
                    'folio_number' => 'FOLIO-' . strtoupper(Str::random(8)),
                    'status' => 'open',
                    'folio_date' => now()->toDateString()
                ]
            );
            
            // Check if guest has any unpaid POS bills (restaurant/bar bills)
            $unpaidPosBills = \App\Models\Sale::where('reservation_id', $reservation->id)
                ->where('is_charged_to_room', true)
                ->where('payment_status', 'pending')
                ->count();
            
            // If guest has not paid anything yet (paid_amount = 0) and there are unpaid POS bills,
            // they must pay all bills at checkout, not individually
            if ($folio->paid_amount == 0 && $unpaidPosBills > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not made any payments yet. All bills (including restaurant/bar charges) must be paid together at checkout. Please proceed to checkout to make payment.'
                ], 400);
            }
            
            // Get currency settings
            $currency = 'USD';
            $currencySymbol = '$';
            $exchangeRate = 1.0;
            
            // Calculate local amount
            $localAmount = $validated['amount'] * $exchangeRate;
            
            // Create payment record
            $payment = Payment::create([
                'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                'guest_folio_id' => $folio->id,
                'reservation_id' => $reservation->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'local_amount' => $localAmount,
                'card_type' => $this->getCardType($validated['cardNumber'] ?? ''),
                'card_last_four' => $this->getLastFourDigits($validated['cardNumber'] ?? ''),
                'check_number' => $validated['payment_method'] === 'bank_transfer' ? $request->input('checkNumber') : null,
                'status' => 'completed',
                'processed_at' => now(),
                'processed_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null
            ]);
            
            // Update reservation balance
            $reservation->balance_amount = max(0, $reservation->balance_amount - $validated['amount']);
            $reservation->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'guest_name' => $reservation->guest->full_name ?? 'Unknown',
                    'amount' => $validated['amount'],
                    'currency' => $currency,
                    'currency_symbol' => $currencySymbol,
                    'local_amount' => $localAmount,
                    'method' => $validated['payment_method'],
                    'reservation_id' => $reservation->id,
                    'processed_at' => $payment->processed_at->format('Y-m-d H:i:s'),
                    'status' => $payment->status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getCardType($cardNumber)
    {
        if (empty($cardNumber)) return null;
        
        $firstDigit = substr($cardNumber, 0, 1);
        
        switch ($firstDigit) {
            case '4':
                return 'visa';
            case '5':
                return 'mastercard';
            case '3':
                return 'amex';
            case '6':
                return 'discover';
            default:
                return 'other';
        }
    }
    
    private function getLastFourDigits($cardNumber)
    {
        if (empty($cardNumber)) return null;
        
        return substr($cardNumber, -4);
    }

}
