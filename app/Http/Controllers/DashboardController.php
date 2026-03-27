<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use App\Models\Reservation;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\TimeEntry;
use App\Models\EmployeeShift;
use App\Models\IptvDevice;
use App\Models\InventoryRequest;
use App\Models\ConciergeRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Ensure user is properly loaded with roles
        if (!$user) {
            return redirect()->route('login');
        }

        // Load roles if not already loaded
        $user->load('roles');

        // Get user's primary role (prefer admin if they have it)
        $roles = $user->roles;
        if ($roles->contains('name', 'admin')) {
            // Redirect admin users to admin dashboard
            return redirect()->route('admin.dashboard');
        } elseif ($roles->contains('name', 'manager')) {
            // Redirect manager users to manager dashboard
            return redirect()->route('manager.dashboard');
        } else {
            $primaryRole = $roles->first();
        }

        $roleName = $primaryRole ? $primaryRole->name : 'staff';

        // Redirect front_desk to dedicated dashboard
        if ($roleName === 'front_desk') {
            return redirect()->route('front-desk.dashboard');
        }

        // Redirect server/restaurant_staff to server dashboard
        if ($roleName === 'server' || $roleName === 'restaurant_staff') {
            return redirect()->route('server.dashboard');
        }

        // Redirect bartender to bartender dashboard
        if ($roleName === 'bartender') {
            return redirect()->route('bartender.dashboard');
        }

        // Get role-specific dashboard data
        $dashboardData = $this->getDashboardDataForRole($user);

        // Route to role-specific dashboard component
        $dashboardComponent = $this->getDashboardComponent($roleName);

        return Inertia::render($dashboardComponent, [
            'user' => $user,
            'navigation' => $this->getNavigationForRole($roleName),
            ...$dashboardData
        ]);
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $user = auth()->user();

        // Ensure user is properly loaded with roles
        if (!$user) {
            return redirect()->route('login');
        }

        // Load roles if not already loaded
        $user->load('roles');

        // Get admin dashboard data
        $dashboardData = $this->getAdminDashboard();

        // Ensure charts prop is always present and structured
        $charts = $dashboardData['charts'] ?? [
            'revenue' => [],
            'occupancy' => []
        ];

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'navigation' => $this->getNavigationForRole('admin'),
            'stats' => $dashboardData['stats'] ?? [],
            'charts' => $charts,
            'recentActivities' => $dashboardData['recentActivities'] ?? [],
            'alerts' => $dashboardData['alerts'] ?? [],
            'systemStatus' => $dashboardData['systemStatus'] ?? [],
            'performanceMetrics' => $dashboardData['performanceMetrics'] ?? [],
        ]);
    }

    /**
     * Manager Dashboard
     */
    public function managerDashboard()
    {
        $user = auth()->user();

        // Ensure user is properly loaded with roles
        if (!$user) {
            return redirect()->route('login');
        }

        // Load roles if not already loaded
        $user->load('roles');

        // Get manager dashboard data
        $dashboardData = $this->getManagerDashboard();

        return Inertia::render('Manager/Dashboard', [
            'user' => $user,
            'navigation' => $this->getNavigationForRole('manager'),
            ...$dashboardData
        ]);
    }

    private function getDashboardComponent($roleName)
    {
        $components = [
            'admin' => 'Admin/Dashboard',
            'manager' => 'Manager/Dashboard',
            'accountant' => 'Accountant/Dashboard',
            'front_desk' => null, // Handled by dedicated controller
            'housekeeping' => 'Housekeeping/Dashboard',
            'maintenance' => 'Maintenance/Dashboard',
            'staff' => 'Staff/Dashboard',
        ];

        return $components[$roleName] ?? 'Staff/Dashboard';
    }

    private function getDashboardDataForRole($user)
    {
        $primaryRole = $user->roles->first();
        $roleName = $primaryRole ? $primaryRole->name : 'staff';

        switch ($roleName) {
            case 'admin':
                return $this->getAdminDashboard();
            case 'manager':
                return $this->getManagerDashboard();
            case 'accountant':
                return $this->getAccountantDashboard();
            case 'front_desk':
                return $this->getFrontDeskDashboard();
            case 'housekeeping':
                return $this->getHousekeepingDashboard();
            case 'maintenance':
                return $this->getMaintenanceDashboard();
            case 'bartender':
            case 'server':
            case 'restaurant_staff':
                return []; // These roles redirect, data comes from their specific controllers
            default:
                return $this->getStaffDashboard($user);
        }
    }

    private function getAdminDashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = $this->countTrulyAvailableRooms();

        // Count current guests (checked-in reservations)
        $totalGuests = Reservation::where('status', 'checked_in')
            ->sum(DB::raw('COALESCE(adults, 1) + COALESCE(children, 0)'));

        // Count total reservations (all active reservations)
        $totalReservations = Reservation::whereIn('status', ['confirmed', 'checked_in', 'pending'])->count();

        // Calculate today's revenue from multiple sources
        // 1. Payments processed today
        $paymentsToday = Payment::whereDate('processed_at', $today)
            ->where('status', 'completed')
            ->sum(DB::raw('COALESCE(local_amount, amount)'));

        // 2. Reservations checked in today (use paid_amount or total_amount)
        $reservationsCheckedInToday = Reservation::whereDate('actual_check_in', $today)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->sum(DB::raw('COALESCE(paid_amount, total_amount, 0)'));

        // 3. Reservations created today with payments
        $reservationsCreatedToday = Reservation::whereDate('created_at', $today)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');

        // Use the highest value from available sources
        $todaysRevenue = max($paymentsToday, $reservationsCheckedInToday, $reservationsCreatedToday);

        // If all are 0, try to get total paid_amount from all active reservations as fallback
        if ($todaysRevenue == 0) {
            $todaysRevenue = Reservation::whereIn('status', ['checked_in', 'checked_out'])
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount');
        }

        $stats = [
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms' => $availableRooms,
            'total_guests' => (int) $totalGuests,
            'total_reservations' => $totalReservations,
            'todays_revenue' => (float) $todaysRevenue,
            'active_users' => User::where('is_active', true)->count(),
        ];

        // Charts data
        $revenueChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Payment::whereDate('processed_at', $date)
                ->where('status', 'completed')
                ->sum(DB::raw('COALESCE(local_amount, amount)'));
            $revenueChart[] = [
                'date' => $date->format('M d'),
                'amount' => (float) $revenue,
            ];
        }

        $occupancyChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $occupied = Reservation::whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date)
                ->whereNotIn('status', ['cancelled'])
                ->count();
            $total = $totalRooms;
            $occupancyChart[] = [
                'date' => $date->format('M d'),
                'rate' => $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
            ];
        }

        // Recent activities
        $recentReservations = Reservation::orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn ($reservation) => [
                'id' => $reservation->id,
                'type' => 'reservation',
                'description' => 'Reservation #' . $reservation->reservation_number,
                'created_at' => $reservation->created_at,
            ]);

        $recentPayments = Payment::orderByDesc('processed_at')
            ->limit(3)
            ->get()
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'type' => 'payment',
                'description' => 'Payment #' . $payment->payment_reference,
                'created_at' => $payment->processed_at ?? $payment->created_at,
            ]);

        $recentActivities = $recentReservations
            ->merge($recentPayments)
            ->sortByDesc('created_at')
            ->values()
            ->take(6);

        // System status
        $systemStatus = [
            'Database' => 'Online',
            'IPTV System' => 'Online',
            'Payment Gateway' => 'Online',
            'Backup System' => 'Online',
        ];

        // Performance metrics
        $avgDailyRate = Reservation::whereDate('check_in_date', '>=', $thisMonth)
            ->avg('room_rate');
        $avgOccupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
        $performanceMetrics = [
            'avgOccupancy' => $avgOccupancy,
            'avgDailyRate' => (float) ($avgDailyRate ?? 0),
            'revPAR' => round(((float) ($avgDailyRate ?? 0)) * ($avgOccupancy / 100), 2),
        ];

        // Alerts
        $onlineDevices = IptvDevice::where('is_active', true)
            ->where(function ($query) {
                $query->where('status', 'online')
                    ->orWhere('last_activity', '>=', Carbon::now()->subMinutes(15));
            })
            ->count();
        $totalDevices = IptvDevice::count();
        $alerts = [
            'maintenance_required' => Room::where('status', 'maintenance')->count(),
            'system_errors' => 0,
            'pending_approvals' => User::where('is_active', false)->count(),
            'offline_devices' => max($totalDevices - $onlineDevices, 0),
        ];

        return [
            'stats' => $stats,
            'charts' => [
                'revenue' => $revenueChart,
                'occupancy' => $occupancyChart,
            ],
            'recentActivities' => $recentActivities,
            'alerts' => $alerts,
            'systemStatus' => $systemStatus,
            'performanceMetrics' => $performanceMetrics,
        ];
    }

    private function getManagerDashboard()
    {
        $today = Carbon::today();
        $startOfToday = $today->copy()->startOfDay();
        $endOfToday = $today->copy()->endOfDay();

        // Today's arrivals - reservations with check_in_date = today and status pending/confirmed
        $todaysArrivals = Reservation::whereDate('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // Today's departures - reservations with check_out_date = today and status checked_in
        $todaysDepartures = Reservation::whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->count();

        // Calculate today's revenue from multiple sources
        // 1. Payments processed today
        $paymentsToday = Payment::whereDate('processed_at', $today)
            ->where('status', 'completed')
            ->sum(DB::raw('COALESCE(local_amount, amount)'));

        // 2. Reservations checked in today (use paid_amount or total_amount)
        $reservationsCheckedInToday = Reservation::whereDate('actual_check_in', $today)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->sum(DB::raw('COALESCE(paid_amount, total_amount, 0)'));

        // 3. Reservations with check_out_date today (departures)
        $departuresToday = Reservation::whereDate('check_out_date', $today)
            ->where('status', 'checked_out')
            ->sum(DB::raw('COALESCE(paid_amount, total_amount, 0)'));

        // Use the highest value from available sources
        $todaysRevenue = max($paymentsToday, $reservationsCheckedInToday, $departuresToday);

        // If all are 0, try to get total from all active reservations as fallback
        if ($todaysRevenue == 0) {
            $todaysRevenue = Reservation::whereIn('status', ['checked_in', 'checked_out'])
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount');
        }

        // Occupancy rate
        $occupancyRate = $this->calculateOccupancyRate();

        // Today's stats for Manager Dashboard
        $todaysStats = [
            'arrivals' => $todaysArrivals,
            'departures' => $todaysDepartures,
            'occupancyRate' => $occupancyRate,
            'revenue' => (float) $todaysRevenue,
        ];

        // Room status
        $roomStatus = [
            'available' => $this->countTrulyAvailableRooms(),
            'occupied' => Room::where('status', 'occupied')->count(),
            'cleaning' => Room::where('housekeeping_status', 'dirty')->orWhere('status', 'cleaning')->count(),
            'maintenance' => Room::where('status', 'maintenance')->orWhere('status', 'out_of_order')->count(),
        ];

        // Staff status - using time entries for accurate tracking
        $now = Carbon::now();

        // Staff who are currently on duty (clocked in today, not clocked out)
        $onDuty = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->where('status', '!=', 'rejected')
            ->distinct('user_id')
            ->count('user_id');

        // Staff who are on break (break started but not ended)
        $onBreak = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->whereNotNull('break_start_time')
            ->whereNull('break_end_time')
            ->where('status', '!=', 'rejected')
            ->distinct('user_id')
            ->count('user_id');

        // Staff who are late today
        $late = TimeEntry::whereDate('work_date', $today)
            ->where('is_late', true)
            ->where('status', '!=', 'rejected')
            ->distinct('user_id')
            ->count('user_id');

        // Staff who were scheduled but didn't clock in (absent)
        // Get all active users with shifts scheduled for today
        $dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $scheduledStaff = EmployeeShift::where('is_active', true)
            ->where('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->whereJsonContains('days_of_week', $dayOfWeek)
            ->pluck('user_id')
            ->unique();

        // Staff who clocked in today
        $clockedInStaff = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->where('status', '!=', 'rejected')
            ->pluck('user_id')
            ->unique();

        // Absent = scheduled but didn't clock in
        $absent = $scheduledStaff->diff($clockedInStaff)->count();

        // Off duty = total active staff minus on duty
        $totalActiveStaff = User::where('is_active', true)->count();
        $offDuty = max(0, $totalActiveStaff - $onDuty);

        $staffStatus = [
            'onDuty' => $onDuty,
            'offDuty' => $offDuty,
            'onBreak' => $onBreak,
            'lateAbsent' => $late + $absent,
        ];

        // Recent check-ins - last 5 check-ins from last 24 hours
        $yesterday = $today->copy()->subDay();
        $recentCheckins = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereNotNull('actual_check_in')
            ->where('actual_check_in', '>=', $yesterday->startOfDay())
            ->orderBy('actual_check_in', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest->full_name ?? 'N/A',
                    'room_number' => $reservation->room->room_number ?? 'N/A',
                    'check_in_time' => $reservation->actual_check_in->toIso8601String(),
                ];
            });

        // Pending tasks - combine housekeeping and maintenance tasks
        $housekeepingTasks = HousekeepingTask::with('room')
            ->where('status', 'pending')
            ->orWhere('status', 'in_progress')
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                $roomNumber = $task->room ? $task->room->room_number : 'N/A';
                return [
                    'id' => 'ht_' . $task->id,
                    'title' => $roomNumber !== 'N/A' ? "Room {$roomNumber} - " . ucfirst(str_replace('_', ' ', $task->task_type)) : ucfirst(str_replace('_', ' ', $task->task_type)),
                    'department' => 'Housekeeping',
                    'priority' => $task->priority,
                    'type' => 'housekeeping',
                ];
            });

        $maintenanceTasks = MaintenanceRequest::with('room')
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                $roomNumber = $task->room ? $task->room->room_number : 'N/A';
                return [
                    'id' => 'mr_' . $task->id,
                    'title' => $roomNumber !== 'N/A' ? "Room {$roomNumber} - {$task->title}" : $task->title,
                    'department' => 'Maintenance',
                    'priority' => $task->priority ?? 'normal',
                    'type' => 'maintenance',
                ];
            });

        $pendingTasks = $housekeepingTasks->merge($maintenanceTasks)->take(5);

        // Weekly occupancy chart - last 7 days
        $occupancyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            // Count rooms that were occupied on this date
            $occupiedOnDate = Reservation::where('status', 'checked_in')
                ->where(function ($query) use ($startOfDay, $endOfDay) {
                    $query->whereBetween('actual_check_in', [$startOfDay, $endOfDay])
                        ->orWhere(function ($q) use ($startOfDay, $endOfDay) {
                            $q->where('check_in_date', '<=', $endOfDay)
                              ->where('check_out_date', '>=', $startOfDay)
                              ->whereIn('status', ['checked_in', 'confirmed']);
                        });
                })
                ->distinct('room_id')
                ->count('room_id');

            $totalRooms = Room::where('is_active', true)->count();
            $occupancyRate = $totalRooms > 0 ? round(($occupiedOnDate / $totalRooms) * 100, 1) : 0;

            $occupancyChart[] = [
                'date' => $date->format('M d'),
                'rate' => $occupancyRate,
            ];
        }

        // Revenue breakdown - from payments and reservations
        // Room revenue from reservations checked in today
        $roomRevenue = Reservation::whereDate('actual_check_in', $today)
            ->where('status', 'checked_in')
            ->sum('total_room_charges');

        // Total payments today (completed status)
        $totalPaymentsToday = Payment::whereDate('processed_at', $today)
            ->where('status', 'completed')
            ->sum('local_amount');

        // Try to get service revenue from reservation services
        $serviceRevenue = DB::table('reservation_services')
            ->join('reservations', 'reservation_services.reservation_id', '=', 'reservations.id')
            ->whereDate('reservations.actual_check_in', $today)
            ->where('reservations.status', 'checked_in')
            ->sum('reservation_services.total_price');

        // Food & Beverage and Other revenue (simplified - would need POS integration)
        // For now, use a portion of total payments as other revenue
        $otherRevenue = max(0, $totalPaymentsToday - $roomRevenue - $serviceRevenue);

        // Build revenue chart
        $revenueChart = [
            ['category' => 'Room Revenue', 'amount' => (float) $roomRevenue],
            ['category' => 'Services', 'amount' => (float) $serviceRevenue],
            ['category' => 'Other', 'amount' => (float) $otherRevenue],
        ];

        // Remove categories with zero amounts for cleaner display
        $revenueChart = array_filter($revenueChart, function($item) {
            return $item['amount'] > 0;
        });
        $revenueChart = array_values($revenueChart); // Re-index array

        // If no revenue data, show empty chart
        if (empty($revenueChart)) {
            $revenueChart = [
                ['category' => 'Room Revenue', 'amount' => 0],
                ['category' => 'Services', 'amount' => 0],
                ['category' => 'Other', 'amount' => 0],
            ];
        }

        return [
            'todaysStats' => $todaysStats,
            'roomStatus' => $roomStatus,
            'staffStatus' => $staffStatus,
            'recentCheckins' => $recentCheckins,
            'pendingTasks' => $pendingTasks,
            'charts' => [
                'occupancy' => $occupancyChart,
                'revenue' => $revenueChart,
            ],
        ];
    }

    private function calculateOccupancyRate()
    {
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        return $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
    }

    /**
     * Count rooms that are genuinely available to sell today.
     * Physical 'available' status minus API bookings that haven't been assigned a room yet.
     */
    private function countTrulyAvailableRooms(): int
    {
        $today = Carbon::today();
        $physicallyAvailable = Room::where('status', 'available')->count();
        $apiBookedWithoutRoom = Reservation::whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNull('room_id')
            ->count();
        return max(0, $physicallyAvailable - $apiBookedWithoutRoom);
    }

    private function getAccountantDashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $todaysRevenue = Payment::where('status', 'completed')
            ->whereDate('processed_at', $today)
            ->sum(DB::raw('COALESCE(local_amount, amount)'));

        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereBetween('processed_at', [$thisMonth, $monthEnd])
            ->sum(DB::raw('COALESCE(local_amount, amount)'));

        $monthlyExpenses = Expense::whereBetween('expense_date', [$thisMonth, $monthEnd])
            ->where('status', '!=', 'rejected')
            ->sum('amount');

        $netProfit = $monthlyRevenue - $monthlyExpenses;

        // Financial summary
        $financialSummary = [
            'todaysRevenue' => (float) $todaysRevenue,
            'monthlyRevenue' => (float) $monthlyRevenue,
            'monthlyExpenses' => (float) $monthlyExpenses,
            'netProfit' => (float) $netProfit,
        ];

        // Recent transactions
        $recentPayments = Payment::where('status', 'completed')
            ->orderByDesc('processed_at')
            ->limit(5)
            ->get()
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'type' => 'income',
                'description' => 'Payment ' . ($payment->payment_number ?? $payment->transaction_id ?? ('#' . $payment->id)),
                'amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
                'date' => $payment->processed_at ?? $payment->created_at,
            ])
            ->values();

        $recentExpenses = Expense::where('status', '!=', 'rejected')
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get()
            ->map(fn ($expense) => [
                'id' => $expense->id,
                'type' => 'expense',
                'description' => 'Expense ' . ($expense->expense_number ?? ('#' . $expense->id)),
                'amount' => (float) $expense->amount,
                'date' => $expense->expense_date ?? $expense->created_at,
            ])
            ->values();

        $recentTransactions = collect($recentPayments)
            ->merge($recentExpenses)
            ->sortByDesc('date')
            ->values()
            ->take(8);

        // Pending payments
        $pendingPayments = Expense::whereIn('status', ['pending', 'approved'])
            ->orderBy('expense_date')
            ->limit(6)
            ->get()
            ->map(fn ($expense) => [
                'id' => $expense->id,
                'description' => $expense->vendor_name
                    ? $expense->vendor_name . ' - ' . $expense->description
                    : $expense->description,
                'amount' => (float) $expense->amount,
                'due_date' => $expense->expense_date ?? $expense->created_at,
            ]);

        // Charts data
        $revenueExpenseChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $monthRevenue = Payment::where('status', 'completed')
                ->whereBetween('processed_at', [$monthStart, $monthEnd])
                ->sum(DB::raw('COALESCE(local_amount, amount)'));
            $monthExpenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->where('status', '!=', 'rejected')
                ->sum('amount');
            $revenueExpenseChart[] = [
                'month' => $month->format('M Y'),
                'revenue' => (float) $monthRevenue,
                'expenses' => (float) $monthExpenses,
            ];
        }

        $expenseChart = ExpenseCategory::withSum([
            'expenses as total_amount' => function ($query) use ($thisMonth, $monthEnd) {
                $query->whereBetween('expense_date', [$thisMonth, $monthEnd])
                    ->where('status', '!=', 'rejected');
            }
        ], 'amount')
            ->get()
            ->map(fn ($category) => [
                'category' => $category->name,
                'amount' => (float) ($category->total_amount ?? 0),
            ])
            ->filter(fn ($row) => $row['amount'] > 0)
            ->values()
            ->all();

        // Financial metrics
        $daysSoFar = max($today->day, 1);
        $avgDailyRevenue = $monthlyRevenue > 0 ? ($monthlyRevenue / $daysSoFar) : 0;
        $profitMargin = $monthlyRevenue > 0 ? round(($netProfit / $monthlyRevenue) * 100, 1) : 0;
        $expenseRatio = $monthlyRevenue > 0 ? round(($monthlyExpenses / $monthlyRevenue) * 100, 1) : 0;
        $metrics = [
            'avgDailyRevenue' => (float) $avgDailyRevenue,
            'profitMargin' => $profitMargin,
            'expenseRatio' => $expenseRatio,
            'cashFlow' => (float) $netProfit,
        ];

        return [
            'financialSummary' => $financialSummary,
            'recentTransactions' => $recentTransactions,
            'pendingPayments' => $pendingPayments,
            'charts' => [
                'revenueExpense' => $revenueExpenseChart,
                'expenses' => $expenseChart,
            ],
            'metrics' => $metrics,
        ];
    }

    private function getFrontDeskDashboard()
    {
        $today = Carbon::today();

        // Today's activities
        $arrivalsCount = Reservation::whereDate('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        $departuresCount = Reservation::whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->count();
        $currentGuests = Reservation::where('status', 'checked_in')->count();
        $todaysActivities = [
            'arrivals' => $arrivalsCount,
            'departures' => $departuresCount,
            'currentGuests' => $currentGuests,
            'availableRooms' => $this->countTrulyAvailableRooms(),
        ];

        // Arrivals list
        $arrivals = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->orderBy('check_in_date')
            ->limit(6)
            ->get()
            ->map(function ($reservation) {
                $expectedArrival = $reservation->preferred_check_in_time
                    ? Carbon::parse($reservation->check_in_date->format('Y-m-d') . ' ' . $reservation->preferred_check_in_time)
                    : $reservation->check_in_date;
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest?->full_name ?? 'Guest',
                    'room_number' => $reservation->room?->room_number ?? 'TBD',
                    'room_type' => $reservation->roomType?->name ?? 'N/A',
                    'expected_arrival' => $expectedArrival,
                    'checked_in' => $reservation->status === 'checked_in' || !empty($reservation->actual_check_in),
                ];
            });

        // Departures list
        $departures = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', $today)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->orderBy('check_out_date')
            ->limit(6)
            ->get()
            ->map(function ($reservation) {
                $expectedDeparture = $reservation->preferred_check_out_time
                    ? Carbon::parse($reservation->check_out_date->format('Y-m-d') . ' ' . $reservation->preferred_check_out_time)
                    : $reservation->check_out_date;
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest?->full_name ?? 'Guest',
                    'room_number' => $reservation->room?->room_number ?? 'TBD',
                    'room_type' => $reservation->roomType?->name ?? 'N/A',
                    'expected_departure' => $expectedDeparture,
                    'checked_out' => $reservation->status === 'checked_out' || !empty($reservation->actual_check_out),
                ];
            });

        // Room status
        $roomStatus = [
            'available' => $this->countTrulyAvailableRooms(),
            'occupied' => Room::where('status', 'occupied')->count(),
            'cleaning' => Room::where('housekeeping_status', 'dirty')->count(), // Dirty rooms need cleaning
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];

        // Guest requests
        $conciergeRequests = ConciergeRequest::orderByDesc('requested_at')
            ->limit(5)
            ->get()
            ->map(fn ($request) => [
                'id' => 'concierge-' . $request->id,
                'type' => 'concierge',
                'description' => $request->details ?: ($request->service_type ?? 'Concierge Request'),
                'room_number' => $request->room_number ?? 'N/A',
                'status' => $request->status ?? 'pending',
                'created_at' => $request->requested_at ?? $request->created_at,
            ]);

        $maintenanceRequests = MaintenanceRequest::with('room')
            ->orderByDesc('reported_at')
            ->limit(5)
            ->get()
            ->map(fn ($request) => [
                'id' => 'maintenance-' . $request->id,
                'type' => 'maintenance',
                'description' => $request->title ?? $request->category ?? 'Maintenance Request',
                'room_number' => $request->room?->room_number ?? 'N/A',
                'status' => $request->status ?? 'pending',
                'created_at' => $request->reported_at ?? $request->created_at,
            ]);

        $housekeepingRequests = HousekeepingTask::with('room')
            ->orderByDesc('scheduled_date')
            ->limit(5)
            ->get()
            ->map(fn ($task) => [
                'id' => 'housekeeping-' . $task->id,
                'type' => 'housekeeping',
                'description' => $task->task_type ?? 'Housekeeping Request',
                'room_number' => $task->room?->room_number ?? 'N/A',
                'status' => $task->status ?? 'pending',
                'created_at' => $task->scheduled_date ?? $task->created_at,
            ]);

        $guestRequests = $conciergeRequests
            ->merge($maintenanceRequests)
            ->merge($housekeepingRequests)
            ->sortByDesc('created_at')
            ->values()
            ->take(8);

        return [
            'todaysActivities' => $todaysActivities,
            'arrivals' => $arrivals,
            'departures' => $departures,
            'roomStatus' => $roomStatus,
            'guestRequests' => $guestRequests,
        ];
    }

    private function getHousekeepingDashboard()
    {
        $user = auth()->user();

        // Room statistics
        $roomStats = [
            'toClean' => Room::where('housekeeping_status', 'dirty')->count(),
            'inProgress' => Room::where('housekeeping_status', 'cleaning')->count(),
            'completed' => Room::where('housekeeping_status', 'clean')->whereDate('last_cleaned_at', Carbon::today())->count(),
            'maintenance' => Room::where('housekeeping_status', 'maintenance_required')->count(),
        ];

        // My room assignments
        $myRooms = HousekeepingTask::with('room')
            ->where('assigned_to', $user?->id)
            ->orderByDesc('scheduled_date')
            ->limit(6)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'number' => $task->room?->room_number ?? 'N/A',
                    'type' => $task->room?->roomType?->name ?? $task->task_type ?? 'Room Task',
                    'status' => $task->status ?? 'assigned',
                    'priority' => $task->priority ?? 'normal',
                ];
            });

        // Daily tasks
        $dailyTasks = HousekeepingTask::with('room')
            ->whereDate('scheduled_date', Carbon::today())
            ->where('assigned_to', $user?->id)
            ->orderBy('scheduled_time')
            ->limit(6)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'description' => $task->task_type ?? 'Housekeeping Task',
                    'location' => $task->room?->room_number ? 'Room ' . $task->room->room_number : 'N/A',
                    'completed' => in_array($task->status, ['completed', 'inspected']),
                ];
            });

        // Inventory status
        $inventory = InventoryRequest::where('department', 'housekeeping')
            ->orderByDesc('requested_at')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'category' => ucfirst($item->category),
                    'stock' => $item->quantity,
                    'min_stock' => 0,
                    'unit' => 'pcs',
                ];
            });

        // Maintenance reports
        $maintenanceReports = MaintenanceRequest::with('room')
            ->where('reported_by', $user?->id)
            ->orderByDesc('reported_at')
            ->limit(6)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'issue' => $request->title ?? $request->category ?? 'Maintenance Issue',
                    'room_number' => $request->room?->room_number ?? 'N/A',
                    'status' => $request->status,
                    'reported_at' => $request->reported_at ?? $request->created_at,
                ];
            });

        return [
            'roomStats' => $roomStats,
            'myRooms' => $myRooms,
            'dailyTasks' => $dailyTasks,
            'inventory' => $inventory,
            'maintenanceReports' => $maintenanceReports,
        ];
    }

    private function getMaintenanceDashboard()
    {
        $today = Carbon::today();
        $user = auth()->user();

        $workOrderStats = [
            'open' => MaintenanceRequest::whereIn('status', ['open', 'assigned'])->count(),
            'inProgress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'completedToday' => MaintenanceRequest::whereDate('resolved_at', $today)->count(),
        ];

        $onlineDevices = IptvDevice::where('is_active', true)
            ->where(function ($query) {
                $query->where('status', 'online')
                    ->orWhere('last_activity', '>=', Carbon::now()->subMinutes(15));
            })
            ->count();

        $iptvStats = [
            'onlineDevices' => $onlineDevices,
            'totalDevices' => IptvDevice::count(),
        ];

        $myWorkOrders = MaintenanceRequest::with('room')
            ->where('assigned_to', $user?->id)
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->orderByDesc('reported_at')
            ->limit(6)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title ?? $request->category ?? 'Maintenance Request',
                    'location' => $request->room?->room_number ? 'Room ' . $request->room->room_number : ($request->location ?? 'N/A'),
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'type' => $request->category ?? 'general',
                ];
            });

        $iptvDevices = IptvDevice::with('room')
            ->orderByDesc('last_activity')
            ->limit(6)
            ->get()
            ->map(function ($device) {
                $lastActivity = $device->last_activity;
                $online = $device->is_active && (
                    $device->status === 'online'
                    || ($lastActivity && $lastActivity->gte(Carbon::now()->subMinutes(15)))
                );

                return [
                    'id' => $device->id,
                    'room_number' => $device->room?->room_number ?? 'N/A',
                    'device_type' => $device->device_type ?? $device->device_name,
                    'ip_address' => $device->ip_address,
                    'online' => $online,
                    'last_seen' => $lastActivity?->diffForHumans() ?? 'N/A',
                ];
            });

        $scheduledTasks = MaintenanceRequest::whereNotNull('scheduled_date')
            ->orderBy('scheduled_date')
            ->limit(6)
            ->get()
            ->map(function ($request) use ($today) {
                $status = 'pending';
                if (in_array($request->status, ['resolved', 'closed'])) {
                    $status = 'completed';
                } elseif ($request->scheduled_date && $request->scheduled_date->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $request->id,
                    'description' => $request->title ?? $request->category ?? 'Scheduled Maintenance',
                    'equipment' => $request->category ? ucfirst($request->category) : 'General',
                    'due_date' => $request->scheduled_date,
                    'status' => $status,
                ];
            });

        $inventory = InventoryRequest::where('department', 'maintenance')
            ->orderByDesc('requested_at')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'category' => ucfirst($item->category),
                    'quantity' => $item->quantity,
                    'min_quantity' => 0,
                ];
            });

        return [
            'workOrderStats' => $workOrderStats,
            'iptvStats' => $iptvStats,
            'myWorkOrders' => $myWorkOrders,
            'iptvDevices' => $iptvDevices,
            'scheduledTasks' => $scheduledTasks,
            'inventory' => $inventory,
        ];
    }

    private function getStaffDashboard($user)
    {
        $today = Carbon::today();
        $todayEntries = TimeEntry::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderByDesc('clock_in_time')
            ->get();
        $latestEntry = $todayEntries->first();
        $hoursWorked = (float) $todayEntries->sum('total_hours');
        $breakHours = (float) $todayEntries->sum('break_hours');
        $isClocked = $latestEntry && $latestEntry->clock_in_time && !$latestEntry->clock_out_time;

        // Time tracking status
        $timeStatus = [
            'isClocked' => $isClocked,
            'clockInTime' => $latestEntry?->clock_in_time?->format('H:i') ?? null,
            'hoursWorked' => $hoursWorked,
            'breakTime' => (int) round($breakHours * 60),
            'netHours' => max($hoursWorked - $breakHours, 0),
        ];

        // Weekly schedule
        $activeShift = EmployeeShift::with('workShift')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('effective_date')
            ->first();

        $weeklySchedule = collect();
        $startOfWeek = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayIndex = (int) $date->format('N');
            $isScheduled = $activeShift && in_array($dayIndex, $activeShift->days_of_week ?? [], true);
            $shift = $activeShift?->workShift;
            $weeklySchedule->push([
                'date' => $date,
                'dayName' => $date->format('l'),
                'isToday' => $date->isToday(),
                'startTime' => $isScheduled ? ($shift?->start_time?->format('H:i') ?? 'N/A') : 'Off',
                'endTime' => $isScheduled ? ($shift?->end_time?->format('H:i') ?? 'N/A') : 'Off',
                'shiftName' => $isScheduled ? ($shift?->name ?? 'Shift') : 'Off',
            ]);
        }

        // Assigned tasks
        $assignedTasks = collect();

        // Recent messages
        $recentMessages = collect();

        // Announcements
        $announcements = collect();

        return [
            'timeStatus' => $timeStatus,
            'weeklySchedule' => $weeklySchedule,
            'assignedTasks' => $assignedTasks,
            'recentMessages' => $recentMessages,
            'announcements' => $announcements,
        ];
    }

    public function getNavigationForRole($role)
    {
        // Dashboard href depends on role
        $dashboardHref = $role === 'admin' ? '/admin/dashboard' : '/dashboard';

        $baseNavigation = [
            [
                'name' => 'Dashboard',
                'href' => $dashboardHref,
                'icon' => 'HomeIcon',
                'current' => false
            ]
        ];

        $roleSpecificNavigation = [
            'admin' => [
                [
                    'name' => 'User Management',
                    'icon' => 'UserGroupIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Users', 'href' => '/admin/users', 'icon' => 'UsersIcon'],
                        ['name' => 'Roles & Permissions', 'href' => '/admin/roles', 'icon' => 'ShieldCheckIcon'],
                        ['name' => 'Add User', 'href' => '/admin/users/create', 'icon' => 'UserPlusIcon']
                    ]
                ],
                [
                    'name' => 'Property Management',
                    'icon' => 'BuildingOfficeIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Room Types',    'href' => '/admin/room-types',    'icon' => 'HomeIcon'],
                        ['name' => 'Rooms',          'href' => '/admin/rooms',          'icon' => 'HomeIcon'],
                        ['name' => 'Room Amenities', 'href' => '/admin/room-amenities', 'icon' => 'HomeIcon'],
                        ['name' => 'Halls',          'href' => '/admin/halls',          'icon' => 'BuildingOfficeIcon'],
                        ['name' => 'Hall Bookings',  'href' => '/admin/hall-bookings',  'icon' => 'CalendarDaysIcon'],
                        ['name' => 'Reservations',   'href' => '/admin/reservations',   'icon' => 'CalendarDaysIcon'],
                        ['name' => 'Guests',         'href' => '/admin/guests',         'icon' => 'UsersIcon'],
                        ['name' => "Today's Guests", 'href' => '/admin/checkin/today-guests',  'icon' => 'UserGroupIcon'],
                        ['name' => 'Police Report',  'href' => '/admin/checkin/police-report', 'icon' => 'DocumentTextIcon'],
                    ]
                ],
                [
                    'name' => 'Financial Management',
                    'icon' => 'CurrencyDollarIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Transactions',       'href' => '/admin/transactions',          'icon' => 'CurrencyDollarIcon'],
                        ['name' => 'Expenses',           'href' => '/admin/expenses',              'icon' => 'BanknotesIcon'],
                        ['name' => 'Expense Categories', 'href' => '/admin/expenses/categories',   'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'Payroll',            'href' => '/admin/payroll',               'icon' => 'CreditCardIcon'],
                        ['name' => 'Reports',            'href' => '/admin/financial-reports',     'icon' => 'ChartBarIcon']
                    ]
                ],
                [
                    'name' => 'Budget Management',
                    'icon' => 'ChartBarIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Budget Dashboard',  'href' => '/admin/budget/dashboard',                  'icon' => 'HomeIcon'],
                        ['name' => 'All Budgets',       'href' => '/admin/budget',                            'icon' => 'ChartBarIcon'],
                        ['name' => 'Create Budget',     'href' => '/admin/budget/create',                     'icon' => 'PlusIcon'],
                        ['name' => 'Budget Reports',    'href' => '/admin/budget/reports',                    'icon' => 'DocumentTextIcon'],
                        ['name' => 'Budget Expenses',   'href' => '/admin/budget/expenses',                   'icon' => 'BanknotesIcon'],
                        ['name' => 'Expense Approvals', 'href' => '/admin/budget/expenses/pending-approvals', 'icon' => 'CheckCircleIcon'],
                        ['name' => 'Budget Alerts',     'href' => '/admin/budget/alerts',                     'icon' => 'BellIcon']
                    ]
                ],
                [
                    'name' => 'POS Management',
                    'icon' => 'ReceiptPercentIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'POS Terminal', 'href' => '/pos', 'icon' => 'CalculatorIcon'],
                        ['name' => 'Products', 'href' => '/admin/pos/products', 'icon' => 'ArchiveBoxIcon'],
                        ['name' => 'Categories', 'href' => '/admin/pos/categories', 'icon' => 'ArchiveBoxIcon'],
                        ['name' => 'Inventory', 'href' => '/pos/inventory', 'icon' => 'ArchiveBoxIcon'],
                        ['name' => 'Stock Batches', 'href' => '/pos/stock-batches', 'icon' => 'CubeIcon']
                    ]
                ],
                [
                    'name' => 'Purchasing & Suppliers',
                    'icon' => 'TruckIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Suppliers', 'href' => '/pos/suppliers', 'icon' => 'TruckIcon'],
                        ['name' => 'Purchase Orders', 'href' => '/pos/purchases', 'icon' => 'ShoppingCartIcon']
                    ]
                ],
                [
                    'name' => 'Sales Management',
                    'icon' => 'DocumentTextIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Sales', 'href' => '/pos/sales', 'icon' => 'DocumentTextIcon'],
                        ['name' => 'Sales Report', 'href' => '/pos/sales/report', 'icon' => 'ChartBarIcon'],
                        ['name' => 'POS Reports', 'href' => '/pos/reports', 'icon' => 'ChartBarIcon']
                    ]
                ],
                [
                    'name' => 'Customer Management',
                    'icon' => 'UserGroupIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Customers', 'href' => '/admin/customers', 'icon' => 'UsersIcon'],
                        ['name' => 'Add Customer', 'href' => '/admin/customers/create', 'icon' => 'UserPlusIcon'],
                        ['name' => 'Customer Groups', 'href' => '/admin/customer-groups', 'icon' => 'UserGroupIcon']
                    ]
                ],
                [
                    'name' => 'IPTV Management',
                    'icon' => 'TvIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Channels', 'href' => '/admin/iptv/channels', 'icon' => 'TvIcon'],
                        ['name' => 'Packages', 'href' => '/admin/iptv/packages', 'icon' => 'TvIcon'],
                        ['name' => 'Devices', 'href' => '/admin/iptv/devices', 'icon' => 'TvIcon'],
                        ['name' => 'Content', 'href' => '/admin/iptv/content', 'icon' => 'TvIcon']
                    ]
                ],
                [
                    'name' => 'Staff Management',
                    'icon' => 'ClockIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Time Tracking', 'href' => '/admin/time-tracking', 'icon' => 'ClockIcon'],
                        ['name' => 'Work Shifts', 'href' => '/admin/work-shifts', 'icon' => 'ClockIcon'],
                        ['name' => 'Schedules', 'href' => '/admin/schedules', 'icon' => 'ClockIcon']
                    ]
                ],
                [
                    'name' => 'Reports & Analytics',
                    'icon' => 'ChartBarIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Dashboard Analytics', 'href' => '/admin/analytics', 'icon' => 'ChartBarIcon'],
                        ['name' => 'Occupancy Reports', 'href' => '/admin/reports/occupancy', 'icon' => 'ChartBarIcon'],
                        ['name' => 'Revenue Reports', 'href' => '/admin/reports/revenue', 'icon' => 'ChartBarIcon'],
                        ['name' => 'Staff Reports', 'href' => '/admin/reports/staff', 'icon' => 'ChartBarIcon']
                    ]
                ],
                [
                    'name' => 'System Settings',
                    'icon' => 'Cog6ToothIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'General Settings', 'href' => '/admin/settings', 'icon' => 'Cog6ToothIcon'],
                        ['name' => 'Room Service Charge', 'href' => '/admin/room-service-settings', 'icon' => 'SparklesIcon'],
                        ['name' => 'Email Settings', 'href' => '/admin/settings/email', 'icon' => 'Cog6ToothIcon'],
                        ['name' => 'Backup & Restore', 'href' => '/admin/settings/backup', 'icon' => 'Cog6ToothIcon'],
                        ['name' => 'System Logs', 'href' => '/admin/settings/logs', 'icon' => 'Cog6ToothIcon']
                    ]
                ],
                [
                    'name' => 'Hotel Services',
                    'icon' => 'SparklesIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Services', 'href' => '/admin/services', 'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'Concierge',    'href' => '/admin/services/concierge', 'icon' => 'BellIcon'],
                    ]
                ],
                [
                    'name' => 'Maintenance',
                    'icon' => 'WrenchScrewdriverIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Requests',   'href' => '/admin/maintenance-requests',          'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'New Request',    'href' => '/admin/maintenance-requests/create',   'icon' => 'PlusIcon'],
                        ['name' => 'All Categories', 'href' => '/admin/maintenance-categories',        'icon' => 'TagIcon'],
                        ['name' => 'New Category',   'href' => '/admin/maintenance-categories/create', 'icon' => 'PlusIcon'],
                    ]
                ],
                [
                    'name' => 'Key Cards',
                    'icon' => 'CreditCardIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Key Cards',    'href' => '/front-desk/key-cards',            'icon' => 'CreditCardIcon'],
                        ['name' => 'Assignment Center','href' => '/front-desk/key-cards/assignment', 'icon' => 'KeyIcon'],
                        ['name' => 'Add Key Card',     'href' => '/front-desk/key-cards/create',     'icon' => 'PlusIcon'],
                    ]
                ]
            ],
            'manager' => [
                [
                    'name' => 'Operations',
                    'icon' => 'CogIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Overview',          'href' => '/manager/operations',              'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'All Reservations',  'href' => '/manager/reservations',            'icon' => 'CalendarDaysIcon'],
                        ['name' => 'Check-ins Today',   'href' => '/manager/reservations/checkins',   'icon' => 'ArrowRightOnRectangleIcon'],
                        ['name' => 'Check-outs Today',  'href' => '/manager/reservations/checkouts',  'icon' => 'ArrowLeftOnRectangleIcon'],
                        ['name' => 'New Reservation',   'href' => '/manager/reservations/create',     'icon' => 'PlusIcon'],
                        ['name' => 'Check In',         'href' => '/manager/checkin',                       'icon' => 'ArrowRightOnRectangleIcon'],
                        ['name' => 'Check Out',        'href' => '/manager/checkout',                      'icon' => 'ArrowLeftOnRectangleIcon'],
                        ['name' => "Today's Guests",  'href' => '/manager/checkin/today-guests',          'icon' => 'UserGroupIcon'],
                        ['name' => 'Police Report',    'href' => '/manager/checkin/police-report',         'icon' => 'DocumentTextIcon'],
                        ['name' => 'Waitlist',         'href' => '/manager/waitlist',                      'icon' => 'QueueListIcon'],
                        ['name' => 'Group Bookings',    'href' => '/manager/group-bookings',          'icon' => 'UserGroupIcon'],
                        ['name' => 'Channel Manager',   'href' => '/manager/channel-manager',         'icon' => 'GlobeAltIcon'],
                    ]
                ],
                [
                    'name' => 'Guests',
                    'icon' => 'UserGroupIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Guests',        'href' => '/manager/guests',          'icon' => 'UsersIcon'],
                        ['name' => 'Current Guests',    'href' => '/manager/guests/current',  'icon' => 'UserIcon'],
                        ['name' => 'Guest History',     'href' => '/manager/guests/history',  'icon' => 'ClockIcon'],
                        ['name' => 'Add Guest',         'href' => '/manager/guests/create',   'icon' => 'UserPlusIcon'],
                        ['name' => 'Guest Types',       'href' => '/manager/guest-types',     'icon' => 'TagIcon'],
                    ]
                ],
                [
                    'name' => 'Property',
                    'icon' => 'BuildingOfficeIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Rooms',         'href' => '/manager/rooms',               'icon' => 'HomeIcon'],
                        ['name' => 'Room Status',       'href' => '/manager/rooms/status',        'icon' => 'ChartBarIcon'],
                        ['name' => 'Housekeeping Rooms','href' => '/manager/rooms/housekeeping',  'icon' => 'SparklesIcon'],
                        ['name' => 'Maintenance Rooms', 'href' => '/manager/rooms/maintenance',   'icon' => 'WrenchScrewdriverIcon'],
                        ['name' => 'Room Types',        'href' => '/manager/room-types',          'icon' => 'Squares2X2Icon'],
                        ['name' => 'Room Amenities',    'href' => '/manager/room-amenities',      'icon' => 'StarIcon'],
                        ['name' => 'Halls',             'href' => '/manager/halls',               'icon' => 'BuildingOfficeIcon'],
                        ['name' => 'Hall Bookings',     'href' => '/manager/hall-bookings',       'icon' => 'CalendarDaysIcon'],
                    ]
                ],
                [
                    'name' => 'Housekeeping',
                    'icon' => 'SparklesIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Tasks',          'href' => '/manager/housekeeping-tasks', 'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'Create Task',    'href' => '/manager/housekeeping-tasks/create', 'icon' => 'PlusIcon'],
                    ]
                ],
                [
                    'name' => 'Maintenance',
                    'icon' => 'WrenchScrewdriverIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Requests',    'href' => '/manager/maintenance-requests',          'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'New Request',     'href' => '/manager/maintenance-requests/create',   'icon' => 'PlusIcon'],
                        ['name' => 'All Categories',  'href' => '/manager/maintenance-categories',        'icon' => 'TagIcon'],
                        ['name' => 'New Category',    'href' => '/manager/maintenance-categories/create', 'icon' => 'PlusIcon'],
                    ]
                ],
                [
                    'name' => 'Key Cards',
                    'icon' => 'CreditCardIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Key Cards',    'href' => '/front-desk/key-cards',            'icon' => 'CreditCardIcon'],
                        ['name' => 'Assignment Center','href' => '/front-desk/key-cards/assignment', 'icon' => 'KeyIcon'],
                        ['name' => 'Add Key Card',     'href' => '/front-desk/key-cards/create',     'icon' => 'PlusIcon'],
                    ]
                ],
                [
                    'name' => 'Hotel Services',
                    'icon' => 'SparklesIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Services', 'href' => '/manager/services', 'icon' => 'ClipboardDocumentListIcon'],
                        ['name' => 'Concierge',    'href' => '/manager/services/concierge', 'icon' => 'BellIcon'],
                        ['name' => 'Room Service Charge', 'href' => '/manager/room-service-settings', 'icon' => 'SparklesIcon'],
                    ]
                ],
                [
                    'name' => 'Settings',
                    'icon' => 'Cog6ToothIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'General Settings', 'href' => '/manager/settings', 'icon' => 'Cog6ToothIcon'],
                        ['name' => 'Room Service Charge', 'href' => '/manager/room-service-settings', 'icon' => 'SparklesIcon'],
                    ]
                ],
                [
                    'name' => 'POS & Sales',
                    'icon' => 'ReceiptPercentIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'POS Terminal',      'href' => '/pos',               'icon' => 'CalculatorIcon'],
                        ['name' => 'Sales',             'href' => '/pos/sales',         'icon' => 'DocumentTextIcon'],
                        ['name' => 'Inventory',         'href' => '/pos/inventory',     'icon' => 'ArchiveBoxIcon'],
                        ['name' => 'Stock Batches',     'href' => '/pos/stock-batches', 'icon' => 'CubeIcon'],
                        ['name' => 'Suppliers',         'href' => '/pos/suppliers',     'icon' => 'TruckIcon'],
                        ['name' => 'Purchase Orders',   'href' => '/pos/purchases',     'icon' => 'ShoppingCartIcon'],
                    ]
                ],
                [
                    'name' => 'Customers',
                    'icon' => 'UserGroupIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Customers',     'href' => '/manager/customers',         'icon' => 'UsersIcon'],
                        ['name' => 'Add Customer',      'href' => '/manager/customers/create',  'icon' => 'UserPlusIcon'],
                        ['name' => 'Customer Groups',   'href' => '/manager/customer-groups',   'icon' => 'UserGroupIcon'],
                    ]
                ],
                [
                    'name' => 'Expenses',
                    'icon' => 'BanknotesIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Expenses',      'href' => '/manager/expenses',                          'icon' => 'BanknotesIcon'],
                        ['name' => 'Create Expense',    'href' => '/manager/expenses/create',                   'icon' => 'PlusIcon'],
                        ['name' => 'Categories',        'href' => '/manager/expenses/categories',               'icon' => 'TagIcon'],
                    ]
                ],
                [
                    'name' => 'Budget Management',
                    'icon' => 'ChartBarIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Budget Dashboard',  'href' => '/manager/budget/dashboard',                    'icon' => 'HomeIcon'],
                        ['name' => 'All Budgets',       'href' => '/manager/budget',                              'icon' => 'ChartBarIcon'],
                        ['name' => 'Create Budget',     'href' => '/manager/budget/create',                      'icon' => 'PlusIcon'],
                        ['name' => 'Budget Reports',    'href' => '/manager/budget/reports',                     'icon' => 'DocumentTextIcon'],
                        ['name' => 'Budget Expenses',   'href' => '/manager/budget/expenses',                    'icon' => 'BanknotesIcon'],
                        ['name' => 'Expense Approvals', 'href' => '/manager/budget/expenses/pending-approvals',  'icon' => 'CheckCircleIcon'],
                    ]
                ],
                [
                    'name' => 'Staff',
                    'icon' => 'UsersIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Staff Overview',    'href' => '/manager/staff',                 'icon' => 'UsersIcon'],
                        ['name' => 'Schedules',         'href' => '/manager/staff/schedules',       'icon' => 'CalendarDaysIcon'],
                        ['name' => 'Time Tracking',     'href' => '/manager/staff/time-tracking',   'icon' => 'ClockIcon'],
                        ['name' => 'Performance',       'href' => '/manager/staff/performance',     'icon' => 'ChartBarIcon'],
                    ]
                ],
                [
                    'name' => 'Reports',
                    'icon' => 'ClipboardDocumentListIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Overview',          'href' => '/manager/reports',           'icon' => 'HomeIcon'],
                        ['name' => 'Revenue',           'href' => '/manager/reports/revenue',   'icon' => 'CurrencyDollarIcon'],
                        ['name' => 'Occupancy',         'href' => '/manager/reports/occupancy', 'icon' => 'BuildingOfficeIcon'],
                        ['name' => 'Staff Reports',     'href' => '/manager/reports/staff',     'icon' => 'UsersIcon'],
                    ]
                ],
            ],
            'accountant' => [
                [
                    'name' => 'Transactions',
                    'href' => '/accountant/transactions',
                    'icon' => 'CurrencyDollarIcon',
                    'current' => false
                ],
                [
                    'name' => 'Reports',
                    'href' => '/accountant/reports',
                    'icon' => 'DocumentTextIcon',
                    'current' => false
                ],
                [
                    'name' => 'Payments',
                    'href' => '/accountant/payments',
                    'icon' => 'CreditCardIcon',
                    'current' => false
                ]
            ],
            'front_desk' => [
                [
                    'name' => 'Check In',
                    'href' => '/front-desk/checkin',
                    'icon' => 'ArrowRightOnRectangleIcon',
                    'current' => false
                ],
                [
                    'name' => 'Check Out',
                    'href' => '/front-desk/checkout',
                    'icon' => 'ArrowLeftOnRectangleIcon',
                    'current' => false
                ],
                [
                    'name' => 'New Reservation',
                    'href' => '/front-desk/reservations/create',
                    'icon' => 'CalendarDaysIcon',
                    'current' => false
                ],
                [
                    'name' => 'Process Payments',
                    'href' => '/front-desk/payments/process',
                    'icon' => 'CreditCardIcon',
                    'current' => false
                ],
                [
                    'name' => 'My Transactions',
                    'href' => '/front-desk/transactions',
                    'icon' => 'CurrencyDollarIcon',
                    'current' => false
                ],
                [
                    'name' => 'Customer Management',
                    'icon' => 'UserGroupIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Customers', 'href' => '/front-desk/customers', 'icon' => 'UsersIcon'],
                        ['name' => 'Add Customer', 'href' => '/front-desk/customers/create', 'icon' => 'UserPlusIcon'],
                        ['name' => 'Customer Groups', 'href' => '/admin/customer-groups', 'icon' => 'UserGroupIcon']
                    ]
                ],
                [
                    'name' => 'Services & Requests',
                    'icon' => 'BellIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'Concierge',    'href' => '/front-desk/services/concierge',    'icon' => 'BellIcon'],
                        ['name' => 'Hall Bookings','href' => '/front-desk/services/hall-bookings', 'icon' => 'BuildingOfficeIcon'],
                        ['name' => 'Housekeeping', 'href' => '/front-desk/services/housekeeping', 'icon' => 'SparklesIcon'],
                        ['name' => 'Maintenance',  'href' => '/front-desk/services/maintenance',  'icon' => 'WrenchScrewdriverIcon'],
                    ]
                ],
                [
                    'name' => 'Expenses',
                    'icon' => 'BanknotesIcon',
                    'current' => false,
                    'children' => [
                        ['name' => 'All Expenses',    'href' => '/front-desk/expenses',        'icon' => 'DocumentTextIcon'],
                        ['name' => 'Record Expense',  'href' => '/front-desk/expenses/create', 'icon' => 'PlusCircleIcon'],
                    ]
                ]
            ],
            'housekeeping' => [
                [
                    'name' => 'Time Tracking',
                    'href' => '/housekeeping/time-tracking',
                    'icon' => 'ClockIcon',
                    'current' => false
                ],
                [
                    'name' => 'Inventory Request',
                    'href' => '/housekeeping/inventory/request',
                    'icon' => 'ArchiveBoxIcon',
                    'current' => false
                ],
                [
                    'name' => 'Maintenance Report',
                    'href' => '/housekeeping/maintenance/report',
                    'icon' => 'WrenchScrewdriverIcon',
                    'current' => false
                ]
            ],
            'maintenance' => [
                [
                    'name' => 'Time Tracking',
                    'href' => '/maintenance/time-tracking',
                    'icon' => 'ClockIcon',
                    'current' => false
                ],
                [
                    'name' => 'Inventory Request',
                    'href' => '/maintenance/inventory/request',
                    'icon' => 'ArchiveBoxIcon',
                    'current' => false
                ]
            ],
            'staff' => [
                [
                    'name' => 'Clock In/Out',
                    'href' => '/staff/time-tracking/clock',
                    'icon' => 'ClockIcon',
                    'current' => false
                ],
                [
                    'name' => 'Timesheet',
                    'href' => '/staff/time-tracking/timesheet',
                    'icon' => 'DocumentTextIcon',
                    'current' => false
                ],
                [
                    'name' => 'Assigned Tasks',
                    'href' => '/staff/tasks/assigned',
                    'icon' => 'ClipboardDocumentListIcon',
                    'current' => false
                ],
                [
                    'name' => 'Profile',
                    'href' => '/staff/profile',
                    'icon' => 'UserIcon',
                    'current' => false
                ],
                [
                    'name' => 'Messages',
                    'href' => '/staff/messages',
                    'icon' => 'ChatBubbleLeftRightIcon',
                    'current' => false
                ]
            ]
        ];

        return array_merge($baseNavigation, $roleSpecificNavigation[$role] ?? []);
    }
}
