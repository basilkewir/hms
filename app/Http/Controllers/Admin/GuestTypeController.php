<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestTypeController extends Controller
{
    public function index()
    {
        $guestTypes = GuestType::orderBy('sort_order')->orderBy('name')->get();
        
        $guestTypes->loadCount('guests');

        $stats = [
            'total' => $guestTypes->count(),
            'active' => $guestTypes->where('is_active', true)->count(),
            'inactive' => $guestTypes->where('is_active', false)->count(),
            'totalGuests' => \App\Models\Guest::whereNotNull('guest_type_id')->count(),
        ];

        // Determine layout based on route name
        $routeName = request()->route()->getName() ?? '';
        $layout = str_starts_with($routeName, 'manager.') ? 'Manager' : 'Admin';

        return Inertia::render($layout . '/GuestTypes/Index', [
            'user' => auth()->user()->load('roles'),
            'guestTypes' => $guestTypes->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'description' => $type->description,
                    'color' => $type->color,
                    'discount_percentage' => $type->discount_percentage,
                    'is_active' => $type->is_active,
                    'sort_order' => $type->sort_order,
                    'guest_count' => $type->guests_count ?? 0,
                ];
            }),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        // Determine layout based on route name
        $routeName = request()->route()->getName() ?? '';
        $layout = str_starts_with($routeName, 'manager.') ? 'Manager' : 'Admin';

        return Inertia::render($layout . '/GuestTypes/Create', [
            'user' => auth()->user()->load('roles'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:guest_types,name',
            'code' => 'nullable|string|max:50|unique:guest_types,code',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        GuestType::create($validated);

        // Determine redirect route based on current route
        $routeName = request()->route()->getName() ?? '';
        $redirectRoute = str_starts_with($routeName, 'manager.') ? 'manager.guest-types.index' : 'admin.guest-types.index';

        return redirect()->route($redirectRoute)->with('success', 'Guest type created successfully');
    }

    public function edit(GuestType $guestType)
    {
        // Determine layout based on route name
        $routeName = request()->route()->getName() ?? '';
        $layout = str_starts_with($routeName, 'manager.') ? 'Manager' : 'Admin';

        return Inertia::render($layout . '/GuestTypes/Edit', [
            'user' => auth()->user()->load('roles'),
            'guestType' => [
                'id' => $guestType->id,
                'name' => $guestType->name,
                'code' => $guestType->code,
                'description' => $guestType->description,
                'color' => $guestType->color,
                'discount_percentage' => $guestType->discount_percentage,
                'is_active' => $guestType->is_active,
                'sort_order' => $guestType->sort_order,
            ],
        ]);
    }

    public function update(Request $request, GuestType $guestType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:guest_types,name,' . $guestType->id,
            'code' => 'nullable|string|max:50|unique:guest_types,code,' . $guestType->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $guestType->update($validated);

        // Determine redirect route based on current route
        $routeName = request()->route()->getName() ?? '';
        $redirectRoute = str_starts_with($routeName, 'manager.') ? 'manager.guest-types.index' : 'admin.guest-types.index';

        return redirect()->route($redirectRoute)->with('success', 'Guest type updated successfully');
    }

    public function destroy(GuestType $guestType)
    {
        // Determine redirect route based on current route
        $routeName = request()->route()->getName() ?? '';
        $redirectRoute = str_starts_with($routeName, 'manager.') ? 'manager.guest-types.index' : 'admin.guest-types.index';

        if ($guestType->guests()->count() > 0) {
            return redirect()->route($redirectRoute)->with('error', 'Cannot delete guest type that is in use');
        }

        $guestType->delete();

        return redirect()->route($redirectRoute)->with('success', 'Guest type deleted successfully');
    }
}
