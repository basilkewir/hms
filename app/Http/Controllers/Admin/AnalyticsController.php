<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $data = $this->buildAnalyticsData();

        return Inertia::render('Admin/Analytics/Index', [
            'user' => auth()->user()->load('roles'),
            ...$data,
        ]);
    }

    public function export()
    {
        $data = $this->buildAnalyticsData();

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['Analytics Summary']);
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Occupancy Rate', $data['analytics']['occupancyRate'] . '%']);
            fputcsv($handle, ['Revenue', $data['analytics']['revenue']]);
            fputcsv($handle, ['Guest Satisfaction', $data['analytics']['guestSatisfaction']]);
            fputcsv($handle, ['ADR', $data['analytics']['adr']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Revenue Breakdown']);
            fputcsv($handle, ['Category', 'Amount']);
            foreach ($data['charts']['revenue'] as $row) {
                fputcsv($handle, [$row['category'], $row['amount']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Occupancy Trend']);
            fputcsv($handle, ['Date', 'Rate']);
            foreach ($data['charts']['occupancy'] as $row) {
                fputcsv($handle, [$row['date'], $row['rate']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Top Rooms']);
            fputcsv($handle, ['Room', 'Type', 'Revenue', 'Occupancy']);
            foreach ($data['topRooms'] as $room) {
                fputcsv($handle, [$room['number'], $room['type'], $room['revenue'], $room['occupancy']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Guest Demographics']);
            fputcsv($handle, ['Category', 'Percentage']);
            foreach ($data['guestDemographics'] as $row) {
                fputcsv($handle, [$row['category'], $row['percentage']]);
            }

            fclose($handle);
        }, 'analytics-report.csv');
    }

    private function buildAnalyticsData(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Calculate real occupancy rate
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Calculate revenue
        $monthlyRevenue = Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount');

        // Calculate ADR (Average Daily Rate)
        $reservationsThisMonth = Reservation::where('created_at', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->get();
        $totalRoomCharges = $reservationsThisMonth->sum('total_room_charges');
        $totalNights = $reservationsThisMonth->sum('nights');
        $adr = $totalNights > 0 ? round($totalRoomCharges / $totalNights, 2) : 0;

        // Calculate RevPAR
        $availableRoomNights = $totalRooms * $thisMonth->daysInMonth;
        $revpar = $availableRoomNights > 0 ? round($totalRoomCharges / $availableRoomNights, 2) : 0;

        // Occupancy chart data (last 7 days)
        $occupancyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $occupied = Room::where('status', 'occupied')
                ->whereHas('reservations', function ($query) use ($date) {
                    $query->whereDate('check_in_date', '<=', $date)
                        ->whereDate('check_out_date', '>', $date);
                })
                ->count();
            $rate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0;
            $occupancyChart[] = [
                'date' => $date->format('M d'),
                'rate' => $rate,
            ];
        }

        // Revenue by category
        $revenueChart = [
            ['category' => 'Room Revenue', 'amount' => $reservationsThisMonth->sum('total_room_charges')],
            ['category' => 'Food & Beverage', 'amount' => Sale::where('sale_date', '>=', $thisMonth)->whereHas('items.product.category', function ($q) {
                $q->whereIn('name', ['Food', 'Beverage']);
            })->sum('total_amount')],
            ['category' => 'Other Services', 'amount' => Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount') - $reservationsThisMonth->sum('total_room_charges')],
        ];

        // Top performing rooms
        $topRooms = Room::with(['reservations' => function ($query) use ($thisMonth) {
            $query->where('created_at', '>=', $thisMonth);
        }])
            ->get()
            ->map(function ($room) {
                $revenue = $room->reservations->sum('total_room_charges');
                $occupancy = $room->reservations->count() > 0 ? 100 : 0;
                return [
                    'id' => $room->id,
                    'number' => $room->room_number,
                    'type' => $room->roomType?->name ?? 'N/A',
                    'revenue' => $revenue,
                    'occupancy' => $occupancy,
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // Guest demographics (by booking source)
        $bookingSources = Reservation::where('created_at', '>=', $thisMonth)
            ->select('booking_source', DB::raw('count(*) as count'))
            ->groupBy('booking_source')
            ->get();

        $totalBookings = $bookingSources->sum('count');
        $guestDemographics = $bookingSources->map(function ($source) use ($totalBookings) {
            $category = ucfirst(str_replace('_', ' ', $source->booking_source));
            return [
                'category' => $category,
                'percentage' => $totalBookings > 0 ? round(($source->count / $totalBookings) * 100, 1) : 0,
            ];
        });

        // Recent activity (real reservations)
        $recentActivity = Reservation::with(['guest', 'room'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'description' => "Reservation {$reservation->reservation_number} - {$reservation->guest->full_name}",
                    'timestamp' => $reservation->created_at,
                ];
            });

        // Detailed metrics
        $lastMonthReservations = Reservation::whereBetween('created_at', [$lastMonth, $thisMonth])
            ->where('status', '!=', 'cancelled')
            ->get();
        $lastMonthRoomCharges = $lastMonthReservations->sum('total_room_charges');
        $lastMonthNights = $lastMonthReservations->sum('nights');
        $lastMonthAdr = $lastMonthNights > 0 ? round($lastMonthRoomCharges / $lastMonthNights, 2) : 0;
        $adrChange = $lastMonthAdr > 0 ? round((($adr - $lastMonthAdr) / $lastMonthAdr) * 100, 1) : 0;

        $lastMonthAvailableNights = $totalRooms * $lastMonth->daysInMonth;
        $lastMonthRevpar = $lastMonthAvailableNights > 0 ? round($lastMonthRoomCharges / $lastMonthAvailableNights, 2) : 0;
        $revparChange = $lastMonthRevpar > 0 ? round((($revpar - $lastMonthRevpar) / $lastMonthRevpar) * 100, 1) : 0;

        return [
            'analytics' => [
                'occupancyRate' => $occupancyRate,
                'revenue' => $monthlyRevenue,
                'guestSatisfaction' => 4.6,
                'adr' => $adr,
            ],
            'topRooms' => $topRooms,
            'guestDemographics' => $guestDemographics,
            'recentActivity' => $recentActivity,
            'detailedMetrics' => [
                [
                    'name' => 'Average Daily Rate (ADR)',
                    'current' => $adr,
                    'previous' => $lastMonthAdr,
                    'change' => $adrChange,
                    'trend' => $adrChange >= 0 ? 'Increasing' : 'Decreasing',
                ],
                [
                    'name' => 'Revenue Per Available Room (RevPAR)',
                    'current' => $revpar,
                    'previous' => $lastMonthRevpar,
                    'change' => $revparChange,
                    'trend' => $revparChange >= 0 ? 'Increasing' : 'Decreasing',
                ],
                [
                    'name' => 'Guest Satisfaction Score',
                    'current' => '4.6/5',
                    'previous' => '4.3/5',
                    'change' => 7.0,
                    'trend' => 'Improving',
                ],
                [
                    'name' => 'Average Length of Stay',
                    'current' => $reservationsThisMonth->count() > 0 ? round($reservationsThisMonth->avg('nights'), 1) . ' days' : '0 days',
                    'previous' => $lastMonthReservations->count() > 0 ? round($lastMonthReservations->avg('nights'), 1) . ' days' : '0 days',
                    'change' => 0,
                    'trend' => 'Stable',
                ],
            ],
            'charts' => [
                'occupancy' => $occupancyChart,
                'revenue' => $revenueChart,
            ],
        ];
    }
}
