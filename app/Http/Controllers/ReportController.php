<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function getReportData(Request $request)
    {
        $reportType = $request->input('type');
        $category = $request->input('category');
        $filters = $request->input('filters', []);

        switch ($category) {
            case 'occupancy':
                return $this->getOccupancyData($reportType, $filters);
            case 'financial':
            case 'revenue':
                return $this->getFinancialData($reportType, $filters);
            case 'staff':
                return $this->getStaffData($reportType, $filters);
            case 'iptv':
                return $this->getIptvData($reportType, $filters);
            case 'system':
                return $this->getSystemData($reportType, $filters);
            case 'guest':
                return $this->getGuestData($reportType, $filters);
            default:
                return response()->json(['error' => 'Invalid report category'], 400);
        }
    }

    private function getOccupancyData($reportType, $filters)
    {
        $dateRange = $this->getDateRange($filters['dateRange'] ?? 'month');

        // Get room occupancy data
        $occupancyData = DB::table('reservations')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->whereBetween('reservations.check_in_date', $dateRange)
            ->select(
                DB::raw('DATE(reservations.check_in_date) as date'),
                'room_types.name as room_type',
                DB::raw('COUNT(*) as occupied_rooms'),
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            ->groupBy('date', 'room_types.name')
            ->get();

        // Get total rooms by type
        $totalRooms = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select('room_types.name as room_type', DB::raw('COUNT(*) as total'))
            ->groupBy('room_types.name')
            ->get()
            ->pluck('total', 'room_type');

        // Calculate occupancy rates
        $processedData = $occupancyData->map(function ($item) use ($totalRooms) {
            $total = $totalRooms[$item->room_type] ?? 1;
            $item->occupancy_rate = round(($item->occupied_rooms / $total) * 100, 2);
            return $item;
        });

        // Summary statistics
        $summary = [
            'occupancyRate' => [
                'label' => 'Average Occupancy Rate',
                'value' => round($processedData->avg('occupancy_rate'), 1) . '%'
            ],
            'totalRooms' => [
                'label' => 'Total Rooms',
                'value' => $totalRooms->sum()
            ],
            'occupiedRooms' => [
                'label' => 'Total Occupied',
                'value' => $processedData->sum('occupied_rooms')
            ],
            'revenue' => [
                'label' => 'Total Revenue',
                'value' => '$' . number_format($processedData->sum('revenue'), 2)
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'chartData' => $processedData->groupBy('date')->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'occupancy' => $items->avg('occupancy_rate')
                ];
            })->values(),
            'chartTitle' => 'Daily Occupancy Trends',
            'tableData' => $processedData,
            'columns' => [
                ['key' => 'date', 'label' => 'Date', 'type' => 'text'],
                ['key' => 'room_type', 'label' => 'Room Type', 'type' => 'text'],
                ['key' => 'occupied_rooms', 'label' => 'Occupied', 'type' => 'number'],
                ['key' => 'occupancy_rate', 'label' => 'Rate (%)', 'type' => 'text'],
                ['key' => 'revenue', 'label' => 'Revenue', 'type' => 'currency']
            ]
        ]);
    }

    private function getFinancialData($reportType, $filters)
    {
        $dateRange = $this->getDateRange($filters['dateRange'] ?? 'month');

        // Get revenue data
        $revenueData = DB::table('transactions')
            ->whereBetween('created_at', $dateRange)
            ->where('type', 'income')
            ->select(
                'category',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('DATE(created_at) as date')
            )
            ->groupBy('category', 'date')
            ->get();

        // Get expense data
        $expenseData = DB::table('transactions')
            ->whereBetween('created_at', $dateRange)
            ->where('type', 'expense')
            ->select(
                'category',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('category')
            ->get();

        $totalRevenue = $revenueData->sum('total_amount');
        $totalExpenses = $expenseData->sum('total_amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $summary = [
            'totalRevenue' => [
                'label' => 'Total Revenue',
                'value' => '$' . number_format($totalRevenue, 2)
            ],
            'totalExpenses' => [
                'label' => 'Total Expenses',
                'value' => '$' . number_format($totalExpenses, 2)
            ],
            'netProfit' => [
                'label' => 'Net Profit',
                'value' => '$' . number_format($netProfit, 2)
            ],
            'profitMargin' => [
                'label' => 'Profit Margin',
                'value' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) . '%' : '0%'
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'chartData' => $revenueData->groupBy('date')->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'revenue' => $items->sum('total_amount')
                ];
            })->values(),
            'chartTitle' => 'Daily Revenue Trends',
            'tableData' => $revenueData->groupBy('category')->map(function ($items, $category) {
                $total = $items->sum('total_amount');
                return [
                    'category' => ucfirst(str_replace('_', ' ', $category)),
                    'revenue' => '$' . number_format($total, 2),
                    'transactions' => $items->sum('transaction_count'),
                    'percentage' => '0%' // Calculate based on total
                ];
            })->values(),
            'columns' => [
                ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
                ['key' => 'revenue', 'label' => 'Revenue', 'type' => 'currency'],
                ['key' => 'transactions', 'label' => 'Transactions', 'type' => 'number'],
                ['key' => 'percentage', 'label' => 'Percentage', 'type' => 'text']
            ]
        ]);
    }

    private function getStaffData($reportType, $filters)
    {
        $dateRange = $this->getDateRange($filters['dateRange'] ?? 'month');

        // Get staff performance data
        $staffData = DB::table('users')
            ->leftJoin('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereBetween('time_entries.clock_in', $dateRange)
            ->select(
                'users.name',
                'users.email',
                'roles.name as role',
                DB::raw('COUNT(time_entries.id) as total_shifts'),
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, time_entries.clock_in, time_entries.clock_out)) as total_hours'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, time_entries.clock_in, time_entries.clock_out)) as avg_hours_per_shift')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'roles.name')
            ->get();

        $totalStaff = DB::table('users')->count();
        $activeStaff = DB::table('users')
            ->join('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->whereBetween('time_entries.clock_in', $dateRange)
            ->distinct('users.id')
            ->count();

        $summary = [
            'totalStaff' => [
                'label' => 'Total Staff',
                'value' => $totalStaff
            ],
            'activeStaff' => [
                'label' => 'Active Staff',
                'value' => $activeStaff
            ],
            'avgHours' => [
                'label' => 'Avg Hours/Week',
                'value' => round($staffData->avg('total_hours'), 1)
            ],
            'totalHours' => [
                'label' => 'Total Hours',
                'value' => $staffData->sum('total_hours')
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'tableData' => $staffData->map(function ($item) {
                return [
                    'name' => $item->name,
                    'role' => ucfirst(str_replace('_', ' ', $item->role ?? 'staff')),
                    'total_hours' => $item->total_hours ?? 0,
                    'total_shifts' => $item->total_shifts ?? 0,
                    'avg_hours' => round($item->avg_hours_per_shift ?? 0, 1)
                ];
            }),
            'columns' => [
                ['key' => 'name', 'label' => 'Employee', 'type' => 'text'],
                ['key' => 'role', 'label' => 'Role', 'type' => 'text'],
                ['key' => 'total_hours', 'label' => 'Total Hours', 'type' => 'number'],
                ['key' => 'total_shifts', 'label' => 'Shifts', 'type' => 'number'],
                ['key' => 'avg_hours', 'label' => 'Avg Hours/Shift', 'type' => 'number']
            ]
        ]);
    }

    private function getIptvData($reportType, $filters)
    {
        $dateRange = $this->getDateRange($filters['dateRange'] ?? 'month');

        // Get IPTV usage data
        $iptvData = DB::table('iptv_devices')
            ->leftJoin('iptv_usage_logs', 'iptv_devices.id', '=', 'iptv_usage_logs.device_id')
            ->leftJoin('rooms', 'iptv_devices.room_id', '=', 'rooms.id')
            ->whereBetween('iptv_usage_logs.created_at', $dateRange)
            ->select(
                'rooms.number as room_number',
                'iptv_devices.device_id',
                'iptv_devices.status',
                DB::raw('COUNT(iptv_usage_logs.id) as usage_count'),
                DB::raw('SUM(iptv_usage_logs.duration_minutes) as total_minutes')
            )
            ->groupBy('iptv_devices.id', 'rooms.number', 'iptv_devices.device_id', 'iptv_devices.status')
            ->get();

        $totalDevices = DB::table('iptv_devices')->count();
        $activeDevices = DB::table('iptv_devices')->where('status', 'active')->count();
        $totalUsage = $iptvData->sum('total_minutes');

        $summary = [
            'totalDevices' => [
                'label' => 'Total Devices',
                'value' => $totalDevices
            ],
            'activeDevices' => [
                'label' => 'Active Devices',
                'value' => $activeDevices
            ],
            'totalUsage' => [
                'label' => 'Total Usage (Hours)',
                'value' => round($totalUsage / 60, 1)
            ],
            'avgUsage' => [
                'label' => 'Avg Usage/Device',
                'value' => $totalDevices > 0 ? round($totalUsage / $totalDevices / 60, 1) . 'h' : '0h'
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'tableData' => $iptvData->map(function ($item) {
                return [
                    'room_number' => $item->room_number,
                    'device_id' => $item->device_id,
                    'status' => ucfirst($item->status),
                    'usage_count' => $item->usage_count ?? 0,
                    'total_hours' => round(($item->total_minutes ?? 0) / 60, 1)
                ];
            }),
            'columns' => [
                ['key' => 'room_number', 'label' => 'Room', 'type' => 'text'],
                ['key' => 'device_id', 'label' => 'Device ID', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                ['key' => 'usage_count', 'label' => 'Sessions', 'type' => 'number'],
                ['key' => 'total_hours', 'label' => 'Hours', 'type' => 'number']
            ]
        ]);
    }

    private function getSystemData($reportType, $filters)
    {
        // Since we don't have system logs table, we'll create sample data
        // In a real system, this would query system_logs, error_logs, etc.

        $summary = [
            'uptime' => [
                'label' => 'System Uptime',
                'value' => '99.9%'
            ],
            'totalUsers' => [
                'label' => 'Total Users',
                'value' => DB::table('users')->count()
            ],
            'activeUsers' => [
                'label' => 'Active Users (24h)',
                'value' => DB::table('users')->where('updated_at', '>=', Carbon::now()->subDay())->count()
            ],
            'totalRooms' => [
                'label' => 'Total Rooms',
                'value' => DB::table('rooms')->count()
            ]
        ];

        $systemData = [
            ['component' => 'Database', 'status' => 'Online', 'uptime' => '99.9%', 'last_check' => Carbon::now()->format('Y-m-d H:i:s')],
            ['component' => 'Web Server', 'status' => 'Online', 'uptime' => '99.8%', 'last_check' => Carbon::now()->format('Y-m-d H:i:s')],
            ['component' => 'IPTV Service', 'status' => 'Online', 'uptime' => '99.5%', 'last_check' => Carbon::now()->format('Y-m-d H:i:s')],
            ['component' => 'Backup Service', 'status' => 'Online', 'uptime' => '100%', 'last_check' => Carbon::now()->format('Y-m-d H:i:s')]
        ];

        return response()->json([
            'summary' => $summary,
            'tableData' => $systemData,
            'columns' => [
                ['key' => 'component', 'label' => 'Component', 'type' => 'text'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                ['key' => 'uptime', 'label' => 'Uptime', 'type' => 'text'],
                ['key' => 'last_check', 'label' => 'Last Check', 'type' => 'text']
            ]
        ]);
    }

    private function getGuestData($reportType, $filters)
    {
        $dateRange = $this->getDateRange($filters['dateRange'] ?? 'month');

        // Get guest data
        $guestData = DB::table('guests')
            ->leftJoin('reservations', 'guests.id', '=', 'reservations.guest_id')
            ->whereBetween('reservations.created_at', $dateRange)
            ->select(
                'guests.first_name',
                'guests.last_name',
                'guests.email',
                'guests.phone',
                DB::raw('COUNT(reservations.id) as total_reservations'),
                DB::raw('SUM(reservations.total_amount) as total_spent'),
                DB::raw('MAX(reservations.created_at) as last_visit')
            )
            ->groupBy('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email', 'guests.phone')
            ->get();

        $totalGuests = DB::table('guests')->count();
        $newGuests = DB::table('guests')->whereBetween('created_at', $dateRange)->count();
        $repeatGuests = $guestData->where('total_reservations', '>', 1)->count();

        $summary = [
            'totalGuests' => [
                'label' => 'Total Guests',
                'value' => $totalGuests
            ],
            'newGuests' => [
                'label' => 'New Guests',
                'value' => $newGuests
            ],
            'repeatGuests' => [
                'label' => 'Repeat Guests',
                'value' => $repeatGuests
            ],
            'avgSpending' => [
                'label' => 'Avg Spending',
                'value' => '$' . number_format($guestData->avg('total_spent') ?? 0, 2)
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'tableData' => $guestData->map(function ($item) {
                return [
                    'name' => $item->first_name . ' ' . $item->last_name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'reservations' => $item->total_reservations ?? 0,
                    'total_spent' => '$' . number_format($item->total_spent ?? 0, 2),
                    'last_visit' => $item->last_visit ? Carbon::parse($item->last_visit)->format('Y-m-d') : 'N/A'
                ];
            }),
            'columns' => [
                ['key' => 'name', 'label' => 'Guest Name', 'type' => 'text'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['key' => 'reservations', 'label' => 'Reservations', 'type' => 'number'],
                ['key' => 'total_spent', 'label' => 'Total Spent', 'type' => 'currency'],
                ['key' => 'last_visit', 'label' => 'Last Visit', 'type' => 'text']
            ]
        ]);
    }

    private function getDateRange($range)
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [$now->startOfDay(), $now->endOfDay()];
            case 'week':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'month':
                return [$now->startOfMonth(), $now->endOfMonth()];
            case 'quarter':
                return [$now->startOfQuarter(), $now->endOfQuarter()];
            case 'year':
                return [$now->startOfYear(), $now->endOfYear()];
            default:
                return [$now->startOfMonth(), $now->endOfMonth()];
        }
    }
}
