<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $groupId = $request->input('group_id', '');
        $status = $request->input('status', '');

        $query = Customer::with('customerGroup')
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            })
            ->when($groupId, function ($q) use ($groupId) {
                $q->where('customer_group_id', $groupId);
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === 'active');
            });

        $customers = $query->orderBy('first_name')->orderBy('last_name')->paginate(15);

        $customerGroups = CustomerGroup::active()->get();
        
        $user = auth()->user();
        $user->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        
        // Determine which view to render based on route/role
        $isManager = $role === 'manager' && request()->routeIs('manager.*');
        $isAccountant = $role === 'accountant' && request()->routeIs('accountant.*');
        $isFrontDesk = $role === 'front_desk' && request()->routeIs('front-desk.*');
        
        $viewPath = 'Admin/Customers/Index';
        if ($isManager) {
            $viewPath = 'Manager/Customers/Index';
        } elseif ($isAccountant) {
            $viewPath = 'Accountant/Customers/Index';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Customers/Index';
        }

        return Inertia::render($viewPath, [
            'user' => $user,
            'customers' => $customers,
            'customerGroups' => $customerGroups,
            'filters' => [
                'search' => $search,
                'group_id' => $groupId,
                'status' => $status
            ]
        ]);
    }

    public function create()
    {
        $customerGroups = CustomerGroup::active()->get();
        
        $user = auth()->user();
        $user->load('roles');
        
        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $isAccountant = str_starts_with($routeName, 'accountant.');
        $isFrontDesk = str_starts_with($routeName, 'front-desk.');
        
        $viewPath = 'Admin/Customers/Create';
        if ($isManager) {
            $viewPath = 'Manager/Customers/Create';
        } elseif ($isAccountant) {
            $viewPath = 'Accountant/Customers/Create';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Customers/Create';
        }

        return Inertia::render($viewPath, [
            'user' => $user,
            'customerGroups' => $customerGroups
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        Customer::create($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.customers.index')
                ->with('success', 'Customer created successfully.');
        } elseif (str_starts_with($routeName, 'accountant.')) {
            return redirect()->route('accountant.customers.index')
                ->with('success', 'Customer created successfully.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.customers.index')
                ->with('success', 'Customer created successfully.');
        }
        
        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['customerGroup', 'sales.items.product', 'createdBy', 'updatedBy']);
        
        $user = auth()->user();
        $user->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        
        // Determine which view to render based on route/role
        $isManager = $role === 'manager' && request()->routeIs('manager.*');
        $isAccountant = $role === 'accountant' && request()->routeIs('accountant.*');
        $isFrontDesk = $role === 'front_desk' && request()->routeIs('front-desk.*');
        
        $viewPath = 'Admin/Customers/Show';
        if ($isManager) {
            $viewPath = 'Manager/Customers/Show';
        } elseif ($isAccountant) {
            $viewPath = 'Accountant/Customers/Show';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Customers/Show';
        }

        return Inertia::render($viewPath, [
            'user' => $user,
            'customer' => $customer
        ]);
    }

    public function edit(Customer $customer)
    {
        $customerGroups = CustomerGroup::active()->get();
        
        $user = auth()->user();
        $user->load('roles');
        
        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $isAccountant = str_starts_with($routeName, 'accountant.');
        $isFrontDesk = str_starts_with($routeName, 'front-desk.');
        
        $viewPath = 'Admin/Customers/Edit';
        if ($isManager) {
            $viewPath = 'Manager/Customers/Edit';
        } elseif ($isAccountant) {
            $viewPath = 'Accountant/Customers/Edit';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Customers/Edit';
        }

        return Inertia::render($viewPath, [
            'user' => $user,
            'customer' => $customer,
            'customerGroups' => $customerGroups
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.customers.index')
                ->with('success', 'Customer updated successfully.');
        } elseif (str_starts_with($routeName, 'accountant.')) {
            return redirect()->route('accountant.customers.index')
                ->with('success', 'Customer updated successfully.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.customers.index')
                ->with('success', 'Customer updated successfully.');
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } elseif (str_starts_with($routeName, 'accountant.')) {
            return redirect()->route('accountant.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.customers.index')
                ->with('success', 'Customer deleted successfully.');
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function getAll()
    {
        $customers = Customer::active()
            ->with('customerGroup')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json($customers);
    }
}
