<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $customerGroups = CustomerGroup::withCount('customers')
            ->orderBy('name')
            ->paginate(15);

        $stats = [
            'total' => CustomerGroup::count(),
            'active' => CustomerGroup::where('is_active', true)->count(),
            'inactive' => CustomerGroup::where('is_active', false)->count(),
            'totalCustomers' => \App\Models\Customer::whereNotNull('customer_group_id')->count(),
        ];

        $user = auth()->user()->load('roles');
        $view = $user->hasRole('accountant') ? 'Accountant/CustomerGroups/Index' : 'Admin/CustomerGroups/Index';

        return Inertia::render($view, [
            'user' => $user,
            'customerGroups' => $customerGroups,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $user = auth()->user()->load('roles');
        $view = $user->hasRole('accountant') ? 'Accountant/CustomerGroups/Create' : 'Admin/CustomerGroups/Create';

        return Inertia::render($view, [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        CustomerGroup::create($validated);

        $user = auth()->user();
        $routePrefix = $user->hasRole('accountant') ? 'accountant' : 'admin';
        
        return redirect()->route($routePrefix . '.customer-groups.index')
            ->with('success', 'Customer group created successfully.');
    }

    public function show(CustomerGroup $customerGroup)
    {
        $customerGroup->load(['customers']);
        
        $user = auth()->user();
        $view = $user->hasRole('accountant') ? 'Accountant/CustomerGroups/Show' : 'Admin/CustomerGroups/Show';

        return Inertia::render($view, [
            'user' => $user,
            'customerGroup' => $customerGroup
        ]);
    }

    public function edit(CustomerGroup $customerGroup)
    {
        $user = auth()->user();
        $view = $user->hasRole('accountant') ? 'Accountant/CustomerGroups/Edit' : 'Admin/CustomerGroups/Edit';
        
        return Inertia::render($view, [
            'user' => $user,
            'customerGroup' => $customerGroup
        ]);
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name,' . $customerGroup->id,
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $customerGroup->update($validated);

        $user = auth()->user();
        $routePrefix = $user->hasRole('accountant') ? 'accountant' : 'admin';

        return redirect()->route($routePrefix . '.customer-groups.index')
            ->with('success', 'Customer group updated successfully.');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        if ($customerGroup->customers()->count() > 0) {
            $user = auth()->user();
            $routePrefix = $user->hasRole('accountant') ? 'accountant' : 'admin';
            
            return redirect()->route($routePrefix . '.customer-groups.index')
                ->with('error', 'Cannot delete customer group with existing customers.');
        }

        $customerGroup->delete();

        $user = auth()->user();
        $routePrefix = $user->hasRole('accountant') ? 'accountant' : 'admin';

        return redirect()->route($routePrefix . '.customer-groups.index')
            ->with('success', 'Customer group deleted successfully.');
    }

    public function getAll()
    {
        $groups = CustomerGroup::active()
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }
}
