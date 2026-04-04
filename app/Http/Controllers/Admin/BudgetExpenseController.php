<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Services\SystemActivityNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetExpenseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_budgets');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        // NOTE: This route intentionally shows Budgets (not budget_expenses)
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $categoryId = $request->input('category_id', '');
        $departmentId = $request->input('department_id', '');
        $year = $request->input('year', Carbon::now()->year);

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
                'year' => $year,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create_budgets');

        $user = $request->user();
        if ($user) {
            $user->load('roles');
        }

        $budgetId = $request->input('budget_id');

        $budgets = Budget::where('status', 'approved')
            ->where('end_date', '>=', now())
            ->get();

        $routeName = request()->route()->getName() ?? '';
        $routePrefix = str_starts_with($routeName, 'manager.') ? 'manager' : 'admin';
        return Inertia::render('Admin/Budgets/Expenses/Create', [
            'user'            => $user,
            'budgets'         => $budgets,
            'selectedBudgetId'=> $budgetId,
            'statuses'        => BudgetExpense::getStatuses(),
            'routePrefix'     => $routePrefix,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create_budgets');

        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->user()->id;
        $validated['status'] = 'pending';

        $expense = BudgetExpense::create($validated);

        $this->updateBudgetSpentAmount($expense->budget_id);

        app(SystemActivityNotifier::class)->notifyRoles(
            ['admin', 'manager'],
            'budget_expense.created',
            'New expense submitted',
            sprintf(
                'Budget expense %s for %s was submitted by %s and is pending approval.',
                $expense->description,
                number_format((float) $expense->amount, 2),
                auth()->user()?->full_name ?? auth()->user()?->email ?? 'Staff'
            ),
            [
                'manager' => route('manager.budget.expenses.show', $expense),
                'default' => route('admin.budget.expenses.pending-approvals'),
            ],
            [
                'budget_expense_id' => $expense->id,
                'budget_id' => $expense->budget_id,
                'amount' => (float) $expense->amount,
            ],
            auth()->user(),
        );

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        return redirect()->route($indexRoute, ['budget_id' => $expense->budget_id])->with('success', 'Expense submitted for approval.');
    }

    public function show(BudgetExpense $expense)
    {
        $this->authorize('view_budgets');

        $expense->load(['budget', 'creator', 'approver']);

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';
        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Budgets/Expenses/Show', [
            'expense'     => $expense,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function edit(BudgetExpense $expense)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $indexRoute = $isManager ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        if ($expense->status !== 'pending') {
            return redirect()->route($indexRoute)->with('error', 'Cannot edit an expense that has been processed.');
        }

        $budgets = Budget::where('status', 'approved')
            ->orWhere('id', $expense->budget_id)
            ->get();

        return Inertia::render('Admin/Budgets/Expenses/Edit', [
            'expense'     => $expense,
            'budgets'     => $budgets,
            'statuses'    => BudgetExpense::getStatuses(),
            'routePrefix' => $isManager ? 'manager' : 'admin'
        ]);
    }

    public function update(Request $request, BudgetExpense $expense)
    {
        $this->authorize('edit_budgets');

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        $showRoute  = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.show' : 'admin.budget.expenses.show';
        if ($expense->status !== 'pending') {
            return redirect()->route($indexRoute)->with('error', 'Cannot edit an expense that has been processed.');
        }

        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $oldBudgetId = $expense->budget_id;
        $expense->update($validated);

        $this->updateBudgetSpentAmount($oldBudgetId);
        $this->updateBudgetSpentAmount($expense->budget_id);

        return redirect()->route($showRoute, $expense)->with('success', 'Expense updated successfully.');
    }

    public function destroy(BudgetExpense $expense)
    {
        $this->authorize('delete_budgets');

        $routeName  = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        if ($expense->status !== 'pending') {
            return redirect()->route($indexRoute)->with('error', 'Cannot delete an expense that has been processed.');
        }

        $budgetId = $expense->budget_id;
        $expense->delete();

        $this->updateBudgetSpentAmount($budgetId);

        return redirect()->route($indexRoute, ['budget_id' => $budgetId])->with('success', 'Expense deleted successfully.');
    }

    public function approve(BudgetExpense $expense)
    {
        $this->authorize('approve_budgets');

        $routeName  = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        if (!$expense->isPending()) {
            return redirect()->route($indexRoute)->with('error', 'Only pending expenses can be approved.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->user()->id,
            'approved_at' => now(),
        ]);

        $this->updateBudgetSpentAmount($expense->budget_id);

        return redirect()->back()
            ->with('success', 'Expense approved successfully.');
    }

    public function reject(Request $request, BudgetExpense $expense)
    {
        $this->authorize('approve_budgets');

        $routeName  = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.budget.expenses.index' : 'admin.budget.expenses.index';
        if (!$expense->isPending()) {
            return redirect()->route($indexRoute)->with('error', 'Only pending expenses can be rejected.');
        }

        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Expense rejected.');
    }

    public function pendingApprovals(Request $request)
    {
        // NOTE: This route intentionally shows Budgets pending approval (not budget_expenses)
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
            ->where('status', Budget::STATUS_PENDING_APPROVAL)
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

        // Calculate total pending amount
        $totalPendingAmount = $budgets->sum('amount');

        return Inertia::render('Admin/Budgets/Expenses/PendingApprovals', [
            'user' => $user,
            'pendingExpenses' => $budgets,
            'totalPendingAmount' => $totalPendingAmount,
            'categories' => $categories,
            'departments' => $departments,
            'years' => $years,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'department_id' => $departmentId,
                'year' => $year,
            ],
        ]);
    }

    private function updateBudgetSpentAmount($budgetId)
    {
        $budget = Budget::find($budgetId);
        if ($budget) {
            $spentAmount = BudgetExpense::where('budget_id', $budgetId)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');
            $budget->update(['spent_amount' => $spentAmount]);
        }
    }
}
