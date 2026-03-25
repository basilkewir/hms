<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\FolioCharge;
use App\Models\GuestFolio;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Sale;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HospitalityReportController extends Controller
{
    public function __construct(private readonly FinancialService $financialService) {}

    // ─── Departmental Revenue ─────────────────────────────────────────────────

    public function departmentalRevenue(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $rows = FolioCharge::query()
            ->where('is_voided', false)
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                DB::raw("COALESCE(charge_type, 'other') as type"),
                DB::raw('SUM(net_amount)   as net_revenue'),
                DB::raw('SUM(tax_amount)   as tax_collected'),
                DB::raw('SUM(total_amount) as gross_revenue'),
                DB::raw('COUNT(*)          as tx_count'),
                DB::raw('SUM(CASE WHEN is_revenue = 0 THEN total_amount ELSE 0 END) as non_revenue')
            )
            ->groupBy(DB::raw("COALESCE(charge_type, 'other')"))
            ->orderByDesc('gross_revenue')
            ->get()
            ->map(fn($r) => [
                'type'         => $r->type,
                'label'        => $this->chargeTypeLabel($r->type),
                'net_revenue'  => (float) $r->net_revenue,
                'tax_collected'=> (float) $r->tax_collected,
                'gross_revenue'=> (float) $r->gross_revenue,
                'tx_count'     => (int) $r->tx_count,
                'non_revenue'  => (float) $r->non_revenue,
            ]);

        $totals = [
            'net_revenue'   => $rows->sum('net_revenue'),
            'tax_collected' => $rows->sum('tax_collected'),
            'gross_revenue' => $rows->sum('gross_revenue'),
            'tx_count'      => $rows->sum('tx_count'),
        ];

        return Inertia::render('Accountant/Reports/DepartmentalRevenue', [
            'user'      => auth()->user()->load('roles'),
            'rows'      => $rows,
            'totals'    => $totals,
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Service Charge Distribution ──────────────────────────────────────────

    public function serviceChargeDistribution(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $charges = FolioCharge::query()
            ->where('is_voided', false)
            ->where('charge_type', 'service_charge')
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'charge_code',
                'department',
                DB::raw('DATE(charge_date) as day'),
                DB::raw('SUM(net_amount)   as amount'),
                DB::raw('COUNT(*)          as tx_count')
            )
            ->groupBy('charge_code', 'department', DB::raw('DATE(charge_date)'))
            ->orderBy('day')
            ->get();

        $daily = $charges->groupBy('day')->map(fn($g) => [
            'day'    => $g->first()->day,
            'amount' => (float) $g->sum('amount'),
            'count'  => (int) $g->sum('tx_count'),
        ])->values();

        $byCode = $charges->groupBy('charge_code')->map(fn($g, $code) => [
            'charge_code' => $code,
            'department'  => $g->first()->department,
            'amount'      => (float) $g->sum('amount'),
            'tx_count'    => (int) $g->sum('tx_count'),
        ])->values();

        $total = (float) $charges->sum('amount');

        return Inertia::render('Accountant/Reports/ServiceChargeDistribution', [
            'user'      => auth()->user()->load('roles'),
            'daily'     => $daily,
            'byCode'    => $byCode,
            'total'     => $total,
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Tax Reconciliation ───────────────────────────────────────────────────

    public function taxReconciliation(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $rows = FolioCharge::query()
            ->where('is_voided', false)
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                DB::raw('ROUND(tax_rate, 2) as rate'),
                DB::raw('SUM(net_amount)    as taxable_base'),
                DB::raw('SUM(tax_amount)    as tax_collected'),
                DB::raw('SUM(total_amount)  as gross'),
                DB::raw('COUNT(*)           as tx_count')
            )
            ->groupBy(DB::raw('ROUND(tax_rate, 2)'))
            ->orderByDesc(DB::raw('SUM(tax_amount)'))
            ->get()
            ->map(fn($r) => [
                'rate'          => (float) $r->rate,
                'taxable_base'  => (float) $r->taxable_base,
                'tax_collected' => (float) $r->tax_collected,
                'gross'         => (float) $r->gross,
                'tx_count'      => (int) $r->tx_count,
            ]);

        // POS tax (from sales table)
        $posTax = Sale::query()
            ->whereBetween('sale_date', [$start, $end])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('SUM(subtotal)    as taxable_base'),
                DB::raw('SUM(tax_amount)  as tax_collected'),
                DB::raw('SUM(total_amount) as gross'),
                DB::raw('COUNT(*)          as tx_count')
            )
            ->first();

        $totals = [
            'folio_taxable' => $rows->sum('taxable_base'),
            'folio_tax'     => $rows->sum('tax_collected'),
            'folio_gross'   => $rows->sum('gross'),
            'pos_taxable'   => (float) ($posTax->taxable_base ?? 0),
            'pos_tax'       => (float) ($posTax->tax_collected ?? 0),
            'pos_gross'     => (float) ($posTax->gross ?? 0),
        ];
        $totals['combined_tax'] = $totals['folio_tax'] + $totals['pos_tax'];

        return Inertia::render('Accountant/Reports/TaxReconciliation', [
            'user'      => auth()->user()->load('roles'),
            'rows'      => $rows,
            'posTax'    => $posTax,
            'totals'    => $totals,
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Aging Accounts Receivable ────────────────────────────────────────────

    public function agingAccountsReceivable(Request $request): \Inertia\Response
    {
        $today = Carbon::today();

        $folios = GuestFolio::query()
            ->where('status', 'open')
            ->where('balance_amount', '>', 0)
            ->with([
                'reservation:id,reservation_number,check_out_date,guest_id,room_type_id',
                'reservation.guest:id,first_name,last_name',
                'guest:id,first_name,last_name',
            ])
            ->select('id', 'folio_number', 'reservation_id', 'guest_id', 'balance_amount', 'folio_date', 'customer_name')
            ->get()
            ->map(function ($f) use ($today) {
                // Age from the reservation checkout date if available, else folio_date
                $dueDate = optional($f->reservation)->check_out_date
                    ? Carbon::parse($f->reservation->check_out_date)
                    : Carbon::parse($f->folio_date);

                $days = (int) $dueDate->diffInDays($today, false);
                $bucket = match(true) {
                    $days <= 0       => 'current',
                    $days <= 30      => '1_30',
                    $days <= 60      => '31_60',
                    $days <= 90      => '61_90',
                    default          => '90_plus',
                };

                $guestName = $f->customer_name
                    ?? ($f->guest ? trim($f->guest->first_name . ' ' . $f->guest->last_name) : null)
                    ?? optional(optional($f->reservation)->guest)->full_name
                    ?? '—';

                return [
                    'folio_id'      => $f->id,
                    'folio_number'  => $f->folio_number,
                    'guest_name'    => $guestName,
                    'confirmation'  => optional($f->reservation)->reservation_number,
                    'due_date'      => $dueDate->toDateString(),
                    'days_overdue'  => max(0, $days),
                    'balance'       => (float) $f->balance_amount,
                    'bucket'        => $bucket,
                ];
            });

        $summary = [
            'current'  => ['label' => 'Current',     'amount' => 0, 'count' => 0],
            '1_30'     => ['label' => '1–30 days',   'amount' => 0, 'count' => 0],
            '31_60'    => ['label' => '31–60 days',  'amount' => 0, 'count' => 0],
            '61_90'    => ['label' => '61–90 days',  'amount' => 0, 'count' => 0],
            '90_plus'  => ['label' => '90+ days',    'amount' => 0, 'count' => 0],
        ];
        foreach ($folios as $f) {
            $summary[$f['bucket']]['amount'] += $f['balance'];
            $summary[$f['bucket']]['count']++;
        }

        return Inertia::render('Accountant/Reports/AgingAR', [
            'user'     => auth()->user()->load('roles'),
            'folios'   => $folios->values(),
            'summary'  => array_values($summary),
            'total'    => (float) $folios->sum('balance'),
            'asOfDate' => $today->toDateString(),
            'currency' => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Night Audit / Trial Balance ──────────────────────────────────────────

    public function nightAudit(Request $request): \Inertia\Response
    {
        $auditDate = $request->get('audit_date')
            ? Carbon::parse($request->get('audit_date'))
            : Carbon::today();

        $dateStr  = $auditDate->toDateString();
        $startOfDay = $auditDate->copy()->startOfDay();
        $endOfDay   = $auditDate->copy()->endOfDay();

        // Room revenue for the audit date (night audit = room charges for that night)
        $roomRevenue = FolioCharge::where('is_voided', false)
            ->where('charge_date', $dateStr)
            ->where('charge_type', 'room')
            ->sum('net_amount');

        // All charges posted on audit date
        $totalCharges = FolioCharge::where('is_voided', false)
            ->where('charge_date', $dateStr)
            ->sum('total_amount');

        $totalNetCharges = FolioCharge::where('is_voided', false)
            ->where('charge_date', $dateStr)
            ->sum('net_amount');

        $totalTaxCharges = FolioCharge::where('is_voided', false)
            ->where('charge_date', $dateStr)
            ->sum('tax_amount');

        // Payments received on audit date
        $paymentsReceived = Payment::where('status', 'completed')
            ->whereBetween('processed_at', [$startOfDay, $endOfDay])
            ->sum('local_amount');

        $paymentsRefunded = Payment::where('status', 'refunded')
            ->whereBetween('processed_at', [$startOfDay, $endOfDay])
            ->sum('local_amount');

        $netPayments = $paymentsReceived - $paymentsRefunded;

        // In-house guests (check-in count)
        $inHouseCount = Reservation::where('status', 'checked_in')->count();

        // Arrivals & departures
        $arrivalsCount    = Reservation::whereDate('check_in_date',  $dateStr)->where('status', 'checked_in')->count();
        $departuresCount  = Reservation::whereDate('check_out_date', $dateStr)->whereIn('status', ['checked_out', 'completed'])->count();

        // Open folios at end of day
        $openFoliosBalance = GuestFolio::where('status', 'open')->sum('balance_amount');
        $openFolioCount    = GuestFolio::where('status', 'open')->count();

        // Charge breakdown by type
        $chargeByType = FolioCharge::where('is_voided', false)
            ->where('charge_date', $dateStr)
            ->select(
                DB::raw("COALESCE(charge_type, 'other') as type"),
                DB::raw('SUM(net_amount)   as net_amount'),
                DB::raw('SUM(tax_amount)   as tax_amount'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*)          as count')
            )
            ->groupBy(DB::raw("COALESCE(charge_type, 'other')"))
            ->get()
            ->map(fn($r) => [
                'type'         => $r->type,
                'label'        => $this->chargeTypeLabel($r->type),
                'net_amount'   => (float) $r->net_amount,
                'tax_amount'   => (float) $r->tax_amount,
                'total_amount' => (float) $r->total_amount,
                'count'        => (int) $r->count,
            ]);

        // Payment method breakdown
        $paymentByMethod = Payment::where('status', 'completed')
            ->whereBetween('processed_at', [$startOfDay, $endOfDay])
            ->select(
                'payment_method',
                DB::raw('SUM(local_amount) as amount'),
                DB::raw('COUNT(*)          as count')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => [
                'method' => $r->payment_method,
                'amount' => (float) $r->amount,
                'count'  => (int) $r->count,
            ]);

        $trialBalance = [
            'total_charges'      => (float) $totalCharges,
            'total_net_charges'  => (float) $totalNetCharges,
            'total_tax_charges'  => (float) $totalTaxCharges,
            'room_revenue'       => (float) $roomRevenue,
            'payments_received'  => (float) $paymentsReceived,
            'payments_refunded'  => (float) $paymentsRefunded,
            'net_payments'       => (float) $netPayments,
            'open_balance'       => (float) $openFoliosBalance,
            'open_folio_count'   => (int) $openFolioCount,
            'in_house_count'     => (int) $inHouseCount,
            'arrivals'           => (int) $arrivalsCount,
            'departures'         => (int) $departuresCount,
        ];

        return Inertia::render('Accountant/Reports/NightAudit', [
            'user'           => auth()->user()->load('roles'),
            'auditDate'      => $auditDate->toDateString(),
            'trialBalance'   => $trialBalance,
            'chargeByType'   => $chargeByType,
            'paymentByMethod'=> $paymentByMethod,
            'currency'       => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Gratuity / Tips Report ───────────────────────────────────────────────

    public function gratuityReport(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $rows = Sale::query()
            ->where('payment_status', 'paid')
            ->whereBetween('sale_date', [$start, $end])
            ->where('tip_amount', '>', 0)
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select(
                'sales.user_id',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as staff_name"),
                DB::raw('COUNT(sales.id)       as tx_count'),
                DB::raw('SUM(sales.subtotal)   as sales_base'),
                DB::raw('SUM(sales.tip_amount) as tip_total')
            )
            ->groupBy('sales.user_id', DB::raw("CONCAT(users.first_name, ' ', users.last_name)"))
            ->orderByDesc('tip_total')
            ->get()
            ->map(fn($r) => [
                'staff_name' => $r->staff_name,
                'tx_count'   => (int) $r->tx_count,
                'sales_base' => (float) $r->sales_base,
                'tip_total'  => (float) $r->tip_total,
            ]);

        $daily = Sale::query()
            ->where('payment_status', 'paid')
            ->whereBetween('sale_date', [$start, $end])
            ->where('tip_amount', '>', 0)
            ->select(
                DB::raw('DATE(sale_date)       as day'),
                DB::raw('SUM(tip_amount)       as tip_total'),
                DB::raw('COUNT(*)              as tx_count')
            )
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('day')
            ->get()
            ->map(fn($r) => [
                'day'       => $r->day,
                'tip_total' => (float) $r->tip_total,
                'tx_count'  => (int) $r->tx_count,
            ]);

        return Inertia::render('Accountant/Reports/Gratuity', [
            'user'      => auth()->user()->load('roles'),
            'rows'      => $rows,
            'daily'     => $daily,
            'totals'    => [
                'tip_total' => (float) $rows->sum('tip_total'),
                'tx_count'  => (int) $rows->sum('tx_count'),
            ],
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Incidentals Analysis (Manager + Accountant) ──────────────────────────

    public function incidentalsAnalysis(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $rows = FolioCharge::query()
            ->where('is_voided', false)
            ->whereNotIn('charge_type', ['room', 'tax'])
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'charge_code',
                DB::raw("COALESCE(charge_type, 'other') as type"),
                'department',
                DB::raw('SUM(net_amount)   as amount'),
                DB::raw('SUM(tax_amount)   as tax'),
                DB::raw('COUNT(*)          as count')
            )
            ->groupBy('charge_code', DB::raw("COALESCE(charge_type, 'other')"), 'department')
            ->orderByDesc(DB::raw('SUM(net_amount)'))
            ->get()
            ->map(fn($r) => [
                'charge_code' => $r->charge_code,
                'type'        => $r->type,
                'label'       => $this->chargeTypeLabel($r->type),
                'department'  => $r->department,
                'amount'      => (float) $r->amount,
                'tax'         => (float) $r->tax,
                'count'       => (int) $r->count,
            ]);

        $byType = $rows
            ->groupBy('type')
            ->map(fn($g, $type) => [
                'type'   => $type,
                'label'  => $this->chargeTypeLabel($type),
                'amount' => (float) $g->sum('amount'),
                'count'  => (int) $g->sum('count'),
            ])
            ->values();

        return Inertia::render('Admin/Reports/IncidentalsAnalysis', [
            'user'      => auth()->user()->load('roles'),
            'rows'      => $rows,
            'byType'    => $byType,
            'total'     => (float) $rows->sum('amount'),
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Damage Frequency Report (Manager + Accountant) ───────────────────────

    public function damageFrequency(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $cases = FolioCharge::query()
            ->where('is_voided', false)
            ->where('reference_type', 'damage')
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->leftJoin('guest_folios', 'guest_folios.id', '=', 'folio_charges.guest_folio_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'guest_folios.room_id')
            ->leftJoin('reservations', 'reservations.id', '=', 'guest_folios.reservation_id')
            ->leftJoin('guests', 'guests.id', '=', 'reservations.guest_id')
            ->select(
                'rooms.room_number',
                'folio_charges.charge_date',
                'folio_charges.description',
                'folio_charges.total_amount',
                DB::raw("NULLIF(TRIM(CONCAT(COALESCE(guests.first_name, ''), ' ', COALESCE(guests.last_name, ''))), '') as guest_name")
            )
            ->orderByDesc('folio_charges.charge_date')
            ->get()
            ->map(fn($r) => [
                'room_number'  => $r->room_number ?? '—',
                'charge_date'  => $r->charge_date,
                'description'  => $r->description,
                'amount'       => (float) $r->total_amount,
                'guest_name'   => $r->guest_name ?: '—',
            ]);

        $byRoom = $cases
            ->groupBy('room_number')
            ->map(fn($g, $room) => [
                'room_number'   => $room,
                'incident_count'=> (int) $g->count(),
                'total_amount'  => (float) $g->sum('amount'),
                'last_incident' => $g->max('charge_date'),
            ])
            ->sortByDesc('incident_count')
            ->values();

        return Inertia::render('Admin/Reports/DamageFrequency', [
            'user'      => auth()->user()->load('roles'),
            'cases'     => $cases->values(),
            'byRoom'    => $byRoom,
            'totals'    => [
                'count'  => (int) $cases->count(),
                'amount' => (float) $cases->sum('amount'),
            ],
            'startDate' => $start->toDateString(),
            'endDate'   => $end->toDateString(),
            'period'    => $request->get('period', 'monthly'),
            'currency'  => $this->financialService->getCurrency(),
        ]);
    }

    // ─── TRevPAR (Manager) ────────────────────────────────────────────────────

    public function trevpar(Request $request): \Inertia\Response
    {
        [$start, $end] = $this->dateRange($request);

        $totalRooms = Room::where('status', '!=', 'inactive')->count();
        $nights     = max(1, $start->diffInDays($end) + 1);
        $availableRoomNights = $totalRooms * $nights;

        // Total folio revenue (all revenue charges)
        $folioRevenue = FolioCharge::where('is_voided', false)
            ->where('is_revenue', true)
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->sum('net_amount');

        // POS revenue in the same period
        $posRevenue = Sale::where('payment_status', 'paid')
            ->whereBetween('sale_date', [$start, $end])
            ->sum('subtotal');

        $totalRevenue = $folioRevenue + $posRevenue;

        // Room-only revenue for RevPAR comparison
        $roomRevenue = FolioCharge::where('is_voided', false)
            ->where('charge_type', 'room')
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->sum('net_amount');

        // Occupied room-nights
        $occupiedNights = Reservation::whereIn('status', ['checked_in', 'checked_out', 'completed'])
            ->where('check_in_date', '<=', $end->toDateString())
            ->where('check_out_date', '>=', $start->toDateString())
            ->get()
            ->sum(fn($r) => (int) Carbon::parse(max($r->check_in_date, $start->toDateString()))
                ->diffInDays(min($r->check_out_date, $end->copy()->addDay()->toDateString())));

        $occupancy = $availableRoomNights > 0 ? $occupiedNights / $availableRoomNights : 0;
        $adr       = $occupiedNights > 0 ? $roomRevenue / $occupiedNights : 0;
        $revpar    = $availableRoomNights > 0 ? $roomRevenue / $availableRoomNights : 0;
        $trevpar   = $availableRoomNights > 0 ? $totalRevenue / $availableRoomNights : 0;

        // Daily breakdown
        $dailyCharges = FolioCharge::where('is_voided', false)
            ->where('is_revenue', true)
            ->whereBetween('charge_date', [$start->toDateString(), $end->toDateString()])
            ->select(DB::raw('DATE(charge_date) as day'), DB::raw('SUM(net_amount) as folio_revenue'))
            ->groupBy(DB::raw('DATE(charge_date)'))
            ->pluck('folio_revenue', 'day');

        $dailyPos = Sale::where('payment_status', 'paid')
            ->whereBetween('sale_date', [$start, $end])
            ->select(DB::raw('DATE(sale_date) as day'), DB::raw('SUM(subtotal) as pos_revenue'))
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->pluck('pos_revenue', 'day');

        $daily = collect();
        $current = $start->copy();
        while ($current->lte($end)) {
            $d = $current->toDateString();
            $fr = (float) ($dailyCharges->get($d) ?? 0);
            $pr = (float) ($dailyPos->get($d) ?? 0);
            $daily->push([
                'day'           => $d,
                'folio_revenue' => $fr,
                'pos_revenue'   => $pr,
                'total_revenue' => $fr + $pr,
                'trevpar'       => $totalRooms > 0 ? round(($fr + $pr) / $totalRooms, 2) : 0,
            ]);
            $current->addDay();
        }

        return Inertia::render('Manager/Reports/TRevPAR', [
            'user'           => auth()->user()->load('roles'),
            'metrics'        => [
                'total_rooms'          => $totalRooms,
                'available_room_nights'=> $availableRoomNights,
                'occupied_room_nights' => $occupiedNights,
                'occupancy_pct'        => round($occupancy * 100, 2),
                'room_revenue'         => round($roomRevenue, 2),
                'total_revenue'        => round($totalRevenue, 2),
                'folio_revenue'        => round($folioRevenue, 2),
                'pos_revenue'          => round($posRevenue, 2),
                'adr'                  => round($adr, 2),
                'revpar'               => round($revpar, 2),
                'trevpar'              => round($trevpar, 2),
            ],
            'daily'          => $daily,
            'startDate'      => $start->toDateString(),
            'endDate'        => $end->toDateString(),
            'period'         => $request->get('period', 'monthly'),
            'currency'       => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Folio Balance (Front Desk) ───────────────────────────────────────────

    public function folioBalance(Request $request): \Inertia\Response
    {
        $folios = GuestFolio::query()
            ->where('status', 'open')
            ->with([
                'reservation:id,reservation_number,check_in_date,check_out_date,guest_id,room_type_id',
                'reservation.guest:id,first_name,last_name',
                'room:id,room_number',
            ])
            ->select('id', 'folio_number', 'reservation_id', 'room_id', 'balance_amount',
                     'total_amount', 'paid_amount', 'folio_date', 'customer_name')
            ->get()
            ->map(function ($f) {
                $res = $f->reservation;
                $reservationGuest = optional($res)->guest;
                return [
                    'folio_id'       => $f->id,
                    'folio_number'   => $f->folio_number,
                    'guest_name'     => $f->customer_name ?? optional($reservationGuest)->full_name ?? '—',
                    'room_number'    => optional($f->room)->room_number ?? '—',
                    'confirmation'   => optional($res)->reservation_number,
                    'check_in_date'  => optional($res)->check_in_date?->format('Y-m-d'),
                    'check_out_date' => optional($res)->check_out_date?->format('Y-m-d'),
                    'total_charges'  => (float) $f->total_amount,
                    'paid_amount'    => (float) $f->paid_amount,
                    'balance'        => (float) $f->balance_amount,
                ];
            })
            ->sortByDesc('balance')
            ->values();

        $summary = [
            'total_folios'    => (int) $folios->count(),
            'total_charges'   => (float) $folios->sum('total_charges'),
            'total_paid'      => (float) $folios->sum('paid_amount'),
            'total_balance'   => (float) $folios->sum('balance'),
        ];

        return Inertia::render('FrontDesk/Reports/FolioBalance', [
            'user'     => auth()->user()->load('roles'),
            'folios'   => $folios,
            'summary'  => $summary,
            'asOfDate' => Carbon::today()->toDateString(),
            'currency' => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Unposted Charges (Front Desk) ────────────────────────────────────────

    public function unpostedCharges(Request $request): \Inertia\Response
    {
        $today     = Carbon::today();
        $tomorrow  = $today->copy()->addDay();

        // POS sales charged to room but NOT yet in guest folio charges (reference_type='pos_sale')
        $postedSaleIds = FolioCharge::where('reference_type', 'pos_sale')
            ->pluck('reference_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $unposted = Sale::query()
            ->where('is_charged_to_room', true)
            ->whereNotIn('id', $postedSaleIds)
            ->whereNotNull('reservation_id')
            ->with([
                'reservation:id,reservation_number,guest_id,check_out_date,room_id',
                'reservation.guest:id,first_name,last_name',
            ])
            ->select('id', 'sale_number', 'reservation_id', 'customer_name',
                     'total_amount', 'tax_amount', 'tip_amount', 'sale_date', 'payment_status')
            ->orderBy('sale_date')
            ->get()
            ->map(function ($s) use ($today) {
                $checkout = optional($s->reservation)->check_out_date;
                $urgent   = $checkout && Carbon::parse($checkout)->lte($today);
                return [
                    'sale_id'       => $s->id,
                    'sale_number'   => $s->sale_number,
                    'guest_name'    => $s->customer_name ?? optional(optional($s->reservation)->guest)->full_name ?? '—',
                    'confirmation'  => optional($s->reservation)->reservation_number,
                    'check_out_date'=> $checkout ? Carbon::parse($checkout)->toDateString() : null,
                    'sale_date'     => $s->sale_date?->toDateString(),
                    'total'         => (float) $s->total_amount,
                    'payment_status'=> $s->payment_status,
                    'urgent'        => $urgent,
                ];
            });

        $summary = [
            'total_unposted' => (int) $unposted->count(),
            'total_amount'   => (float) $unposted->sum('total'),
            'urgent_count'   => (int) $unposted->where('urgent', true)->count(),
        ];

        return Inertia::render('FrontDesk/Reports/UnpostedCharges', [
            'user'     => auth()->user()->load('roles'),
            'charges'  => $unposted->values(),
            'summary'  => $summary,
            'asOfDate' => $today->toDateString(),
            'currency' => $this->financialService->getCurrency(),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function dateRange(Request $request): array
    {
        $period    = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if ($startDate && $endDate) {
            return [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];
        }

        return match ($period) {
            'daily'     => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'weekly'    => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'quarterly' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'yearly'    => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default     => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function chargeTypeLabel(string $type): string
    {
        return match ($type) {
            'room'           => 'Room Revenue',
            'food_beverage'  => 'Food & Beverage',
            'service_charge' => 'Service Charge',
            'spa'            => 'Spa & Wellness',
            'laundry'        => 'Laundry',
            'damage'         => 'Damage Recovery',
            'incidental'     => 'Incidentals',
            'tax'            => 'Tax',
            default          => 'Other',
        };
    }
}
