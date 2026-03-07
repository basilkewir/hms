<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\EmployeeShift;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Sale;
use App\Models\Reservation;
use App\Models\GroupBooking;
use App\Models\PerformanceReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get performance overview stats
        $performanceStats = $this->getPerformanceStats();

        // Get recent performance reviews (using time tracking data as proxy)
        $recentReviews = $this->getRecentReviews();

        // Get employee performance data
        $employees = $this->getEmployeePerformance();

        return inertia('Manager/Staff/Performance', [
            'user' => $user,
            'performanceStats' => $performanceStats,
            'recentReviews' => $recentReviews,
            'employees' => $employees,
        ]);
    }

    public function export()
    {
        $employees = $this->getEmployeePerformance();
        $dateStamp = now()->format('Y-m-d_His');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=performance_report_{$dateStamp}.csv",
        ];

        $callback = function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee Name',
                'Employee ID',
                'Department',
                'Performance Score',
                'Attendance Rate',
                'Last Review',
            ]);

            foreach ($employees as $employee) {
                fputcsv($handle, [
                    $employee['name'],
                    $employee['employee_id'],
                    $employee['department'],
                    $employee['performance_score'],
                    $employee['attendance_rate'],
                    $employee['last_review'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function scheduleReview(Request $request, User $user)
    {
        $validated = $request->validate([
            'scheduled_for' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        PerformanceReview::create([
            'user_id' => $user->id,
            'reviewer_id' => Auth::id(),
            'scheduled_for' => $validated['scheduled_for'] ?? now()->addDays(7),
            'status' => 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Performance review scheduled successfully.');
    }

    private function getPerformanceStats()
    {
        $today = Carbon::today();
        $users = User::whereHas('roles', function($query) {
            $query->where('name', '!=', 'admin');
        })->get();

        $totalEmployees = $users->count();
        $highPerformers = 0;
        $needsImprovement = 0;
        $totalScore = 0;

        foreach ($users as $user) {
            $score = $this->calculatePerformanceScore($user);
            $totalScore += $score;

            if ($score >= 8) {
                $highPerformers++;
            } elseif ($score < 6) {
                $needsImprovement++;
            }
        }

        $averageScore = $totalEmployees > 0 ? round($totalScore / $totalEmployees, 1) : 0;

        return [
            'averageScore' => $averageScore,
            'highPerformers' => $highPerformers,
            'needsImprovement' => $needsImprovement,
            'employeeSatisfaction' => 4.3, // Placeholder for now
        ];
    }

    private function getRecentReviews()
    {
        $users = User::whereHas('roles', function($query) {
            $query->where('name', '!=', 'admin');
        })->limit(3)->get();

        return $users->map(function($user) {
            $score = $this->calculatePerformanceScore($user);
            $feedback = $this->generateFeedback($score, $user->department);

            return [
                'id' => $user->id,
                'employee_name' => $user->name,
                'position' => $user->position ?? 'Staff',
                'score' => $score,
                'feedback' => $feedback,
            ];
        });
    }

    private function getEmployeePerformance()
    {
        $users = User::whereHas('roles', function($query) {
            $query->where('name', '!=', 'admin');
        })->get();

        return $users->map(function($user) {
            $score = $this->calculatePerformanceScore($user);
            $attendanceRate = $this->calculateAttendanceRate($user);
            $lastReview = $this->getLastReviewDate($user);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'employee_id' => $user->employee_id ?? 'EMP' . $user->id,
                'department' => $user->department ?? 'General',
                'performance_score' => $score,
                'attendance_rate' => $attendanceRate,
                'last_review' => $lastReview,
            ];
        })->sortByDesc('performance_score')->values();
    }

    private function calculatePerformanceScore(User $user)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Calculate different performance metrics based on department
        $department = $user->department ?? 'general';

        // Base scores (40% weight)
        $attendanceScore = $this->calculateAttendanceScore($user, $thisMonth);
        $punctualityScore = $this->calculatePunctualityScore($user, $thisMonth);

        // Task-based performance (30% weight) - varies by department
        $taskScore = $this->calculateTaskPerformanceScore($user, $thisMonth, $department);

        // Sales/Revenue performance (30% weight) - for sales and front desk
        $salesScore = $this->calculateSalesPerformanceScore($user, $thisMonth, $department);

        // Weighted average based on department
        $totalScore = $this->calculateDepartmentWeightedScore($attendanceScore, $punctualityScore, $taskScore, $salesScore, $department);

        return round($totalScore, 1);
    }

    private function calculateTaskPerformanceScore(User $user, $startDate, $department)
    {
        switch ($department) {
            case 'housekeeping':
                return $this->calculateHousekeepingPerformance($user, $startDate);
            case 'maintenance':
                return $this->calculateMaintenancePerformance($user, $startDate);
            case 'front_office':
                return $this->calculateFrontOfficePerformance($user, $startDate);
            case 'food_beverage':
                return $this->calculateFoodBeveragePerformance($user, $startDate);
            case 'sales':
                return $this->calculateSalesTaskPerformance($user, $startDate);
            default:
                return $this->calculateGeneralTaskPerformance($user, $startDate);
        }
    }

    private function calculateSalesPerformanceScore(User $user, $startDate, $department)
    {
        // Only calculate sales score for relevant departments
        if (!in_array($department, ['sales', 'front_office', 'food_beverage'])) {
            return 5.0; // Neutral score for non-sales roles
        }

        // Calculate sales/revenue generated by this employee
        $salesRevenue = $this->calculateSalesRevenue($user, $startDate);
        $targetRevenue = $this->getDepartmentSalesTarget($department);

        if ($targetRevenue == 0) return 5.0;

        $salesRate = ($salesRevenue / $targetRevenue) * 100;

        // Convert to 10-point scale with sales-specific thresholds
        if ($salesRate >= 120) return 10.0;
        if ($salesRate >= 100) return 9.0;
        if ($salesRate >= 90) return 8.0;
        if ($salesRate >= 80) return 7.0;
        if ($salesRate >= 70) return 6.0;
        if ($salesRate >= 60) return 5.0;
        if ($salesRate >= 50) return 4.0;
        if ($salesRate >= 40) return 3.0;
        if ($salesRate >= 30) return 2.0;
        return 1.0;
    }

    private function calculateHousekeepingPerformance(User $user, $startDate)
    {
        // Calculate housekeeping task completion rate
        $completedTasks = \App\Models\HousekeepingTask::where('assigned_to', $user->id)
            ->whereDate('completed_at', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        $totalTasks = \App\Models\HousekeepingTask::where('assigned_to', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->count();

        if ($totalTasks == 0) return 5.0;

        $completionRate = ($completedTasks / $totalTasks) * 100;

        // Housekeeping-specific performance thresholds
        if ($completionRate >= 95) return 10.0;
        if ($completionRate >= 90) return 9.0;
        if ($completionRate >= 85) return 8.0;
        if ($completionRate >= 80) return 7.0;
        if ($completionRate >= 75) return 6.0;
        if ($completionRate >= 70) return 5.0;
        if ($completionRate >= 65) return 4.0;
        if ($completionRate >= 60) return 3.0;
        if ($completionRate >= 50) return 2.0;
        return 1.0;
    }

    private function calculateMaintenancePerformance(User $user, $startDate)
    {
        // Calculate maintenance task completion rate and response time
        $completedRequests = \App\Models\MaintenanceRequest::where('assigned_to', $user->id)
            ->whereDate('completed_at', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        $totalRequests = \App\Models\MaintenanceRequest::where('assigned_to', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->count();

        if ($totalRequests == 0) return 5.0;

        $completionRate = ($completedRequests / $totalRequests) * 100;

        // Calculate average response time (time from assignment to completion)
        $avgResponseTime = \App\Models\MaintenanceRequest::where('assigned_to', $user->id)
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', $startDate)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, completed_at)'));

        $responseScore = $this->calculateResponseTimeScore($avgResponseTime);

        // Weighted average for maintenance: 60% completion rate, 40% response time
        return ($completionRate * 0.6) + ($responseScore * 0.4);
    }

    private function calculateFrontOfficePerformance(User $user, $startDate)
    {
        // Calculate front office performance based on check-ins, check-outs, and customer satisfaction
        $checkins = \App\Models\Reservation::where('created_by', $user->id)
            ->whereDate('check_in_date', '>=', $startDate)
            ->count();

        $checkouts = \App\Models\Reservation::where('created_by', $user->id)
            ->whereDate('check_out_date', '>=', $startDate)
            ->count();

        // Calculate POS sales for front desk staff
        $posSales = \App\Models\Sale::where('user_id', $user->id)
            ->whereDate('sale_date', '>=', $startDate)
            ->sum('total_amount');

        // Calculate room assignments and billing accuracy
        $roomAssignments = \App\Models\Reservation::where('assigned_by', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->count();

        // Performance calculation for front office
        $transactionScore = min(($checkins + $checkouts + $roomAssignments) * 2, 10);
        $salesScore = min(($posSales / 1000) * 2, 10); // Normalize sales to 10-point scale

        return ($transactionScore * 0.6) + ($salesScore * 0.4);
    }

    private function calculateFoodBeveragePerformance(User $user, $startDate)
    {
        // Calculate food and beverage sales performance
        $sales = \App\Models\Sale::where('user_id', $user->id)
            ->whereDate('sale_date', '>=', $startDate)
            ->sum('total_amount');

        $saleCount = \App\Models\Sale::where('user_id', $user->id)
            ->whereDate('sale_date', '>=', $startDate)
            ->count();

        // Calculate average sale value
        $avgSaleValue = $saleCount > 0 ? $sales / $saleCount : 0;

        // Food & beverage performance: 70% sales volume, 30% transaction count
        $salesScore = min(($sales / 5000) * 10, 10); // Normalize to 10-point scale
        $volumeScore = min(($saleCount / 50) * 10, 10); // Normalize to 10-point scale

        return ($salesScore * 0.7) + ($volumeScore * 0.3);
    }

    private function calculateSalesTaskPerformance(User $user, $startDate)
    {
        // Calculate sales performance based on bookings and revenue
        $bookings = \App\Models\Reservation::where('created_by', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->count();

        $bookingRevenue = \App\Models\Reservation::where('created_by', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->sum('total_amount');

        $groupBookings = \App\Models\GroupBooking::where('created_by', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->count();

        // Sales performance: 60% revenue, 30% booking count, 10% group bookings
        $revenueScore = min(($bookingRevenue / 10000) * 10, 10);
        $bookingScore = min(($bookings / 20) * 10, 10);
        $groupScore = min(($groupBookings / 5) * 10, 10);

        return ($revenueScore * 0.6) + ($bookingScore * 0.3) + ($groupScore * 0.1);
    }

    private function calculateGeneralTaskPerformance(User $user, $startDate)
    {
        // General task performance for other departments
        // This could be extended based on specific task tracking systems

        // For now, return a base score based on time tracking
        return $this->calculateConsistencyScore($user, $startDate);
    }

    private function calculateResponseTimeScore($avgHours)
    {
        if ($avgHours === null) return 5.0;
        if ($avgHours <= 2) return 10.0;
        if ($avgHours <= 4) return 9.0;
        if ($avgHours <= 8) return 8.0;
        if ($avgHours <= 16) return 7.0;
        if ($avgHours <= 24) return 6.0;
        if ($avgHours <= 48) return 5.0;
        if ($avgHours <= 72) return 4.0;
        if ($avgHours <= 96) return 3.0;
        if ($avgHours <= 120) return 2.0;
        return 1.0;
    }

    private function calculateSalesRevenue(User $user, $startDate)
    {
        // Calculate total revenue generated by this user
        $posSales = \App\Models\Sale::where('user_id', $user->id)
            ->whereDate('sale_date', '>=', $startDate)
            ->sum('total_amount');

        $reservationRevenue = \App\Models\Reservation::where('created_by', $user->id)
            ->whereDate('created_at', '>=', $startDate)
            ->sum('total_amount');

        return $posSales + $reservationRevenue;
    }

    private function getDepartmentSalesTarget($department)
    {
        // Define sales targets by department (could be made configurable)
        $targets = [
            'sales' => 50000,
            'front_office' => 25000,
            'food_beverage' => 15000,
        ];

        return $targets[$department] ?? 0;
    }

    private function calculateDepartmentWeightedScore($attendanceScore, $punctualityScore, $taskScore, $salesScore, $department)
    {
        switch ($department) {
            case 'sales':
                // Sales: 20% attendance, 20% punctuality, 30% tasks, 30% sales
                return ($attendanceScore * 0.2) + ($punctualityScore * 0.2) + ($taskScore * 0.3) + ($salesScore * 0.3);

            case 'front_office':
                // Front Office: 25% attendance, 25% punctuality, 30% tasks, 20% sales
                return ($attendanceScore * 0.25) + ($punctualityScore * 0.25) + ($taskScore * 0.3) + ($salesScore * 0.2);

            case 'food_beverage':
                // Food & Beverage: 30% attendance, 30% punctuality, 20% tasks, 20% sales
                return ($attendanceScore * 0.3) + ($punctualityScore * 0.3) + ($taskScore * 0.2) + ($salesScore * 0.2);

            case 'housekeeping':
                // Housekeeping: 40% attendance, 30% punctuality, 30% tasks, 0% sales
                return ($attendanceScore * 0.4) + ($punctualityScore * 0.3) + ($taskScore * 0.3);

            case 'maintenance':
                // Maintenance: 40% attendance, 30% punctuality, 30% tasks, 0% sales
                return ($attendanceScore * 0.4) + ($punctualityScore * 0.3) + ($taskScore * 0.3);

            default:
                // General staff: 40% attendance, 30% punctuality, 30% tasks
                return ($attendanceScore * 0.4) + ($punctualityScore * 0.3) + ($taskScore * 0.3);
        }
    }

    private function calculateAttendanceScore(User $user, $startDate)
    {
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereDate('work_date', '>=', $startDate)
            ->get();

        if ($timeEntries->isEmpty()) {
            return 5.0; // Neutral score for new employees
        }

        $totalDays = $startDate->diffInDays(Carbon::now());
        $presentDays = $timeEntries->whereNotNull('clock_in_time')->count();

        if ($totalDays == 0) return 10.0;

        $attendanceRate = ($presentDays / $totalDays) * 100;

        // Convert to 10-point scale
        if ($attendanceRate >= 95) return 10.0;
        if ($attendanceRate >= 90) return 9.0;
        if ($attendanceRate >= 85) return 8.0;
        if ($attendanceRate >= 80) return 7.0;
        if ($attendanceRate >= 75) return 6.0;
        if ($attendanceRate >= 70) return 5.0;
        if ($attendanceRate >= 65) return 4.0;
        if ($attendanceRate >= 60) return 3.0;
        if ($attendanceRate >= 50) return 2.0;
        return 1.0;
    }

    private function calculatePunctualityScore(User $user, $startDate)
    {
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereDate('work_date', '>=', $startDate)
            ->whereNotNull('clock_in_time')
            ->get();

        if ($timeEntries->isEmpty()) {
            return 5.0; // Neutral score for new employees
        }

        $lateCount = $timeEntries->where('is_late', true)->count();
        $totalCount = $timeEntries->count();

        $punctualityRate = (($totalCount - $lateCount) / $totalCount) * 100;

        // Convert to 10-point scale
        if ($punctualityRate >= 95) return 10.0;
        if ($punctualityRate >= 90) return 9.0;
        if ($punctualityRate >= 85) return 8.0;
        if ($punctualityRate >= 80) return 7.0;
        if ($punctualityRate >= 75) return 6.0;
        if ($punctualityRate >= 70) return 5.0;
        if ($punctualityRate >= 65) return 4.0;
        if ($punctualityRate >= 60) return 3.0;
        if ($punctualityRate >= 50) return 2.0;
        return 1.0;
    }

    private function calculateConsistencyScore(User $user, $startDate)
    {
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereDate('work_date', '>=', $startDate)
            ->whereNotNull('clock_in_time')
            ->get();

        if ($timeEntries->isEmpty()) {
            return 5.0; // Neutral score for new employees
        }

        // Calculate average hours worked per day
        $totalHours = $timeEntries->sum('total_hours');
        $workDays = $timeEntries->count();
        $avgHours = $workDays > 0 ? $totalHours / $workDays : 0;

        // Consistency based on hours worked (assuming 8 hours is standard)
        $consistencyRate = min(($avgHours / 8) * 100, 100);

        // Convert to 10-point scale
        if ($consistencyRate >= 95) return 10.0;
        if ($consistencyRate >= 90) return 9.0;
        if ($consistencyRate >= 85) return 8.0;
        if ($consistencyRate >= 80) return 7.0;
        if ($consistencyRate >= 75) return 6.0;
        if ($consistencyRate >= 70) return 5.0;
        if ($consistencyRate >= 65) return 4.0;
        if ($consistencyRate >= 60) return 3.0;
        if ($consistencyRate >= 50) return 2.0;
        return 1.0;
    }

    private function calculateAttendanceRate(User $user)
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereDate('work_date', '>=', $thisMonth)
            ->get();

        if ($timeEntries->isEmpty()) {
            return 0;
        }

        $totalDays = $thisMonth->diffInDays(Carbon::now());
        $presentDays = $timeEntries->whereNotNull('clock_in_time')->count();

        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
    }

    private function getLastReviewDate(User $user)
    {
        // Use the latest time entry date as proxy for last review
        $latestEntry = TimeEntry::where('user_id', $user->id)
            ->latest('work_date')
            ->first();

        return $latestEntry ? $latestEntry->work_date->format('Y-m-d') : 'N/A';
    }

    private function generateFeedback($score, $department)
    {
        if ($score >= 9) {
            return "Excellent performance with consistent attendance and punctuality. Strong contribution to the {$department} department.";
        } elseif ($score >= 8) {
            return "Very good performance with reliable attendance. Continue maintaining high standards in the {$department} department.";
        } elseif ($score >= 7) {
            return "Good performance overall. Some areas for improvement in consistency and punctuality.";
        } elseif ($score >= 6) {
            return "Satisfactory performance. Focus on improving attendance and punctuality for better results.";
        } else {
            return "Performance needs improvement. Immediate attention required for attendance and work consistency.";
        }
    }
}
