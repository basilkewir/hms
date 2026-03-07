<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';
        
        // Get expense statistics
        $currentMonth = Carbon::now()->startOfMonth();
        $currentYear = Carbon::now()->startOfYear();
        
        $monthlyExpenses = Expense::where('expense_date', '>=', $currentMonth)
            ->where('status', '!=', 'rejected')
            ->sum('amount');
        
        $pendingCount = Expense::where('status', 'pending')->count();
        $categoriesCount = ExpenseCategory::count();
        
        // Get expenses over budget (if there's a budget system, otherwise use a threshold)
        $overBudgetThreshold = 10000; // Example threshold
        $overBudgetCount = Expense::where('expense_date', '>=', $currentMonth)
            ->where('amount', '>', $overBudgetThreshold)
            ->count();
        
        // Get recent expenses
        $recentExpenses = Expense::with(['category', 'submittedBy'])
            ->latest('expense_date')
            ->limit(50)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'expense_number' => $expense->expense_number,
                    'description' => $expense->description,
                    'vendor' => $expense->vendor_name,
                    'category' => $expense->category?->name ?? 'Uncategorized',
                    'category_id' => $expense->category?->id,
                    'category_color' => $expense->category?->color ?? '#3b82f6',
                    'amount' => (float) $expense->amount,
                    'date' => $expense->expense_date->format('Y-m-d'),
                    'status' => $expense->status,
                    'payment_method' => $expense->payment_method,
                    'currency' => $expense->currency ?? 'USD',
                    'receipt_number' => $expense->receipt_number,
                    'submitted_by' => $expense->submittedBy?->full_name ?? 'N/A',
                    'approved_by' => $expense->approvedBy?->full_name,
                    'notes' => $expense->notes,
                ];
            });
        
        // Get all categories for filters and form
        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                    'color' => $category->color ?? '#3b82f6',
                    'description' => $category->description,
                ];
            });
        
        // Get settings for currency dropdown and formatting
        $settings = [
            'currency' => Setting::get('currency', 'USD'),
            'currency_position' => Setting::get('currency_position', 'prefix'),
            'supported_currencies' => Setting::get('supported_currencies', json_encode([
                'USD' => 'US Dollar ($)',
                'EUR' => 'Euro (€)',
                'GBP' => 'British Pound (£)',
                'XAF' => 'Central African CFA Franc (FCFA)'
            ]))
        ];

        return Inertia::render('Admin/Expenses/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'expenseStats' => [
                'monthly' => (float) $monthlyExpenses,
                'pending' => $pendingCount,
                'categories' => $categoriesCount,
                'overBudget' => $overBudgetCount
            ],
            'recentExpenses' => $recentExpenses,
            'categories' => $categories,
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer',
            'receipt_number' => 'nullable|string|max:255',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Generate expense number
        $expenseNumber = $this->generateExpenseNumber();

        // Handle file upload
        $receiptFilePath = null;
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $fileName = 'expense_' . time() . '_' . $file->getClientOriginalName();
            $receiptFilePath = $file->storeAs('expenses/receipts', $fileName, 'public');
        }

        // Get default currency from settings if not provided
        $defaultCurrency = Setting::get('currency', 'USD');
        
        // Create expense
        $expense = Expense::create([
            'expense_number' => $expenseNumber,
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_name' => $validated['vendor_name'],
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? $defaultCurrency,
            'payment_method' => $validated['payment_method'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'receipt_file_path' => $receiptFilePath,
            'status' => 'pending',
            'submitted_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.expenses.index' : 'admin.expenses.index';
        return redirect()->route($indexRoute)->with('success', 'Expense created successfully.');
    }

    private function generateExpenseNumber()
    {
        $today = Carbon::today()->format('Ymd');
        $lastExpense = Expense::whereDate('created_at', Carbon::today())
            ->latest()
            ->first();

        if ($lastExpense && preg_match('/EXP-' . $today . '-(\d+)/', $lastExpense->expense_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return 'EXP-' . $today . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer',
            'receipt_number' => 'nullable|string|max:255',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle file upload if a new file is provided
        $receiptFilePath = $expense->receipt_file_path; // Keep existing file by default
        if ($request->hasFile('receipt_file')) {
            // Delete old file if it exists
            if ($expense->receipt_file_path && \Storage::disk('public')->exists($expense->receipt_file_path)) {
                \Storage::disk('public')->delete($expense->receipt_file_path);
            }
            
            // Store new file
            $file = $request->file('receipt_file');
            $fileName = 'expense_' . time() . '_' . $file->getClientOriginalName();
            $receiptFilePath = $file->storeAs('expenses/receipts', $fileName, 'public');
        }

        // Get default currency from settings if not provided
        $defaultCurrency = Setting::get('currency', 'USD');
        
        // Update expense
        $expense->update([
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_name' => $validated['vendor_name'],
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? $expense->currency ?? $defaultCurrency,
            'payment_method' => $validated['payment_method'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'receipt_file_path' => $receiptFilePath,
            'notes' => $validated['notes'] ?? null,
        ]);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.expenses.index' : 'admin.expenses.index';
        return redirect()->route($indexRoute)->with('success', 'Expense updated successfully.');
    }
}
