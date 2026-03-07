<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->get();

        return Inertia::render('Admin/POS/Categories/Index', [
            'categories' => $categories,
            'user' => auth()->user()->load('roles')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        ProductCategory::create($validated);

        return redirect()->route('admin.pos.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $category->id,
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return redirect()->route('admin.pos.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.pos.categories.index')
                ->with('error', 'Cannot delete category with existing products.');
        }

        $category->delete();

        return redirect()->route('admin.pos.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}