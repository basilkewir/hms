<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        if ($request->has(['month', 'year'])) {
            try {
                // Handle both month name and number
                $month = $request->month;
                if (!is_numeric($month)) {
                    $month = Carbon::parse($month)->month;
                }
                $date = Carbon::createFromDate($request->year, $month, 1);
                $periodStart = $date->copy()->startOfMonth();
                $periodEnd = $date->copy()->endOfMonth();
            } catch (\Exception $e) {
                // Fallback to current month if date is invalid
                $periodStart = Carbon::now()->startOfMonth();
                $periodEnd = Carbon::now()->endOfMonth();
            }
        } else {
            $periodStart = Carbon::now()->startOfMonth();
            $periodEnd = Carbon::now()->endOfMonth();
        }

        $employees = User::active()
            ->with(['department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $entriesByUser = TimeEntry::whereBetween('work_date', [$periodStart, $periodEnd])
            ->get()
            ->groupBy('user_id');

        $employeePayroll = $employees->map(function ($employee) use ($entriesByUser) {
            $entries = $entriesByUser->get($employee->id, collect());

            $regularHours = (float) $entries->sum('regular_hours');
            $overtimeHours = (float) $entries->sum('overtime_hours');
            $hourlyRate = (float) ($employee->hourly_rate ?? 15.0); // default $15/hr if not set

            $baseSalary = 0;
            $overtimePay = 0;

            if ($employee->pay_type === 'hourly') {
                // If no time entries, assume standard 160 hours/month
                $effectiveHours = $regularHours > 0 ? $regularHours : 160;
                $baseSalary = $hourlyRate * $effectiveHours;
                $overtimePay = $hourlyRate * 1.5 * $overtimeHours;
            } else {
                // salary-based: use salary or estimate from hourly_rate * 160
                $baseSalary = (float) ($employee->salary ?? ($hourlyRate * 160));
            }

            $deductions = round($baseSalary * 0.08, 2); // 8% deductions (tax/social)
            $netPay = $baseSalary + $overtimePay - $deductions;
            $status = $entries->count() > 0 && $entries->every(fn ($entry) => $entry->status === 'approved')
                ? 'approved'
                : 'pending';

            return [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_id' => $employee->employee_id ?? ('EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT)),
                'department' => $employee->department?->name ?? $employee->department ?? 'General',
                'base_salary' => round($baseSalary, 2),
                'overtime_pay' => round($overtimePay, 2),
                'deductions' => round($deductions, 2),
                'net_pay' => round($netPay, 2),
                'status' => $status,
                'overtime_hours' => round($overtimeHours, 2),
            ];
        });

        $monthlyPayroll = $employeePayroll->sum('net_pay');
        $pendingApprovals = $employeePayroll->where('status', 'pending')->count();
        $overtimeHours = $employeePayroll->sum('overtime_hours');

        return Inertia::render('Accountant/Payroll/Index', [
            'user' => $user,
            'payrollStats' => [
                'totalEmployees' => $employeePayroll->count(),
                'monthlyPayroll' => round($monthlyPayroll, 2),
                'pendingApprovals' => $pendingApprovals,
                'overtimeHours' => round($overtimeHours, 2),
            ],
            'employees' => $employeePayroll,
        ]);
    }

    public function export(Request $request)
    {
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();

        $employees = User::active()
            ->with(['department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $entriesByUser = TimeEntry::whereBetween('work_date', [$periodStart, $periodEnd])
            ->get()
            ->groupBy('user_id');

        $rows = $employees->map(function ($employee) use ($entriesByUser) {
            $entries = $entriesByUser->get($employee->id, collect());

            $regularHours = (float) $entries->sum('regular_hours');
            $overtimeHours = (float) $entries->sum('overtime_hours');
            $hourlyRate = (float) ($employee->hourly_rate ?? 15.0);

            $baseSalary = 0;
            $overtimePay = 0;

            if ($employee->pay_type === 'hourly') {
                $effectiveHours = $regularHours > 0 ? $regularHours : 160;
                $baseSalary = $hourlyRate * $effectiveHours;
                $overtimePay = $hourlyRate * 1.5 * $overtimeHours;
            } else {
                $baseSalary = (float) ($employee->salary ?? ($hourlyRate * 160));
            }

            $deductions = round($baseSalary * 0.08, 2);
            $netPay = $baseSalary + $overtimePay - $deductions;

            return [
                $employee->full_name,
                $employee->employee_id ?? ('EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT)),
                $employee->department?->name ?? $employee->department ?? 'General',
                round($baseSalary, 2),
                round($overtimePay, 2),
                round($deductions, 2),
                round($netPay, 2),
            ];
        });

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Employee', 'Employee ID', 'Department', 'Base Salary', 'Overtime Pay', 'Deductions', 'Net Pay']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'payroll-report.csv');
    }

    public function history(Request $request)
    {
        $user = $request->user()->load('roles');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $summary = $this->getPayrollSummary($start, $end);
            $months[] = [
                'month' => $month->format('M Y'),
                'total_payroll' => round($summary['total_payroll'], 2),
                'employees' => $summary['employee_count'],
                'month_num' => $month->month,
                'year' => $month->year,
                'overtime_hours' => round($summary['overtime_hours'], 2),
                'pending' => $summary['pending_count'],
            ];
        }

        return Inertia::render('Accountant/Payroll/History', [
            'user' => $user,
            'months' => $months,
        ]);
    }

    public function process(Request $request)
    {
        $user = $request->user()->load('roles');
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();

        $summary = $this->getPayrollSummary($periodStart, $periodEnd);
        $pendingEmployees = $summary['employees']
            ->filter(fn ($employee) => $employee['status'] === 'pending')
            ->values();

        return Inertia::render('Accountant/Payroll/Process', [
            'user' => $user,
            'period' => [
                'start' => $periodStart->format('Y-m-d'),
                'end' => $periodEnd->format('Y-m-d'),
            ],
            'pendingEmployees' => $pendingEmployees,
            'pendingTotal' => round($pendingEmployees->sum('net_pay'), 2),
        ]);
    }

    public function taxes(Request $request)
    {
        $user = $request->user()->load('roles');
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();

        $summary = $this->getPayrollSummary($periodStart, $periodEnd);
        $taxRate = (float) Setting::get('payroll_tax_rate', 0);
        $taxableWages = (float) $summary['total_payroll'];
        $estimatedTax = $taxRate > 0 ? ($taxableWages * ($taxRate / 100)) : 0;

        return Inertia::render('Accountant/Payroll/Taxes', [
            'user' => $user,
            'period' => [
                'start' => $periodStart->format('Y-m-d'),
                'end' => $periodEnd->format('Y-m-d'),
            ],
            'taxRate' => $taxRate,
            'taxableWages' => round($taxableWages, 2),
            'estimatedTax' => round($estimatedTax, 2),
        ]);
    }

    private function getPayrollSummary(Carbon $start, Carbon $end): array
    {
        $employees = User::active()
            ->with(['department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $entriesByUser = TimeEntry::whereBetween('work_date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $employeePayroll = $employees->map(function ($employee) use ($entriesByUser) {
            $entries = $entriesByUser->get($employee->id, collect());

            $regularHours = (float) $entries->sum('regular_hours');
            $overtimeHours = (float) $entries->sum('overtime_hours');
            $hourlyRate = (float) ($employee->hourly_rate ?? 15.0);

            $baseSalary = 0;
            $overtimePay = 0;

            if ($employee->pay_type === 'hourly') {
                $effectiveHours = $regularHours > 0 ? $regularHours : 160;
                $baseSalary = $hourlyRate * $effectiveHours;
                $overtimePay = $hourlyRate * 1.5 * $overtimeHours;
            } else {
                $baseSalary = (float) ($employee->salary ?? ($hourlyRate * 160));
            }

            $deductions = round($baseSalary * 0.08, 2);
            $netPay = $baseSalary + $overtimePay - $deductions;
            $status = $entries->count() > 0 && $entries->every(fn ($entry) => $entry->status === 'approved')
                ? 'approved'
                : 'pending';

            return [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_id' => $employee->employee_id ?? ('EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT)),
                'department' => $employee->department?->name ?? $employee->department ?? 'General',
                'base_salary' => round($baseSalary, 2),
                'overtime_pay' => round($overtimePay, 2),
                'deductions' => round($deductions, 2),
                'net_pay' => round($netPay, 2),
                'status' => $status,
                'overtime_hours' => round($overtimeHours, 2),
            ];
        });

        return [
            'employees' => $employeePayroll,
            'employee_count' => $employeePayroll->count(),
            'total_payroll' => $employeePayroll->sum('net_pay'),
            'overtime_hours' => $employeePayroll->sum('overtime_hours'),
            'pending_count' => $employeePayroll->where('status', 'pending')->count(),
        ];
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $employee = User::findOrFail($validated['employee_id']);

        // Approve time entries
        TimeEntry::where('user_id', $employee->id)
            ->whereBetween('work_date', [$validated['period_start'], $validated['period_end']])
            ->update(['status' => 'approved']);

        // Create payroll record (simplified for now)
        // In a real system, we'd create a PayrollRecord model entry here

        return redirect()->back()->with('success', "Payroll approved for {$employee->full_name}");
    }

    public function approveAll(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        // Approve all pending time entries in the period
        TimeEntry::whereBetween('work_date', [$validated['period_start'], $validated['period_end']])
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'All pending payroll approved successfully');
    }
}
