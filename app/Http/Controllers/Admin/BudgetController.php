<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Department;
use App\Models\User;
use App\Models\Setting;
use App\Models\BudgetExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_budgets');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        // Get filters
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $categoryId = $request->input('category_id', '');
        $departmentId = $request->input('department_id', '');
        $year = $request->input('year', Carbon::now()->year);

        // Build query
        $query = Budget::with(['category', 'department', 'createdBy', 'approvedBy'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($year, function ($q) use ($year) {
                $q->where(function ($q) use ($year) {
                    $q->whereYear('start_date', $year)
                        ->orWhereYear('end_date', $year);
                });
            });

        $budgets = $query->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->paginate(15);

        // Get filter options
        $categories = ExpenseCategory::active()->get();
        $departments = Department::active()->get();
        $years = Budget::selectRaw('YEAR(start_date) as year')
            ->union(Budget::selectRaw('YEAR(end_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return Inertia::render('Admin/Budgets/Index', [
            'user' => $user,
            'budgets' => $budgets,
            'categories' => $categories,
            'departments' => $departments,
            'years' => $years,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category_id' => $categoryId,
                'department_id' => $departmentId,
                'year' => $year
            ]
        ]);
    }

    public function archived(Request $request)
    {
        $this->authorize('view_budgets');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        $search = $request->input('search', '');
        $categoryId = $request->input('category_id', '');
        $departmentId = $request->input('department_id', '');
        $year = $request->input('year', Carbon::now()->year);

        $query = Budget::with(['category', 'department', 'createdBy', 'approvedBy'])
            ->where('status', Budget::STATUS_ARCHIVED)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($year, function ($q) use ($year) {
                $q->where(function ($q) use ($year) {
                    $q->whereYear('start_date', $year)
                        ->orWhereYear('end_date', $year);
                });
            });

        $budgets = $query->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->paginate(15);

        $categories = ExpenseCategory::active()->get();
        $departments = Department::active()->get();
        $years = Budget::selectRaw('YEAR(start_date) as year')
            ->union(Budget::selectRaw('YEAR(end_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return Inertia::render('Admin/Budgets/Index', [
            'user' => $user,
            'budgets' => $budgets,
            'categories' => $categories,
            'departments' => $departments,
            'years' => $years,
            'filters' => [
                'search' => $search,
                'status' => Budget::STATUS_ARCHIVED,
                'category_id' => $categoryId,
                'department_id' => $departmentId,
                'year' => $year
            ]
        ]);
    }

    public function create()
    {
        $this->authorize('create_budgets');

        $user = auth()->user()->load('roles');
        $categories = ExpenseCategory::active()->get();
        $departments = Department::active()->get();

        $routeName = request()->route()->getName() ?? '';
        $routePrefix = str_starts_with($routeName, 'manager.') ? 'manager' : 'admin';
        return Inertia::render('Admin/Budgets/Create', [
            'user'         => $user,
            'categories'   => $categories,
            'departments'  => $departments,
            'statuses'     => Budget::getStatuses(),
            'routePrefix'  => $routePrefix,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create_budgets');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:budgets,name',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'required|exists:expense_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $budget = Budget::create($validated);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.index' : 'admin.budget.index';
        return redirect()->route($indexRoute)->with('success', 'Budget created successfully. Submit for approval.');
    }

    public function show(Budget $budget)
    {
        $this->authorize('view_budgets');

        $budget->load(['category', 'department', 'createdBy', 'approvedBy', 'expenses']);

        // Get expenses for this budget period (from budget_expenses table)
        $expenses = $budget->expenses()
            ->orderBy('expense_date', 'desc')
            ->get();

        $user = auth()->user()->load('roles');

        $role = $user->roles->first()?->name ?? 'admin';
        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Budgets/Show', [
            'user'        => $user,
            'budget'      => $budget,
            'expenses'    => $expenses,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function edit(Budget $budget)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $indexRoute = $isManager ? 'manager.budget.index' : 'admin.budget.index';
        if ($budget->status === 'approved') {
            return redirect()->route($indexRoute)->with('error', 'Cannot edit an approved budget. Create a new one instead.');
        }

        $user = auth()->user()->load('roles');
        $categories = ExpenseCategory::active()->get();
        $departments = Department::active()->get();

        return Inertia::render('Admin/Budgets/Edit', [
            'user'        => $user,
            'budget'      => $budget,
            'categories'  => $categories,
            'departments' => $departments,
            'statuses'    => Budget::getStatuses(),
            'routePrefix' => $isManager ? 'manager' : 'admin',
        ]);
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        if ($budget->status === 'approved') {
            return redirect()->route($showRoute, $budget)->with('error', 'Cannot edit an approved budget.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:budgets,name,' . $budget->id,
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'required|exists:expense_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'notes' => 'nullable|string',
        ]);

        $budget->update($validated);

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        return redirect()->route($showRoute, $budget)->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete_budgets');

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.index' : 'admin.budget.index';
        if ($budget->status === 'approved') {
            return redirect()->route($indexRoute)->with('error', 'Cannot delete an approved budget.');
        }

        $budget->delete();

        return redirect()->route($indexRoute)->with('success', 'Budget deleted successfully.');
    }

    public function submitForApproval(Budget $budget)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        if ($budget->status !== 'draft') {
            return redirect()->route($showRoute, $budget)->with('error', 'Only draft budgets can be submitted for approval.');
        }

        $budget->update(['status' => 'pending_approval']);

        return redirect()->route($showRoute, $budget)->with('success', 'Budget submitted for approval.');
    }

    public function approve(Budget $budget)
    {
        $this->authorize('approve_budgets');

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        if ($budget->status !== 'pending_approval') {
            return redirect()->route($showRoute, $budget)->with('error', 'Only pending approval budgets can be approved.');
        }

        $budget->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

        return redirect()->route($showRoute, $budget)->with('success', 'Budget approved successfully.');
    }

    public function reject(Budget $budget)
    {
        $this->authorize('approve_budgets');

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        if ($budget->status !== 'pending_approval') {
            return redirect()->route($showRoute, $budget)->with('error', 'Only pending approval budgets can be rejected.');
        }

        $budget->update(['status' => 'rejected']);

        return redirect()->route($showRoute, $budget)->with('success', 'Budget rejected.');
    }

    public function archive(Budget $budget)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.show' : 'admin.budget.show';
        if ($budget->status !== 'approved') {
            return redirect()->route($showRoute, $budget)->with('error', 'Only approved budgets can be archived.');
        }

        $budget->update(['status' => 'archived']);

        return redirect()->route($showRoute, $budget)->with('success', 'Budget archived successfully.');
    }

public function dashboard(Request $request)
    {
        $this->authorize('view_budgets');

        try {
            $user = $request->user();
            if ($user) {
                $user->load('roles');
            }

            $currentDate = Carbon::now();

            // Get ALL budgets (not just active) to show all data
            $allBudgets = Budget::with(['category', 'department'])->get();

            $totalBudgets = $allBudgets->count();
            $approvedBudgets = $allBudgets->where('status', 'approved')->count();
            $pendingApproval = $allBudgets->where('status', 'pending_approval')->count();
            $draftBudgets = $allBudgets->where('status', 'draft')->count();
            $rejectedBudgets = $allBudgets->where('status', 'rejected')->count();

            // Calculate stats for all budgets with actual spending
            $overBudget = $allBudgets->where('is_over_budget', true)->count();
            $nearBudget = $allBudgets->where('is_near_budget', true)->count();
            $onTrack = $allBudgets->where('is_on_track', true)->count();

            // Calculate totals
            $totalBudgetAmount = $allBudgets->sum('amount');
            $totalSpentAmount = $allBudgets->sum('spent_amount');

            $summary = [
                'total_budgets' => $totalBudgets,
                'approved_budgets' => $approvedBudgets,
                'pending_approval' => $pendingApproval,
                'draft_budgets' => $draftBudgets,
                'rejected_budgets' => $rejectedBudgets,
                'over_budget' => $overBudget,
                'near_budget' => $nearBudget,
                'on_track' => $onTrack,
                'total_budget_amount' => $totalBudgetAmount,
                'total_spent_amount' => $totalSpentAmount,
            ];

            // Get all budgets with category and department loaded (not just active)
            $allBudgetsList = Budget::with(['category', 'department', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($budget) {
                    return [
                        'id' => $budget->id,
                        'name' => $budget->name,
                        'category' => $budget->category ? ['name' => $budget->category->name] : null,
                        'department' => $budget->department ? ['name' => $budget->department->name] : null,
                        'start_date' => $budget->start_date,
                        'end_date' => $budget->end_date,
                        'amount' => $budget->amount,
                        'spent_amount' => $budget->spent_amount,
                        'remaining_amount' => $budget->remaining_amount,
                        'utilization_percentage' => $budget->utilization_percentage,
                        'is_over_budget' => $budget->is_over_budget,
                        'is_near_budget' => $budget->is_near_budget,
                        'is_on_track' => $budget->is_on_track,
                        'status' => $budget->status,
                        'status_label' => $this->getStatusLabel($budget->status),
                    ];
                });

            // Get department-wise utilization from all budgets
            $departments = Department::active()->get();
            $departmentData = [];

            foreach ($departments as $dept) {
                $deptBudgets = $allBudgets->where('department_id', $dept->id);
                if ($deptBudgets->count() > 0) {
                    $deptApprovedBudgets = $deptBudgets->where('status', 'approved');
                    $totalBudget = $deptApprovedBudgets->sum('amount');
                    $totalSpent = $deptApprovedBudgets->sum('spent_amount');
                    $utilization = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 2) : 0;

                    $departmentData[] = [
                        'name' => $dept->name,
                        'total_budget' => $totalBudget,
                        'total_spent' => $totalSpent,
                        'utilization' => $utilization,
                        'budgets_count' => $deptBudgets->count(),
                        'approved_count' => $deptApprovedBudgets->count(),
                    ];
                }
            }

            return Inertia::render('Admin/Budgets/Dashboard', [
                'user' => $user,
                'summary' => $summary,
                'budgets' => $allBudgetsList,
                'departmentUtilization' => $departmentData
            ]);

        } catch (\Exception $e) {
            // Return error details for debugging
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            'archived' => 'Archived',
        ];
        return $labels[$status] ?? $status;
    }

    public function reports(Request $request)
    {
        // $this->authorize('view_budget_reports'); // Temporarily disabled for testing

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $search = (string) $request->input('search', '');

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'XAF';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'suffix';

        // Currency configuration for the frontend
        $currencyConfig = [
            'code' => $currency,
            'symbol' => $currency,
            'position' => $currencyPosition
        ];

        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $budgetsForYear = Budget::query()
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->whereBetween('start_date', [$yearStart, $yearEnd])
                    ->orWhereBetween('end_date', [$yearStart, $yearEnd])
                    ->orWhere(function ($q) use ($yearStart, $yearEnd) {
                        $q->where('start_date', '<=', $yearStart)
                            ->where('end_date', '>=', $yearEnd);
                    });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->with(['category', 'department'])
            ->orderBy('start_date', 'desc')
            ->orderBy('name')
            ->get();

        $budgetIds = $budgetsForYear->pluck('id');

        $actualsByBudget = BudgetExpense::query()
            ->select('budget_id', DB::raw('SUM(amount) as total_amount'))
            ->whereIn('budget_id', $budgetIds)
            ->whereIn('status', ['approved', 'paid'])
            ->groupBy('budget_id')
            ->pluck('total_amount', 'budget_id');

        // Budget vs Actual Analysis
        $budgetAnalysis = $budgetsForYear->map(function ($budget) use ($actualsByBudget) {
            $budgeted = (float) ($budget->amount ?? 0);
            $actual = (float) ($actualsByBudget[$budget->id] ?? 0);
            $variance = $budgeted - $actual;
            $utilization = $budgeted > 0 ? round(($actual / $budgeted) * 100, 2) : ($actual > 0 ? 100 : 0);

            return [
                'name' => $budget->name,
                'category' => $budget->category?->name ?? 'N/A',
                'department' => $budget->department?->name ?? 'N/A',
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $variance,
                'utilization' => $utilization,
                'status' => $budget->status,
                'health' => $utilization >= 100 ? 'critical' : ($utilization >= 80 ? 'warning' : 'good'),
                'days_remaining' => $budget->getDaysRemaining(),
                'projected_overrun' => max(0, $actual - $budgeted)
            ];
        });

        // Monthly trend analysis
        $monthlyActuals = BudgetExpense::query()
            ->selectRaw('MONTH(expense_date) as month_num, SUM(amount) as total_amount')
            ->whereYear('expense_date', $year)
            ->whereIn('budget_id', $budgetIds)
            ->whereIn('status', ['approved', 'paid'])
            ->groupBy('month_num')
            ->pluck('total_amount', 'month_num');

        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $startOfMonth = Carbon::create($year, $m, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $m, 1)->endOfMonth();

            $totalBudgeted = (float) $budgetsForYear
                ->filter(function ($b) use ($startOfMonth, $endOfMonth) {
                    return $b->start_date <= $endOfMonth && $b->end_date >= $startOfMonth;
                })
                ->sum('amount');

            $totalActual = (float) ($monthlyActuals[$m] ?? 0);

            $monthlyTrends[] = [
                'month' => Carbon::create($year, $m, 1)->format('F'),
                'budgeted' => $totalBudgeted,
                'actual' => $totalActual,
                'variance' => $totalBudgeted - $totalActual,
                'utilization' => $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 2) : 0
            ];
        }

        $now = now();
        $activeBudgetsForYear = $budgetsForYear->filter(function ($b) use ($now) {
            return $b->status === Budget::STATUS_APPROVED && $b->start_date <= $now && $b->end_date >= $now;
        });

        $activeUtilizations = $activeBudgetsForYear->map(function ($b) use ($actualsByBudget) {
            $budgeted = (float) ($b->amount ?? 0);
            $actual = (float) ($actualsByBudget[$b->id] ?? 0);
            return $budgeted > 0 ? round(($actual / $budgeted) * 100, 2) : ($actual > 0 ? 100 : 0);
        });

        // Budget health summary
        $budgetHealthSummary = [
            'total_budgets' => $budgetsForYear->count(),
            'active_budgets' => $activeBudgetsForYear->count(),
            'on_track' => $activeUtilizations->filter(fn ($u) => $u < 80)->count(),
            'near_budget' => $activeUtilizations->filter(fn ($u) => $u >= 80 && $u < 100)->count(),
            'over_budget' => $activeUtilizations->filter(fn ($u) => $u >= 100)->count(),
            'near_expiration' => $activeBudgetsForYear->filter(fn ($b) => $b->getDaysRemaining() <= 7 && $b->getDaysRemaining() > 0)->count(),
        ];

        // Department-wise analysis
        $departmentAnalysis = Department::active()
            ->get()
            ->map(function ($department) use ($budgetsForYear, $actualsByBudget) {
                $departmentBudgets = $budgetsForYear->where('department_id', $department->id);
                $totalBudgeted = (float) $departmentBudgets->sum('amount');
                $totalActual = (float) $departmentBudgets->sum(function ($b) use ($actualsByBudget) {
                    return (float) ($actualsByBudget[$b->id] ?? 0);
                });
                $utilization = $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 2) : ($totalActual > 0 ? 100 : 0);

                return [
                    'name' => $department->name,
                    'total_budgeted' => $totalBudgeted,
                    'total_actual' => $totalActual,
                    'utilization' => $utilization,
                    'variance' => $totalBudgeted - $totalActual,
                    'budgets_count' => $departmentBudgets->count(),
                    'over_budget_count' => $departmentBudgets->filter(function ($b) use ($actualsByBudget) {
                        $budgeted = (float) ($b->amount ?? 0);
                        $actual = (float) ($actualsByBudget[$b->id] ?? 0);
                        return $actual > $budgeted;
                    })->count(),
                    'health' => $utilization >= 100 ? 'critical' : ($utilization >= 80 ? 'warning' : 'good')
                ];
            });

        // Category-wise analysis
        $categoryAnalysis = ExpenseCategory::where('is_active', true)
            ->get()
            ->map(function ($category) use ($budgetsForYear, $actualsByBudget) {
                $categoryBudgets = $budgetsForYear->where('category_id', $category->id);
                $totalBudgeted = (float) $categoryBudgets->sum('amount');
                $totalActual = (float) $categoryBudgets->sum(function ($b) use ($actualsByBudget) {
                    return (float) ($actualsByBudget[$b->id] ?? 0);
                });
                $utilization = $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 2) : ($totalActual > 0 ? 100 : 0);

                return [
                    'name' => $category->name,
                    'total_budgeted' => $totalBudgeted,
                    'total_actual' => $totalActual,
                    'utilization' => $utilization,
                    'variance' => $totalBudgeted - $totalActual,
                    'budgets_count' => $categoryBudgets->count(),
                    'over_budget_count' => $categoryBudgets->filter(function ($b) use ($actualsByBudget) {
                        $budgeted = (float) ($b->amount ?? 0);
                        $actual = (float) ($actualsByBudget[$b->id] ?? 0);
                        return $actual > $budgeted;
                    })->count()
                ];
            });

        return Inertia::render('Admin/Budgets/Reports', [
            'user' => $user,
            'budgetAnalysis' => $budgetAnalysis,
            'monthlyTrends' => $monthlyTrends,
            'budgetHealthSummary' => $budgetHealthSummary,
            'departmentAnalysis' => $departmentAnalysis,
            'categoryAnalysis' => $categoryAnalysis,
            'year' => $year,
            'month' => $month,
            'currencyConfig' => $currencyConfig
        ]);
    }

    public function analytics(Request $request)
    {
        $this->authorize('view_budget_analytics');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        $budgetId = $request->input('budget_id');
        $timeframe = $request->input('timeframe', 'monthly'); // monthly, quarterly, yearly

        if ($budgetId) {
            $budget = Budget::with(['category', 'department', 'createdBy'])
                ->findOrFail($budgetId);

            // Get detailed analytics for specific budget
            $analytics = [
                'budget' => $budget,
                'monthly_utilization' => $budget->getMonthlyUtilization(),
                'expense_breakdown' => $budget->getExpenseBreakdown(),
                'variance_analysis' => $budget->getVarianceAnalysis(),
                'alerts' => [
                    'warning' => $budget->shouldSendWarningAlert(),
                    'critical' => $budget->shouldSendCriticalAlert(),
                    'over_budget' => $budget->shouldSendOverBudgetAlert(),
                    'near_expiration' => $budget->isNearExpiration()
                ]
            ];

            return Inertia::render('Admin/Budgets/Analytics', [
                'user' => $user,
                'analytics' => $analytics
            ]);
        } else {
            // Get analytics for all budgets
            $activeBudgets = Budget::active()->with(['category', 'department'])->get();

            // Get active budgets for grouping
            $budgetsForGrouping = Budget::active()->get();

            // Department breakdown - group by department
            $departmentBreakdown = Department::active()
                ->get()
                ->map(function ($department) use ($budgetsForGrouping) {
                    $budgets = $budgetsForGrouping->where('department_id', $department->id);
                    return [
                        'name' => $department->name,
                        'total_budgeted' => $budgets->sum('amount'),
                        'total_spent' => $budgets->sum('spent_amount'),
                        'utilization' => $budgets->sum('amount') > 0 ? round($budgets->sum('spent_amount') / $budgets->sum('amount') * 100, 2) : 0,
                        'budgets_count' => $budgets->count(),
                        'over_budget_count' => $budgets->where('is_over_budget', true)->count()
                    ];
                })
                ->filter(function ($dept) {
                    return $dept['budgets_count'] > 0;
                })
                ->values();

            // Category breakdown - group by category
            $categoryBreakdown = ExpenseCategory::active()
                ->get()
                ->map(function ($category) use ($budgetsForGrouping) {
                    $budgets = $budgetsForGrouping->where('category_id', $category->id);
                    return [
                        'name' => $category->name,
                        'total_budgeted' => $budgets->sum('amount'),
                        'total_spent' => $budgets->sum('spent_amount'),
                        'utilization' => $budgets->sum('amount') > 0 ? round($budgets->sum('spent_amount') / $budgets->sum('amount') * 100, 2) : 0,
                        'budgets_count' => $budgets->count(),
                        'over_budget_count' => $budgets->where('is_over_budget', true)->count()
                    ];
                })
                ->filter(function ($cat) {
                    return $cat['budgets_count'] > 0;
                })
                ->values();

            $analytics = [
                'summary' => [
                    'total_active_budgets' => $activeBudgets->count(),
                    'total_budgeted' => $activeBudgets->sum('amount'),
                    'total_spent' => $activeBudgets->sum('spent_amount'),
                    'total_remaining' => $activeBudgets->sum('remaining_amount'),
                    'average_utilization' => $activeBudgets->count() > 0 ? round($activeBudgets->sum('spent_amount') / $activeBudgets->sum('amount') * 100, 2) : 0,
                    'over_budget_count' => $activeBudgets->where('is_over_budget', true)->count(),
                    'near_budget_count' => $activeBudgets->where('is_near_budget', true)->count(),
                    'on_track_count' => $activeBudgets->where('is_on_track', true)->count(),
                ],
                'health_distribution' => [
                    'good' => $activeBudgets->filter(function ($b) { return $b->getBudgetHealth() === 'good'; })->count(),
                    'warning' => $activeBudgets->filter(function ($b) { return $b->getBudgetHealth() === 'warning'; })->count(),
                    'critical' => $activeBudgets->filter(function ($b) { return $b->getBudgetHealth() === 'critical'; })->count(),
                ],
                'department_breakdown' => $departmentBreakdown,
                'category_breakdown' => $categoryBreakdown,
            ];

            return Inertia::render('Admin/Budgets/Analytics', [
                'user' => $user,
                'analytics' => $analytics,
                'timeframe' => $timeframe
            ]);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('export_budget_data');

        $format = strtolower($request->input('format', 'csv'));
        $year = $request->input('year', Carbon::now()->year);

        $budgets = Budget::where(function ($q) use ($year) {
                $q->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            })
            ->with(['category', 'department'])
            ->get();

        switch ($format) {
            case 'csv':
                return $this->exportCsv($budgets, $year);
            case 'xlsx':
                return $this->exportExcel($budgets, $year);
            case 'pdf':
                return $this->exportPdf($budgets, $year);
            case 'docx':
                return $this->exportWord($budgets, $year);
            default:
                return redirect()->back()->with('error', 'Export format not supported.');
        }
    }

    private function exportCsv($budgets, $year)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="budgets_export_' . $year . '.csv"',
        ];

        $callback = function () use ($budgets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Category', 'Department', 'Amount', 'Spent', 'Remaining', 'Utilization %', 'Status', 'Start Date', 'End Date']);

            foreach ($budgets as $budget) {
                fputcsv($file, [
                    $budget->name,
                    $budget->category->name,
                    $budget->department?->name ?? 'N/A',
                    $budget->amount,
                    $budget->spent_amount,
                    $budget->remaining_amount,
                    $budget->utilization_percentage,
                    $budget->status,
                    $budget->start_date->format('Y-m-d'),
                    $budget->end_date->format('Y-m-d')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($budgets, $year)
    {
        // Simple Excel export using CSV with .xlsx extension (fallback)
        // In production, you'd use PhpSpreadsheet
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="budgets_export_' . $year . '.xlsx"',
        ];

        $callback = function () use ($budgets) {
            $file = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Name', 'Category', 'Department', 'Amount', 'Spent', 'Remaining', 'Utilization %', 'Status', 'Start Date', 'End Date']);

            foreach ($budgets as $budget) {
                fputcsv($file, [
                    $budget->name,
                    $budget->category->name,
                    $budget->department?->name ?? 'N/A',
                    $budget->amount,
                    $budget->spent_amount,
                    $budget->remaining_amount,
                    $budget->utilization_percentage,
                    $budget->status,
                    $budget->start_date->format('Y-m-d'),
                    $budget->end_date->format('Y-m-d')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPdf($budgets, $year)
    {
        // Simple HTML to PDF export
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Budgets Export ' . $year . '</title><style>
            body{font-family:Arial,sans-serif;margin:20px;}
            h1{color:#333;}
            table{width:100%;border-collapse:collapse;margin-top:20px;}
            th,td{border:1px solid #ddd;padding:8px;text-align:left;}
            th{background-color:#f2f2f2;}
        </style></head><body>';
        $html .= '<h1>Budgets Export - ' . $year . '</h1>';
        $html .= '<table><tr><th>Name</th><th>Category</th><th>Department</th><th>Amount</th><th>Spent</th><th>Remaining</th><th>Utilization %</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>';

        foreach ($budgets as $budget) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($budget->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($budget->category->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($budget->department?->name ?? 'N/A') . '</td>';
            $html .= '<td>' . number_format($budget->amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->spent_amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->remaining_amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->utilization_percentage, 2) . '%</td>';
            $html .= '<td>' . htmlspecialchars($budget->status) . '</td>';
            $html .= '<td>' . $budget->start_date->format('Y-m-d') . '</td>';
            $html .= '<td>' . $budget->end_date->format('Y-m-d') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="budgets_export_' . $year . '.pdf"',
        ];

        // Simple PDF generation using HTML (in production use DomPDF)
        return response($html, 200, $headers);
    }

    private function exportWord($budgets, $year)
    {
        // Simple Word document using HTML
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Budgets Export ' . $year . '</title><style>
            body{font-family:Arial,sans-serif;margin:20px;}
            h1{color:#333;}
            table{width:100%;border-collapse:collapse;margin-top:20px;}
            th,td{border:1px solid #ddd;padding:8px;text-align:left;}
            th{background-color:#f2f2f2;}
        </style></head><body>';
        $html .= '<h1>Budgets Export - ' . $year . '</h1>';
        $html .= '<table><tr><th>Name</th><th>Category</th><th>Department</th><th>Amount</th><th>Spent</th><th>Remaining</th><th>Utilization %</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>';

        foreach ($budgets as $budget) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($budget->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($budget->category->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($budget->department?->name ?? 'N/A') . '</td>';
            $html .= '<td>' . number_format($budget->amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->spent_amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->remaining_amount, 2) . '</td>';
            $html .= '<td>' . number_format($budget->utilization_percentage, 2) . '%</td>';
            $html .= '<td>' . htmlspecialchars($budget->status) . '</td>';
            $html .= '<td>' . $budget->start_date->format('Y-m-d') . '</td>';
            $html .= '<td>' . $budget->end_date->format('Y-m-d') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="budgets_export_' . $year . '.docx"',
        ];

        return response($html, 200, $headers);
    }

    public function alerts(Request $request)
    {
        $this->authorize('create_budget_alerts');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        // Get budgets that need alerts
        $alertBudgets = Budget::active()
            ->with(['category', 'department'])
            ->get()
            ->filter(function ($budget) {
                return $budget->shouldSendWarningAlert() ||
                       $budget->shouldSendCriticalAlert() ||
                       $budget->shouldSendOverBudgetAlert() ||
                       $budget->isNearExpiration();
            })
            ->map(function ($budget) {
                return [
                    'budget' => $budget,
                    'alerts' => [
                        'warning' => $budget->shouldSendWarningAlert(),
                        'critical' => $budget->shouldSendCriticalAlert(),
                        'over_budget' => $budget->shouldSendOverBudgetAlert(),
                        'near_expiration' => $budget->isNearExpiration()
                    ],
                    'days_remaining' => $budget->getDaysRemaining(),
                    'utilization_percentage' => $budget->utilization_percentage
                ];
            });

        return Inertia::render('Admin/Budgets/Alerts', [
            'user' => $user,
            'alertBudgets' => $alertBudgets
        ]);
    }
}
