<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceCategoryController extends Controller
{
    /**
     * Display a listing of the maintenance categories.
     */
    public function index()
    {
        $categories = MaintenanceCategory::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/MaintenanceCategories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $routeName = request()->route()->getName() ?? '';
        $routePrefix = str_starts_with($routeName, 'manager.') ? 'manager' : 'admin';
        return Inertia::render('Admin/MaintenanceCategories/Create', [
            'routePrefix' => $routePrefix,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:maintenance_categories,code',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        MaintenanceCategory::create($validated);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.maintenance-categories.index' : 'admin.maintenance-categories.index';
        return redirect()->route($indexRoute)->with('success', 'Maintenance category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(MaintenanceCategory $maintenanceCategory)
    {
        $routeName = request()->route()->getName() ?? '';
        $routePrefix = str_starts_with($routeName, 'manager.') ? 'manager' : 'admin';
        return Inertia::render('Admin/MaintenanceCategories/Edit', [
            'category'    => $maintenanceCategory,
            'routePrefix' => $routePrefix,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, MaintenanceCategory $maintenanceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:maintenance_categories,code,' . $maintenanceCategory->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $maintenanceCategory->update($validated);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.maintenance-categories.index' : 'admin.maintenance-categories.index';
        return redirect()->route($indexRoute)->with('success', 'Maintenance category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(MaintenanceCategory $maintenanceCategory)
    {
        $maintenanceCategory->delete();

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.maintenance-categories.index' : 'admin.maintenance-categories.index';
        return redirect()->route($indexRoute)->with('success', 'Maintenance category deleted successfully.');
    }

    /**
     * Toggle the active status of the category.
     */
    public function toggleActive(MaintenanceCategory $maintenanceCategory)
    {
        $maintenanceCategory->update([
            'is_active' => !$maintenanceCategory->is_active
        ]);

        $status = $maintenanceCategory->is_active ? 'activated' : 'deactivated';

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.maintenance-categories.index' : 'admin.maintenance-categories.index';
        return redirect()->route($indexRoute)->with('success', "Category {$status} successfully.");
    }
}
