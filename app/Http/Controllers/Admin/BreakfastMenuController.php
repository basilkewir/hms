<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakfastMenu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BreakfastMenuController extends Controller
{
    public function index()
    {
        $menus = BreakfastMenu::orderBy('sort_order')->get();
        
        return Inertia::render('Admin/Breakfast/Index', [
            'menus' => $menus,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'serving_time_start' => 'nullable|string',
            'serving_time_end' => 'nullable|string',
            'is_active' => 'boolean',
            'available_online' => 'boolean',
        ]);

        BreakfastMenu::create($validated);

        return redirect()->back()->with('success', 'Breakfast menu created successfully');
    }

    public function update(Request $request, BreakfastMenu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'serving_time_start' => 'nullable|string',
            'serving_time_end' => 'nullable|string',
            'is_active' => 'boolean',
            'available_online' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->back()->with('success', 'Breakfast menu updated successfully');
    }

    public function destroy(BreakfastMenu $menu)
    {
        $menu->delete();

        return redirect()->back()->with('success', 'Breakfast menu deleted successfully');
    }
}
