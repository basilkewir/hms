<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $budgetStart = (clone $startDate)->subMonth();
        $budgetEnd = (clone $endDate)->subMonth();

        $categories = ExpenseCategory::where('is_active', true)->get();

        $budgetCategories = $categories->map(function ($category) use ($startDate, $endDate, $budgetStart, $budgetEnd) {
            $budgeted = Expense::where('expense_category_id', $category->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereBetween('expense_date', [$budgetStart, $budgetEnd])
                ->sum('amount');

            $actual = Expense::where('expense_category_id', $category->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            $progress = $budgeted > 0 ? round(($actual / $budgeted) * 100) : ($actual > 0 ? 100 : 0);
            $variance = $budgeted - $actual;

            if ($budgeted === 0.0 && $actual > 0) {
                $status = 'over_budget';
            } elseif ($progress > 100) {
                $status = 'over_budget';
            } elseif ($progress > 90) {
                $status = 'at_risk';
            } elseif ($progress > 75) {
                $status = 'on_track';
            } else {
                $status = 'under_budget';
            }

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'budgeted' => round($budgeted, 2),
                'actual' => round($actual, 2),
                'variance' => round($variance, 2),
                'progress' => $progress,
                'status' => $status,
            ];
        });

        $totalBudget = $budgetCategories->sum('budgeted');
        $totalSpent = $budgetCategories->sum('actual');
        $spentPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100) : 0;
        $remaining = $totalBudget - $totalSpent;
        $overBudgetCategories = $budgetCategories->where('status', 'over_budget')->count();

        return Inertia::render('Accountant/Budget/Index', [
            'user' => $user,
            'budgetStats' => [
                'totalBudget' => round($totalBudget, 2),
                'totalSpent' => round($totalSpent, 2),
                'spentPercentage' => $spentPercentage,
                'remaining' => round($remaining, 2),
                'overBudgetCategories' => $overBudgetCategories,
            ],
            'budgetCategories' => $budgetCategories,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $budgetStart = (clone $startDate)->subMonth();
        $budgetEnd = (clone $endDate)->subMonth();

        $categories = ExpenseCategory::where('is_active', true)->get();
        $rows = $categories->map(function ($category) use ($startDate, $endDate, $budgetStart, $budgetEnd) {
            $budgeted = Expense::where('expense_category_id', $category->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereBetween('expense_date', [$budgetStart, $budgetEnd])
                ->sum('amount');

            $actual = Expense::where('expense_category_id', $category->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            return [
                $category->name,
                $category->description,
                round($budgeted, 2),
                round($actual, 2),
                round($budgeted - $actual, 2),
            ];
        });

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Category', 'Description', 'Budgeted', 'Actual', 'Variance']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'budget-report.csv');
    }

    public function comparison(Request $request)
    {
        $user = $request->user()->load('roles');
        $currentStart = Carbon::now()->startOfMonth();
        $currentEnd = Carbon::now()->endOfMonth();
        $previousStart = Carbon::now()->subMonth()->startOfMonth();
        $previousEnd = Carbon::now()->subMonth()->endOfMonth();

        $categories = ExpenseCategory::where('is_active', true)->get();

        $rows = $categories->map(function ($category) use ($currentStart, $currentEnd, $previousStart, $previousEnd) {
            $previous = Expense::where('expense_category_id', $category->id)
                ->where('status', '!=', 'rejected')
                ->whereBetween('expense_date', [$previousStart, $previousEnd])
                ->sum('amount');

            $current = Expense::where('expense_category_id', $category->id)
                ->where('status', '!=', 'rejected')
                ->whereBetween('expense_date', [$currentStart, $currentEnd])
                ->sum('amount');

            $variance = $current - $previous;
            $percent = $previous > 0 ? round(($variance / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'previous' => round($previous, 2),
                'current' => round($current, 2),
                'variance' => round($variance, 2),
                'percent' => $percent,
            ];
        })->values();

        return Inertia::render('Accountant/Budget/Comparison', [
            'user' => $user,
            'periods' => [
                'current' => $currentStart->format('M Y'),
                'previous' => $previousStart->format('M Y'),
            ],
            'rows' => $rows,
        ]);
    }

    public function forecast(Request $request)
    {
        $user = $request->user()->load('roles');
        $historyMonths = 6;
        $historyStart = Carbon::now()->subMonths($historyMonths)->startOfMonth();
        $historyEnd = Carbon::now()->endOfMonth();

        // Get detailed monthly data for better forecasting
        $monthlyData = [];
        for ($i = $historyMonths; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthTotal = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->where('status', '!=', 'rejected')
                ->sum('amount');
                
            $monthlyData[] = [
                'month' => $monthStart->format('M Y'),
                'total' => $monthTotal,
                'month_number' => $monthStart->month,
                'year' => $monthStart->year,
            ];
        }

        // Calculate category averages with seasonal adjustments
        $categoryTotals = ExpenseCategory::with(['expenses' => function ($query) use ($historyStart, $historyEnd) {
            $query->whereBetween('expense_date', [$historyStart, $historyEnd])
                ->where('status', '!=', 'rejected');
        }])
            ->where('is_active', true)
            ->get()
            ->map(function ($category) use ($historyMonths) {
                $totalAmount = $category->expenses->sum('amount');
                $avgMonthly = round((float) $totalAmount / $historyMonths, 2);
                
                // Calculate trend (simple linear trend based on recent months)
                $recentExpenses = $category->expenses
                    ->sortBy('expense_date')
                    ->slice(-3); // Last 3 months for trend
                
                $trendFactor = 1.0;
                if ($recentExpenses->count() >= 2) {
                    $firstAmount = $recentExpenses->first()->amount;
                    $lastAmount = $recentExpenses->last()->amount;
                    $trendFactor = $firstAmount > 0 ? $lastAmount / $firstAmount : 1.0;
                    // Limit trend factor to reasonable range
                    $trendFactor = max(0.8, min(1.2, $trendFactor));
                }
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'avg_monthly' => $avgMonthly,
                    'trend_factor' => $trendFactor,
                    'recent_avg' => $recentExpenses->avg('amount') ?? $avgMonthly,
                ];
            })
            ->values();

        // Generate forecast with seasonal adjustments and trends
        $forecastMonths = [];
        for ($i = 1; $i <= 3; $i++) {
            $month = Carbon::now()->addMonths($i);
            $monthNumber = $month->month;
            
            // Apply seasonal multiplier
            $seasonalMultiplier = $this->getSeasonalMultiplier($monthNumber);
            
            // Calculate base forecast using category averages with trends
            $baseForecast = $categoryTotals->sum(function ($category) use ($seasonalMultiplier) {
                $adjustedAvg = $category['avg_monthly'] * $category['trend_factor'];
                return $adjustedAvg * $seasonalMultiplier;
            });
            
            // Add some intelligent variation based on historical patterns
            $variationFactor = $this->getVariationFactor($monthlyData, $monthNumber);
            $finalForecast = round($baseForecast * $variationFactor, 2);
            
            $forecastMonths[] = [
                'month' => $month->format('M Y'),
                'total' => $finalForecast,
                'base_amount' => $baseForecast,
                'seasonal_multiplier' => $seasonalMultiplier,
                'variation_factor' => $variationFactor,
            ];
        }

        return Inertia::render('Accountant/Budget/Forecast', [
            'user' => $user,
            'historyPeriod' => [
                'start' => $historyStart->format('Y-m-d'),
                'end' => $historyEnd->format('Y-m-d'),
                'months_analyzed' => $historyMonths,
            ],
            'forecastMonths' => $forecastMonths,
            'categoryAverages' => $categoryTotals,
            'monthlyHistory' => $monthlyData,
        ]);
    }

    public function comparisonExport(Request $request)
    {
        $currentStart = Carbon::now()->startOfMonth();
        $currentEnd = Carbon::now()->endOfMonth();
        $previousStart = Carbon::now()->subMonth()->startOfMonth();
        $previousEnd = Carbon::now()->subMonth()->endOfMonth();

        $categories = ExpenseCategory::where('is_active', true)->get();

        $rows = $categories->map(function ($category) use ($currentStart, $currentEnd, $previousStart, $previousEnd) {
            $previous = Expense::where('expense_category_id', $category->id)
                ->where('status', '!=', 'rejected')
                ->whereBetween('expense_date', [$previousStart, $previousEnd])
                ->sum('amount');

            $current = Expense::where('expense_category_id', $category->id)
                ->where('status', '!=', 'rejected')
                ->whereBetween('expense_date', [$currentStart, $currentEnd])
                ->sum('amount');

            $variance = $current - $previous;
            $percent = $previous > 0 ? round(($variance / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

            return [
                $category->name,
                $category->description,
                round($previous, 2),
                round($current, 2),
                round($variance, 2),
                $percent . '%',
            ];
        })->values();

        return response()->streamDownload(function () use ($rows, $currentStart, $previousStart) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Category', 'Description', $previousStart->format('M Y'), $currentStart->format('M Y'), 'Variance', 'Change %']);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'budget-comparison.csv');
    }

    public function forecastExport(Request $request)
    {
        $historyMonths = 6;
        $historyStart = Carbon::now()->subMonths($historyMonths)->startOfMonth();
        $historyEnd = Carbon::now()->endOfMonth();

        // Get forecast data
        $categoryTotals = ExpenseCategory::with(['expenses' => function ($query) use ($historyStart, $historyEnd) {
            $query->whereBetween('expense_date', [$historyStart, $historyEnd])
                ->where('status', '!=', 'rejected');
        }])
            ->where('is_active', true)
            ->get()
            ->map(function ($category) use ($historyMonths) {
                $totalAmount = $category->expenses->sum('amount');
                $avgMonthly = round((float) $totalAmount / $historyMonths, 2);
                
                $recentExpenses = $category->expenses
                    ->sortBy('expense_date')
                    ->slice(-3);
                
                $trendFactor = 1.0;
                if ($recentExpenses->count() >= 2) {
                    $firstAmount = $recentExpenses->first()->amount;
                    $lastAmount = $recentExpenses->last()->amount;
                    $trendFactor = $firstAmount > 0 ? $lastAmount / $firstAmount : 1.0;
                    $trendFactor = max(0.8, min(1.2, $trendFactor));
                }
                
                return [
                    'name' => $category->name,
                    'avg_monthly' => $avgMonthly,
                    'trend_factor' => $trendFactor,
                ];
            })
            ->values();

        // Generate forecast data
        $forecastData = [];
        for ($i = 1; $i <= 3; $i++) {
            $month = Carbon::now()->addMonths($i);
            $monthNumber = $month->month;
            
            $seasonalMultiplier = $this->getSeasonalMultiplier($monthNumber);
            
            $baseForecast = $categoryTotals->sum(function ($category) use ($seasonalMultiplier) {
                $adjustedAvg = $category['avg_monthly'] * $category['trend_factor'];
                return $adjustedAvg * $seasonalMultiplier;
            });
            
            $forecastData[] = [
                'month' => $month->format('M Y'),
                'forecast' => round($baseForecast, 2),
                'seasonal_multiplier' => $seasonalMultiplier,
            ];
        }

        return response()->streamDownload(function () use ($categoryTotals, $forecastData, $historyStart, $historyEnd) {
            $handle = fopen('php://output', 'wb');
            
            // Category analysis
            fputcsv($handle, ['Category Analysis']);
            fputcsv($handle, ['Category', 'Monthly Average', 'Trend Factor']);
            foreach ($categoryTotals as $category) {
                fputcsv($handle, [
                    $category['name'],
                    $category['avg_monthly'],
                    round($category['trend_factor'], 3),
                ]);
            }
            
            fputcsv($handle, []);
            fputcsv($handle, ['Forecast Data']);
            fputcsv($handle, ['Month', 'Forecast Amount', 'Seasonal Multiplier']);
            foreach ($forecastData as $forecast) {
                fputcsv($handle, [
                    $forecast['month'],
                    $forecast['forecast'],
                    $forecast['seasonal_multiplier'],
                ]);
            }
            
            fputcsv($handle, []);
            fputcsv($handle, ['Analysis Period']);
            fputcsv($handle, ['History Start', $historyStart->format('Y-m-d')]);
            fputcsv($handle, ['History End', $historyEnd->format('Y-m-d')]);
            fputcsv($handle, ['Months Analyzed', 6]);
            
            fclose($handle);
        }, 'budget-forecast.csv');
    }
    
    private function getSeasonalMultiplier($month)
    {
        // Seasonal patterns for hotel expenses
        if ($month >= 6 && $month <= 8) {
            return 1.12; // Summer - higher occupancy, more expenses
        } elseif ($month >= 11 || $month <= 1) {
            return 1.18; // Holiday season - peak expenses
        } elseif ($month >= 2 && $month <= 4) {
            return 0.92; // Late winter/early spring - lower expenses
        } elseif ($month >= 9 && $month <= 10) {
            return 1.05; // Fall - moderate expenses
        } else {
            return 1.0; // Normal months
        }
    }
    
    private function getVariationFactor($monthlyData, $targetMonth)
    {
        // Calculate variation based on historical patterns for this month
        $sameMonthData = array_filter($monthlyData, function ($data) use ($targetMonth) {
            return $data['month_number'] == $targetMonth;
        });
        
        if (count($sameMonthData) > 0) {
            $historicalAvg = array_sum(array_column($sameMonthData, 'total')) / count($sameMonthData);
            $overallAvg = array_sum(array_column($monthlyData, 'total')) / count($monthlyData);
            
            if ($overallAvg > 0) {
                return $historicalAvg / $overallAvg;
            }
        }
        
        // Fallback to seasonal multiplier if no historical data for this month
        return $this->getSeasonalMultiplier($targetMonth);
    }
}
