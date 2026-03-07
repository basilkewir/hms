<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user()->load('roles');
        $currentMonth = Carbon::now()->startOfMonth();

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Build query with filters
        $query = Expense::with('category')->orderByDesc('expense_date');

        // Apply filters if provided
        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        if ($request->get('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('date_range')) {
            $dateRange = $request->get('date_range');
            $now = Carbon::now();
            
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('expense_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('expense_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('expense_date', $now->month)
                          ->whereYear('expense_date', $now->year);
                    break;
                case 'quarter':
                    $query->whereBetween('expense_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('expense_date', $now->year);
                    break;
                // 'all' case doesn't need filtering
            }
        }

        // Get paginated results
        $expenses = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform the data
        $transformedExpenses = $expenses->getCollection()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'date' => optional($expense->expense_date)->format('Y-m-d'),
                'description' => $expense->description,
                'category_id' => $expense->category?->id,
                'category_name' => $expense->category?->name ?? 'Uncategorized',
                'category_color' => $expense->category?->color ?? '#3b82f6',
                'amount' => (float) $expense->amount,
                'vendor' => $expense->vendor_name,
                'status' => $expense->status,
                'reference_number' => $expense->receipt_number,
            ];
        });

        // Replace the collection in pagination
        $expenses->setCollection($transformedExpenses);

        $expenseStats = [
            'thisMonth' => (float) Expense::where('expense_date', '>=', $currentMonth)
                ->where('status', '!=', 'rejected')
                ->sum('amount'),
            'pending' => Expense::where('status', 'pending')->count(),
            'total' => Expense::count(),
            'categories' => ExpenseCategory::where('is_active', true)->count(),
        ];

        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'color' => $category->color ?? '#3b82f6',
            ]);

        return Inertia::render('Accountant/Expenses/Index', [
            'user' => $user,
            'expenseStats' => $expenseStats,
            'expenses' => $expenses,
            'categories' => $categories,
            'filters' => [
                'search' => $request->get('search', ''),
                'category_id' => $request->get('category_id', ''),
                'status' => $request->get('status', ''),
                'date_range' => $request->get('date_range', 'all'),
                'per_page' => $perPage,
            ]
        ]);
    }

    public function create()
    {
        $user = auth()->user()->load('roles');
        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
            ]);

        return Inertia::render('Accountant/Expenses/Create', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    public function edit(Expense $expense)
    {
        $user = auth()->user()->load('roles');
        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
            ]);

        return Inertia::render('Accountant/Expenses/Create', [
            'user' => $user,
            'categories' => $categories,
            'expense' => [
                'id' => $expense->id,
                'expense_category_id' => $expense->expense_category_id,
                'vendor_name' => $expense->vendor_name,
                'description' => $expense->description,
                'expense_date' => optional($expense->expense_date)->format('Y-m-d'),
                'amount' => (float) $expense->amount,
                'payment_method' => $expense->payment_method,
                'receipt_number' => $expense->receipt_number,
                'notes' => $expense->notes,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,check,credit_card,bank_transfer',
            'receipt_number' => 'nullable|string|max:255',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        $receiptFilePath = null;
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $fileName = 'expense_' . time() . '_' . $file->getClientOriginalName();
            $receiptFilePath = $file->storeAs('expenses/receipts', $fileName, 'public');
        }

        $defaultCurrency = Setting::get('currency', 'USD');

        Expense::create([
            'expense_number' => $this->generateExpenseNumber(),
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_name' => $validated['vendor_name'] ?? null,
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'amount' => $validated['amount'],
            'currency' => $defaultCurrency,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'receipt_number' => $validated['receipt_number'] ?? null,
            'receipt_file_path' => $receiptFilePath,
            'status' => 'pending',
            'submitted_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('accountant.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,check,credit_card,bank_transfer',
            'receipt_number' => 'nullable|string|max:255',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        $receiptFilePath = $expense->receipt_file_path;
        if ($request->hasFile('receipt_file')) {
            if ($expense->receipt_file_path && \Storage::disk('public')->exists($expense->receipt_file_path)) {
                \Storage::disk('public')->delete($expense->receipt_file_path);
            }
            $file = $request->file('receipt_file');
            $fileName = 'expense_' . time() . '_' . $file->getClientOriginalName();
            $receiptFilePath = $file->storeAs('expenses/receipts', $fileName, 'public');
        }

        $defaultCurrency = Setting::get('currency', 'USD');

        $expense->update([
            'expense_category_id' => $validated['expense_category_id'],
            'vendor_name' => $validated['vendor_name'] ?? null,
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
            'amount' => $validated['amount'],
            'currency' => $expense->currency ?? $defaultCurrency,
            'payment_method' => $validated['payment_method'] ?? $expense->payment_method ?? 'cash',
            'receipt_number' => $validated['receipt_number'] ?? null,
            'receipt_file_path' => $receiptFilePath,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('accountant.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function approve(Expense $expense)
    {
        if ($expense->status === 'pending') {
            $expense->status = 'approved';
            $expense->approved_by = auth()->id();
            $expense->approved_at = Carbon::now();
            $expense->save();
        }

        return redirect()->route('accountant.expenses.index');
    }

    public function categories()
    {
        $user = auth()->user()->load('roles');

        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'color' => $category->color ?? '#3b82f6',
                    'expenses_count' => $category->expenses_count,
                ];
            });

        return Inertia::render('Accountant/Expenses/Categories', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    public function reports()
    {
        $user = auth()->user()->load('roles');
        $startMonth = Carbon::now()->subMonths(5)->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $monthlyTotals = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $total = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->where('status', '!=', 'rejected')
                ->sum('amount');

            $monthlyTotals[] = [
                'month' => $month->format('M Y'),
                'total' => (float) $total,
            ];
        }

        $categoryTotals = ExpenseCategory::withSum([
            'expenses as total_amount' => function ($query) use ($startMonth, $endMonth) {
                $query->whereBetween('expense_date', [$startMonth, $endMonth])
                    ->where('status', '!=', 'rejected');
            }
        ], 'amount')
            ->get()
            ->map(fn ($category) => [
                'name' => $category->name,
                'total' => (float) ($category->total_amount ?? 0),
                'color' => $category->color ?? '#3b82f6',
            ])
            ->filter(fn ($row) => $row['total'] > 0)
            ->values()
            ->all();

        $summary = [
            'period_start' => $startMonth->format('Y-m-d'),
            'period_end' => $endMonth->format('Y-m-d'),
            'total_expenses' => (float) Expense::whereBetween('expense_date', [$startMonth, $endMonth])
                ->where('status', '!=', 'rejected')
                ->sum('amount'),
        ];

        return Inertia::render('Accountant/Expenses/Reports', [
            'user' => $user,
            'summary' => $summary,
            'monthlyTotals' => $monthlyTotals,
            'categoryTotals' => $categoryTotals,
        ]);
    }

    private function generateExpenseNumber(): string
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

    public function export(Request $request)
    {
        // Build query with same filters as index
        $query = Expense::with('category')->orderByDesc('expense_date');

        // Apply filters if provided
        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        if ($request->get('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('date_range')) {
            $dateRange = $request->get('date_range');
            $now = Carbon::now();
            
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('expense_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('expense_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('expense_date', $now->month)
                          ->whereYear('expense_date', $now->year);
                    break;
                case 'quarter':
                    $query->whereBetween('expense_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('expense_date', $now->year);
                    break;
            }
        }

        // Get all expenses for export
        $expenses = $query->get();

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['Date', 'Description', 'Category', 'Vendor', 'Amount', 'Status', 'Reference Number'];

        foreach ($expenses as $expense) {
            $csvData[] = [
                optional($expense->expense_date)->format('Y-m-d'),
                $expense->description,
                $expense->category?->name ?? 'Uncategorized',
                $expense->vendor_name,
                $expense->amount,
                $expense->status,
                $expense->receipt_number,
            ];
        }

        // Generate filename
        $filename = 'expenses_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $csv = '';
        foreach ($csvData as $row) {
            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        // Return CSV download
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
