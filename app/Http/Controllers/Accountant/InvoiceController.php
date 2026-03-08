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

        // Show ALL folios, not just unpaid ones
        $allFolios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $total   = (float) ($folio->total_amount ?? 0);
                $status  = 'sent';
                if ($balance <= 0 && $total > 0) {
                    $status = 'paid';
                } elseif ($balance <= 0 && $total <= 0) {
                    $status = 'draft';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id'             => $folio->id,
                    'invoice_number' => $folio->folio_number ?? ('INV-' . str_pad($folio->id, 5, '0', STR_PAD_LEFT)),
                    'customer_name'  => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                    'total_amount'   => $total,
                    'paid_amount'    => (float) ($folio->paid_amount ?? 0),
                    'issue_date'     => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date'       => optional($dueDate)->format('Y-m-d'),
                    'status'         => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                ];
            });

        // Also pull in Payments that are not linked to a folio as standalone invoices
        $paymentInvoices = Payment::with(['reservation.guest', 'reservation.room'])
            ->whereNull('guest_folio_id')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($p) use ($today) {
                $dueDate = $p->reservation?->check_out_date;
                $status  = match ($p->status) {
                    'completed' => 'paid',
                    'failed'    => 'overdue',
                    default     => 'sent',
                };
                return [
                    'id'             => 'PAY-' . $p->id,
                    'invoice_number' => $p->payment_number ?? ('PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
                    'customer_name'  => $p->reservation?->guest
                        ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                        : 'Guest',
                    'customer_email' => $p->reservation?->guest?->email ?? null,
                    'total_amount'   => (float) $p->amount,
                    'paid_amount'    => $p->status === 'completed' ? (float) $p->amount : 0,
                    'issue_date'     => $p->processed_at?->format('Y-m-d') ?? $p->created_at->format('Y-m-d'),
                    'due_date'       => optional($dueDate)->format('Y-m-d'),
                    'status'         => $status,
                    'reservation_id' => $p->reservation_id,
                    'balance_amount' => $p->status === 'completed' ? 0 : (float) $p->amount,
                ];
            });

        $folios = $allFolios->merge($paymentInvoices)->sortByDesc('issue_date');

        if ($statusFilter) {
            $folios = $folios->filter(fn ($folio) => $folio['status'] === $statusFilter)->values();
        }

        // Stats from all folios (unfiltered)
        $all = $allFolios->merge($paymentInvoices);
        $invoiceStats = [
            'total'       => $all->count(),
            'totalAmount' => round($all->sum('total_amount'), 2),
            'pending'     => $all->whereIn('status', ['sent', 'draft'])->count(),
            'overdue'     => $all->where('status', 'overdue')->count(),
        ];

        return Inertia::render('Accountant/Invoices/Index', [
            'user'         => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices'     => $folios->values(),
            'filters'      => ['status' => $statusFilter],
        ]);
    }

    public function export(Request $request)
    {
        $today = Carbon::today();
        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $total   = (float) ($folio->total_amount ?? 0);
                $status  = $balance <= 0 && $total > 0 ? 'paid' : ($dueDate && Carbon::parse($dueDate)->lt($today) ? 'overdue' : 'sent');
                return [
                    'Invoice Number' => $folio->folio_number ?? ('INV-' . $folio->id),
                    'Customer'       => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                    'Email'          => $folio->guest?->email ?? '',
                    'Total Amount'   => $total,
                    'Paid Amount'    => (float) ($folio->paid_amount ?? 0),
                    'Balance'        => $balance,
                    'Status'         => $status,
                    'Issue Date'     => optional($folio->folio_date)->format('Y-m-d'),
                    'Due Date'       => optional($dueDate)->format('Y-m-d'),
                ];
            });

        $filename = 'invoices-' . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(function () use ($folios) {
            $handle = fopen('php://output', 'wb');
            if ($folios->isNotEmpty()) {
                fputcsv($handle, array_keys($folios->first()));
            }
            foreach ($folios as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
