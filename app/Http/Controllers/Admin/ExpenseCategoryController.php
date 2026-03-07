<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';
        
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
                    'expenses_count' => $category->expenses_count,
                ];
            });

        return Inertia::render('Admin/Expenses/Categories', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'code' => 'nullable|string|max:50|unique:expense_categories,code',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::slug($validated['name'], '_'));
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = '#3b82f6';
        }

        ExpenseCategory::create($validated);

        return redirect()->route('admin.expenses.categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id,
            'code' => 'nullable|string|max:50|unique:expense_categories,code,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::slug($validated['name'], '_'));
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = $category->color ?? '#3b82f6';
        }

        $category->update($validated);

        return redirect()->route('admin.expenses.categories')
            ->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            return redirect()->route('admin.expenses.categories.index')
                ->with('error', 'Cannot delete category with existing expenses.');
        }

        $category->delete();

        return redirect()->route('admin.expenses.categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}
