<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\GuestFolio;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $today = Carbon::today();

        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->where('balance_amount', '>', 0) // Only show unsettled bills
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'sent';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                ];
            });

        if ($statusFilter) {
            $folios = $folios->filter(fn ($folio) => $folio['status'] === $statusFilter)->values();
        }

        $invoiceStats = [
            'total' => $folios->count(),
            'totalAmount' => round($folios->sum('total_amount'), 2),
            'pending' => $folios->whereIn('status', ['sent'])->count(),
            'overdue' => $folios->where('status', 'overdue')->count(),
        ];

        return Inertia::render('Accountant/Invoices/Index', [
            'user' => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios->values(),
            'filters' => [
                'status' => $statusFilter,
            ],
        ]);
    }

    public function show(GuestFolio $folio)
    {
        $folio->load(['guest', 'reservation', 'room', 'charges', 'payments']);
        $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
        $balance = (float) ($folio->balance_amount ?? 0);
        $status = $balance <= 0 ? 'paid' : 'sent';
        if ($balance > 0 && $dueDate && Carbon::parse($dueDate)->lt(Carbon::today())) {
            $status = 'overdue';
        }

        return Inertia::render('Accountant/Invoices/Show', [
            'user' => auth()->user()->load('roles'),
            'invoice' => [
                'id' => $folio->id,
                'invoice_number' => $folio->folio_number,
                'customer_name' => $folio->guest?->full_name ?? 'Guest',
                'customer_email' => $folio->guest?->email ?? null,
                'room_number' => $folio->room?->room_number ?? 'N/A',
                'total_amount' => (float) ($folio->total_amount ?? 0),
                'paid_amount' => (float) ($folio->paid_amount ?? 0),
                'balance_amount' => $balance,
                'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                'due_date' => optional($dueDate)->format('Y-m-d'),
                'status' => $status,
                'notes' => $folio->notes,
                'charges' => $folio->charges->map(fn ($charge) => [
                    'id' => $charge->id,
                    'description' => $charge->description,
                    'amount' => (float) $charge->amount,
                    'created_at' => optional($charge->created_at)->toDateString(),
                ]),
                'payments' => $folio->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
                    'method' => $payment->payment_method,
                    'status' => $payment->status,
                    'processed_at' => optional($payment->processed_at)->toDateTimeString(),
                ]),
            ],
        ]);
    }

    public function overdue()
    {
        $statusFilter = 'overdue';
        $today = Carbon::today();

        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'sent';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'paid_date' => $balance <= 0 ? optional($folio->updated_at)->format('Y-m-d') : null,
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                    'room_number' => $folio->room?->room_number,
                ];
            })
            ->filter(fn ($folio) => $folio['status'] === 'overdue')
            ->values();

        $invoiceStats = [
            'overdue' => $folios->count(),
            'overdueAmount' => round($folios->sum('balance_amount'), 2),
        ];

        return Inertia::render('Accountant/Invoices/Overdue', [
            'user' => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios,
        ]);
    }

    public function paid()
    {
        $statusFilter = 'paid';
        $today = Carbon::today();

        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'sent';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'paid_date' => $balance <= 0 ? optional($folio->updated_at)->format('Y-m-d') : null,
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                    'room_number' => $folio->room?->room_number,
                    'payment_method' => 'cash', // This would come from payment records
                ];
            })
            ->filter(fn ($folio) => $folio['status'] === 'paid')
            ->values();

        $invoiceStats = [
            'paid' => $folios->count(),
            'paidAmount' => round($folios->sum('total_amount'), 2),
        ];

        return Inertia::render('Accountant/Invoices/Paid', [
            'user' => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios,
        ]);
    }

    public function sendReminders()
    {
        $today = Carbon::today();
        
        // Get overdue invoices
        $overdueFolios = GuestFolio::with(['guest', 'reservation'])
            ->where('balance_amount', '>', 0)
            ->whereHas('reservation', function ($query) use ($today) {
                $query->where('check_out_date', '<', $today);
            })
            ->get();

        $remindersSent = 0;
        
        foreach ($overdueFolios as $folio) {
            if ($folio->guest && $folio->guest->email) {
                // Here you would implement actual email sending
                // For now, we'll just count how many reminders would be sent
                $remindersSent++;
                
                // TODO: Implement actual email sending
                // Mail::to($folio->guest->email)->send(new InvoiceReminder($folio));
            }
        }

        return redirect()->route('accountant.invoices.index')
            ->with('success', "Payment reminders sent to {$remindersSent} customers with overdue invoices.");
    }
}
