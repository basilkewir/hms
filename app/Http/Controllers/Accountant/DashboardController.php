<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\GuestFolio;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        $today = Carbon::today();
        $currentMonth = Carbon::now()->startOfMonth();

        // Revenue calculations
        $todayRevenue = Payment::where('status', 'completed')
            ->whereDate('processed_at', $today)
            ->sum(DB::raw('COALESCE(local_amount, amount)'));
        $todayPosRevenue = Sale::where('payment_status', 'completed')
            ->whereDate('sale_date', $today)
            ->sum('total_amount');

        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('processed_at', Carbon::now()->month)
            ->whereYear('processed_at', Carbon::now()->year)
            ->sum(DB::raw('COALESCE(local_amount, amount)'));
        $monthlyPosRevenue = Sale::where('payment_status', 'completed')
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        // Expense calculations
        $monthlyExpenses = Expense::where('expense_date', '>=', $currentMonth)
            ->where('status', '!=', 'rejected')
            ->sum('amount');

        // Financial summary
        $financialSummary = [
            'todaysRevenue' => round($todayRevenue + $todayPosRevenue, 2),
            'monthlyRevenue' => round($monthlyRevenue + $monthlyPosRevenue, 2),
            'monthlyExpenses' => round($monthlyExpenses, 2),
            'netProfit' => round(($monthlyRevenue + $monthlyPosRevenue) - $monthlyExpenses, 2),
        ];

        // Recent transactions
        $recentTransactions = collect();

        // Recent payments
        $recentPayments = Payment::with(['folio.guest'])
            ->where('status', 'completed')
            ->orderByDesc('processed_at')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'description' => $payment->folio?->guest?->full_name ? 'Payment from ' . $payment->folio->guest->full_name : 'Payment',
                    'date' => $payment->processed_at?->toDateTimeString() ?? $payment->created_at->toDateTimeString(),
                    'amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
                    'type' => 'income',
                ];
            });

        // Recent expenses
        $recentExpenses = Expense::with('category')
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'date' => $expense->expense_date?->toDateTimeString(),
                    'amount' => (float) $expense->amount,
                    'type' => 'expense',
                ];
            });

        $recentTransactions = $recentPayments->merge($recentExpenses)
            ->sortByDesc('date')
            ->values()
            ->take(10);

        // Pending payments
        $pendingPayments = Payment::with(['folio.guest'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'description' => $payment->folio?->guest?->full_name
                        ? 'Payment from ' . $payment->folio->guest->full_name
                        : ($payment->description ?? 'Pending Payment'),
                    'due_date' => $payment->created_at->addDays(7)->toDateTimeString(),
                    'amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
                ];
            });

        // If no real pending payments found, don't show dummy data - just show empty array
        // The frontend will handle empty state appropriately

        // Charts data
        $charts = [
            'revenueExpense' => $this->getRevenueExpenseChart(),
            'expenses' => $this->getExpenseChart(),
        ];

        // Metrics
        $metrics = [
            'avgDailyRevenue' => $this->getAverageDailyRevenue(),
            'profitMargin' => $financialSummary['monthlyRevenue'] > 0
                ? round(($financialSummary['netProfit'] / $financialSummary['monthlyRevenue']) * 100, 1)
                : 0,
            'expenseRatio' => $financialSummary['monthlyRevenue'] > 0
                ? round(($financialSummary['monthlyExpenses'] / $financialSummary['monthlyRevenue']) * 100, 1)
                : 0,
            'cashFlow' => $financialSummary['netProfit'],
        ];

        return Inertia::render('Accountant/Dashboard', [
            'user' => $user,
            'financialSummary' => $financialSummary,
            'recentTransactions' => $recentTransactions,
            'pendingPayments' => $pendingPayments,
            'charts' => $charts,
            'metrics' => $metrics,
        ]);
    }

    private function getRevenueExpenseChart()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = Payment::where('status', 'completed')
                ->whereBetween('processed_at', [$monthStart, $monthEnd])
                ->sum(DB::raw('COALESCE(local_amount, amount)'));

            $posRevenue = Sale::where('payment_status', 'completed')
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $expenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->where('status', '!=', 'rejected')
                ->sum('amount');

            $data[] = [
                'month' => $month->format('M Y'),
                'revenue' => round($revenue + $posRevenue, 2),
                'expenses' => round($expenses, 2),
            ];
        }

        return $data;
    }

    private function getExpenseChart()
    {
        $startMonth = Carbon::now()->subMonths(5)->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        return Expense::selectRaw('expense_categories.name as category, SUM(expenses.amount) as total_amount')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.expense_date', [$startMonth, $endMonth])
            ->where('expenses.status', '!=', 'rejected')
            ->groupBy('expense_categories.name')
            ->orderByDesc('total_amount')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'amount' => round((float) $item->total_amount, 2),
                ];
            });
    }

    private function getAverageDailyRevenue()
    {
        $last30Days = Carbon::now()->subDays(30);

        $revenue = Payment::where('status', 'completed')
            ->where('processed_at', '>=', $last30Days)
            ->sum(DB::raw('COALESCE(local_amount, amount)'));

        $posRevenue = Sale::where('payment_status', 'completed')
            ->where('sale_date', '>=', $last30Days)
            ->sum('total_amount');

        return round(($revenue + $posRevenue) / 30, 2);
    }
}
