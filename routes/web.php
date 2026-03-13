<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR\TrainingProgramController;
use App\Models\TrainingProgram;
use App\Http\Controllers\POS\POSController;
use App\Http\Controllers\POS\SupplierController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\FrontDesk\CheckOutController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\BuildingWingController;
use App\Http\Controllers\Admin\BedTypeController;
use App\Http\Controllers\Admin\HotelServiceController;
use App\Http\Controllers\Admin\LaundryController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\GroupBookingController;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\HallBookingController;
use App\Http\Controllers\Admin\HousekeepingController;
use App\Http\Controllers\Admin\MaintenanceCategoryController;
use App\Http\Controllers\Admin\MaintenanceRequestController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Disable registration — this system does not allow self-registration
Route::get('/register', function () {
    return redirect()->route('login');
});
Route::post('/register', function () {
    abort(403, 'Registration is disabled.');
});

// License Activation Gate (public — accessible when system is unlicensed)
Route::get('/license/activate', function () {
    $trial = app(\App\Services\LicenseValidationService::class)->getTrialStatus();
    return Inertia::render('Auth/LicenseActivate', ['trial' => $trial]);
})->name('license.activate');

Route::post('/license/activate', function (Request $request) {
    $request->validate([
        'license_key' => 'required|string|min:10',
        'hotel_name'  => 'nullable|string|max:255',
    ]);

    $service = app(\App\Services\LicenseValidationService::class);
    $result  = $service->validateLicense(
        $request->license_key,
        $request->hotel_name ?: config('app.name')
    );

    \Illuminate\Support\Facades\Cache::forget('license_valid');

    if (!$result['valid']) {
        return back()->withErrors(['license_key' => $result['message']])->withInput();
    }

    return redirect()->intended(route('login'))->with('success', 'License activated! Please log in.');
})->name('license.activate.store');

// General User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Redirect to role-specific dashboard
        return match($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            'front_desk' => redirect()->route('front-desk.dashboard'),
            'accountant' => redirect()->route('accountant.dashboard'),
            'housekeeping' => redirect()->route('housekeeping.dashboard'),
            'maintenance' => redirect()->route('maintenance.dashboard'),
            'hr' => redirect()->route('hr.dashboard'),
            'bartender' => redirect()->route('bartender.dashboard'),
            'server', 'restaurant_staff' => redirect()->route('server.dashboard'),
            default => redirect()->route('login')
        };
    })->name('dashboard');

    Route::get('/user/profile', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'sessions' => collect(request()->session()->get('recent_sessions', []))->map(function ($session) {
                return [
                    'id' => $session['id'] ?? null,
                    'ip_address' => $session['ip_address'] ?? null,
                    'user_agent' => $session['user_agent'] ?? null,
                    'last_activity' => $session['last_activity'] ?? null,
                    'agent' => [
                        'is_desktop' => !empty($session['user_agent']) && !preg_match('/Mobile|Android|iPhone|iPad/i', $session['user_agent']),
                        'platform' => !empty($session['user_agent']) ? 'Unknown' : 'Unknown',
                        'browser' => !empty($session['user_agent']) ? 'Unknown' : 'Unknown',
                    ],
                ];
            })->toArray()
        ]);
    })->name('profile.show');
});

// Admin Routes
Route::middleware(['auth', 'role:admin|manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get all users with their roles
        $users = \App\Models\User::with('roles')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(50);

        return Inertia::render('Admin/Users/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'users' => $users
        ]);
    })->name('users.index');

    Route::get('/users/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Users/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('users.create');

    // Single user profile
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');

    // Roles
    Route::get('/roles', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Roles/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('roles.index');

    // Roles API endpoints for Roles & Permissions page
    Route::get('/roles/api', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.api');
    Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles/permissions/all', [\App\Http\Controllers\Admin\RoleController::class, 'getPermissions'])->name('roles.permissions.all');
    Route::get('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'getRolePermissions'])->name('roles.permissions.get');
    Route::put('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Guest Types
    Route::get('/guest-types', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $guestTypes = \App\Models\GuestType::query()
            ->withCount('guests')
            ->orderBy('name')
            ->get()
            ->map(function ($gt) {
                return [
                    'id' => $gt->id,
                    'name' => $gt->name,
                    'code' => $gt->code,
                    'description' => $gt->description,
                    'color' => $gt->color,
                    'discount_percentage' => $gt->discount_percentage,
                    'is_active' => (bool) $gt->is_active,
                    'guest_count' => $gt->guests_count,
                ];
            });

        $stats = [
            'total' => $guestTypes->count(),
            'active' => $guestTypes->where('is_active', true)->count(),
            'inactive' => $guestTypes->where('is_active', false)->count(),
            'totalGuests' => $guestTypes->sum('guest_count'),
        ];

        return Inertia::render('Admin/GuestTypes/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guestTypes' => $guestTypes,
            'stats' => $stats,
        ]);
    })->name('guest-types.index');

    Route::get('/guest-types/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('Admin/GuestTypes/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('guest-types.create');

    Route::post('/guest-types', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        \App\Models\GuestType::create($validated);
        return redirect()->route('admin.guest-types.index')->with('success', 'Guest Type created.');
    })->name('guest-types.store');

    Route::get('/guest-types/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $guestType = \App\Models\GuestType::findOrFail($id);
        return Inertia::render('Admin/GuestTypes/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guestType' => $guestType,
        ]);
    })->name('guest-types.edit');

    Route::put('/guest-types/{id}', function ($id, \Illuminate\Http\Request $request) {
        $guestType = \App\Models\GuestType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $guestType->update($validated);
        return redirect()->route('admin.guest-types.index')->with('success', 'Guest Type updated.');
    })->name('guest-types.update');

    Route::delete('/guest-types/{id}', function ($id) {
        $guestType = \App\Models\GuestType::findOrFail($id);
        // Simple safe delete (could add constraints checks)
        $guestType->delete();
        return back()->with('success', 'Guest Type deleted.');
    })->name('guest-types.destroy');

    // Memberships
    Route::get('/memberships', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Load from dedicated loyalty_memberships table
        $tiers = \App\Models\LoyaltyMembership::query()
            ->orderBy('min_points')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'min_points' => $m->min_points,
                    'discount_percentage' => $m->discount_percentage,
                    'is_active' => (bool) $m->is_active,
                    'members_count' => 0,
                ];
            });

        $stats = [
            'total' => $tiers->count(),
            'active_members' => 0,
            'rewards_redeemed' => 0,
            'points_issued' => 0,
        ];

        return Inertia::render('Admin/Memberships/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'memberships' => $tiers,
            'stats' => $stats,
        ]);
    })->name('memberships.index');

    Route::get('/memberships/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Memberships/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('memberships.create');

    Route::post('/memberships', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_points' => 'required|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        \App\Models\LoyaltyMembership::create($validated);
        return redirect()->route('admin.memberships.index')->with('success', 'Membership created.');
    })->name('memberships.store');

    Route::get('/memberships/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $m = \App\Models\LoyaltyMembership::findOrFail($id);
        $membership = [
            'id' => $m->id,
            'name' => $m->name,
            'min_points' => $m->min_points,
            'discount_percentage' => $m->discount_percentage,
            'is_active' => (bool) $m->is_active,
            'description' => $m->description,
        ];

        return Inertia::render('Admin/Memberships/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'membership' => $membership,
        ]);
    })->name('memberships.edit');

    Route::put('/memberships/{id}', function ($id, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_points' => 'required|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $m = \App\Models\LoyaltyMembership::findOrFail($id);
        $m->update($validated);
        return redirect()->route('admin.memberships.index')->with('success', 'Membership updated.');
    })->name('memberships.update');

    Route::delete('/memberships/{id}', function ($id) {
        $m = \App\Models\LoyaltyMembership::findOrFail($id);
        $m->delete();
        return back()->with('success', 'Membership deleted.');
    })->name('memberships.destroy');

    // Customers
    Route::get('/customers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $search = request('search');
        $groupId = request('group_id');
        $status = request('status');

        $query = \App\Models\Customer::with(['customerGroup'])
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        if ($groupId) {
            $query->where('customer_group_id', $groupId);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $customers = $query->paginate(50)->withQueryString();

        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id', 'name', 'discount_percentage', 'is_active']);

        return Inertia::render('Admin/Customers/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customers' => $customers,
            'customerGroups' => $customerGroups,
            'filters' => [
                'search' => $search,
                'group_id' => $groupId,
                'status' => $status,
            ],
        ]);
    })->name('customers.index');

    // Customers - Create
    Route::get('/customers/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id','name']);

        return Inertia::render('Admin/Customers/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroups' => $customerGroups,
        ]);
    })->name('customers.create');

    // Customers - Store
    Route::post('/customers', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = auth()->id();

        \App\Models\Customer::create($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    })->name('customers.store');

    // Customers - Show
    Route::get('/customers/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customer = \App\Models\Customer::with(['customerGroup'])->findOrFail($id);

        return Inertia::render('Admin/Customers/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customer' => $customer,
        ]);
    })->name('customers.show');

    // Customers - Edit
    Route::get('/customers/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customer = \App\Models\Customer::with(['customerGroup'])->findOrFail($id);
        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id','name','discount_percentage']);

        return Inertia::render('Admin/Customers/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customer' => $customer,
            'customerGroups' => $customerGroups,
        ]);
    })->name('customers.edit');

    // Customers - Update
    Route::put('/customers/{id}', function ($id, \Illuminate\Http\Request $request) {
        $customer = \App\Models\Customer::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer->id)->with('success', 'Customer updated successfully.');
    })->name('customers.update');

    // Users - Edit
    Route::get('/users/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $editUser = \App\Models\User::with('roles')->findOrFail($id);

        return Inertia::render('Admin/Users/Edit', [
            'authUser' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'user' => $editUser,
            'roles' => \App\Models\Role::select('id','name','display_name')->orderBy('name')->get(),
        ]);
    })->name('users.edit');

    Route::put('/users/{id}', function ($id, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $u = \App\Models\User::findOrFail($id);
        $u->update($validated);
        return redirect()->route('admin.users.show', ['user' => $u->id])->with('success', 'User updated.');
    })->name('users.update');

    // Customer Groups
    Route::get('/customer-groups', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $groupsQuery = \App\Models\CustomerGroup::withCount('customers')
            ->orderBy('name');

        $customerGroups = $groupsQuery->paginate(20)->withQueryString();

        $allGroups = \App\Models\CustomerGroup::withCount('customers')->get();
        $stats = [
            'total' => $allGroups->count(),
            'active' => $allGroups->where('is_active', true)->count(),
            'inactive' => $allGroups->where('is_active', false)->count(),
            'totalCustomers' => $allGroups->sum('customers_count'),
        ];

        return Inertia::render('Admin/CustomerGroups/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroups' => $customerGroups,
            'stats' => $stats,
        ]);
    })->name('customer-groups.index');

    // Customer Groups additional routes used by frontend links
    Route::get('/customer-groups/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/CustomerGroups/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('customer-groups.create');

    // Customer Groups - Store
    Route::post('/customer-groups', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        \App\Models\CustomerGroup::create($validated);

        return redirect()->route('admin.customer-groups.index')->with('success', 'Customer group created successfully.');
    })->name('customer-groups.store');

    Route::get('/customer-groups/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $group = \App\Models\CustomerGroup::with(['customers' => function($q){ $q->select('id','first_name','last_name','customer_code'); }])->findOrFail($id);

        return Inertia::render('Admin/CustomerGroups/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroup' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'discount_percentage' => $group->discount_percentage,
                'is_active' => (bool) ($group->is_active ?? true),
                'customers' => $group->customers ?? [],
            ],
        ]);
    })->name('customer-groups.show');

    Route::get('/customer-groups/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $group = \App\Models\CustomerGroup::findOrFail($id);

        return Inertia::render('Admin/CustomerGroups/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroup' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'discount_percentage' => $group->discount_percentage,
                'is_active' => (bool) ($group->is_active ?? true),
            ],
        ]);
    })->name('customer-groups.edit');

    Route::delete('/customer-groups/{id}', function ($id) {
        // TODO: Implement deletion when backend logic is ready
        return back()->with('error', 'Deletion not implemented yet.');
    })->name('customer-groups.destroy');

    Route::get('/customer-groups/export/{format}', function ($format) {
        // TODO: Implement export. Stub to satisfy Ziggy route resolution.
        return response()->noContent();
    })->name('customer-groups.export');

    // Reports Dashboard
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = [
            'total_customers' => 0,
            'total_sales' => 0,
            'revenue_today' => 0,
            'revenue_month' => 0,
        ];

        $recentSales = [];
        $recentCustomers = [];

        // Safely compute stats if models exist
        try {
            if (class_exists(\App\Models\Customer::class)) {
                $stats['total_customers'] = \App\Models\Customer::count();
                $recentCustomers = \App\Models\Customer::orderByDesc('created_at')
                    ->limit(5)
                    ->get(['id','first_name','last_name','customer_code','created_at'])
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'first_name' => $c->first_name,
                        'last_name' => $c->last_name,
                        'customer_code' => $c->customer_code,
                        'created_at' => $c->created_at,
                    ]);
            }

            if (class_exists(\App\Models\Sale::class)) {
                $stats['total_sales'] = \App\Models\Sale::count();
                $today        = now()->toDateString();
                $startOfMonth = now()->startOfMonth()->toDateString();
                $endOfMonth   = now()->endOfMonth()->toDateString();
                $stats['revenue_today'] = (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
                $stats['revenue_month'] = (float) \App\Models\Sale::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->sum('total_amount');

                $recentSales = \App\Models\Sale::orderByDesc('created_at')
                    ->limit(5)
                    ->get(['id','sale_number','sale_date','created_at','total_amount'])
                    ->map(fn($s) => [
                        'id'           => $s->id,
                        'sale_number'  => $s->sale_number,
                        'sale_date'    => $s->sale_date ?? $s->created_at,
                        'total_amount' => (float) $s->total_amount,
                    ]);
            }
        } catch (\Throwable $e) {
            // Swallow errors; show zeros/defaults
        }

        return Inertia::render('Admin/Reports/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'recentSales' => $recentSales,
            'recentCustomers' => $recentCustomers,
        ]);
    })->name('reports.index');

    // Reports - Occupancy
    Route::get('/reports/occupancy', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = [
            'total_rooms' => 0,
            'occupied_today' => 0,
            'available_today' => 0,
            'occupancy_today_pct' => 0.0,
            'occupancy_month_pct' => 0.0,
        ];
        $byType = [];
        $recentReservations = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\Room::class)) {
                $stats['total_rooms'] = \App\Models\Room::count();

                // Rooms by type with occupied count
                if (\Schema::hasColumn('rooms', 'type')) {
                    // Get room_ids occupied today
                    $occupiedRoomIds = class_exists(\App\Models\Reservation::class)
                        ? \App\Models\Reservation::query()
                            ->when(\Schema::hasColumn('reservations','check_in_date'), fn($q) => $q->where('check_in_date','<=',$today)->where('check_out_date','>',$today))
                            ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status',['cancelled','canceled']))
                            ->whereNotNull('room_id')
                            ->pluck('room_id')
                            ->unique()
                            ->values()
                            ->toArray()
                        : [];

                    $byType = \App\Models\Room::select('type', \DB::raw('count(*) as total'))
                        ->groupBy('type')
                        ->get()
                        ->map(function ($r) use ($occupiedRoomIds) {
                            $occupied = \App\Models\Room::where('type', $r->type)
                                ->whereIn('id', $occupiedRoomIds)
                                ->count();
                            return [
                                'type'          => $r->type,
                                'total'         => (int) $r->total,
                                'occupied'      => $occupied,
                                'occupancy_pct' => $r->total > 0 ? round(($occupied / $r->total) * 100, 1) : 0.0,
                            ];
                        })->toArray();
                } elseif (\Schema::hasColumn('rooms', 'room_type_id')) {
                    // Fallback: group by room_type_id, join with room_types table if exists
                    $byType = \App\Models\Room::select('room_type_id', \DB::raw('count(*) as total'))
                        ->groupBy('room_type_id')
                        ->with('roomType:id,name')
                        ->get()
                        ->map(fn($r) => [
                            'type'          => $r->roomType?->name ?? 'Type '.$r->room_type_id,
                            'total'         => (int) $r->total,
                            'occupied'      => 0,
                            'occupancy_pct' => 0.0,
                        ])->toArray();
                }
            }

            if (class_exists(\App\Models\Reservation::class)) {
                // Occupied today: reservations overlapping today and not cancelled
                $occupiedTodayQuery = \App\Models\Reservation::query();
                if (\Schema::hasColumn('reservations', 'check_in_date') && \Schema::hasColumn('reservations', 'check_out_date')) {
                    $occupiedTodayQuery->where('check_in_date', '<=', $today)
                        ->where('check_out_date', '>', $today);
                }
                if (\Schema::hasColumn('reservations', 'status')) {
                    $occupiedTodayQuery->whereNotIn('status', ['cancelled', 'canceled']);
                }
                $stats['occupied_today'] = (int) $occupiedTodayQuery->distinct('room_id')->count('room_id');
                $stats['available_today'] = max(0, $stats['total_rooms'] - $stats['occupied_today']);
                $stats['occupancy_today_pct'] = $stats['total_rooms'] > 0 ? round(($stats['occupied_today'] / $stats['total_rooms']) * 100, 1) : 0.0;

                // Monthly occupancy approximation using room-nights
                if ($stats['total_rooms'] > 0 && \Schema::hasColumn('reservations', 'check_in_date') && \Schema::hasColumn('reservations', 'check_out_date')) {
                    $monthNights = (int) now()->endOfMonth()->day;
                    $totalRoomNights = $stats['total_rooms'] * $monthNights;

                    $occupiedNights = (int) \App\Models\Reservation::query()
                        ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('check_in_date', [$startOfMonth, $endOfMonth])
                              ->orWhereBetween('check_out_date', [$startOfMonth, $endOfMonth])
                              ->orWhere(function ($qq) use ($startOfMonth, $endOfMonth) {
                                  $qq->where('check_in_date', '<=', $startOfMonth)
                                     ->where('check_out_date', '>=', $endOfMonth);
                              });
                        })
                        ->when(\Schema::hasColumn('reservations', 'status'), function ($q) {
                            $q->whereNotIn('status', ['cancelled', 'canceled']);
                        })
                        ->sum(\DB::raw('DATEDIFF(LEAST(check_out_date, "'.$endOfMonth.'"), GREATEST(check_in_date, "'.$startOfMonth.'"))'));

                    $stats['occupancy_month_pct'] = $totalRoomNights > 0 ? round(($occupiedNights / $totalRoomNights) * 100, 1) : 0.0;
                }

                // Recent reservations list
                $recentReservations = \App\Models\Reservation::orderByDesc('created_at')
                    ->limit(8)
                    ->get(['id','reservation_code','guest_name','room_id','check_in_date','check_out_date','status'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'reservation_code' => $r->reservation_code ?? $r->id,
                        'guest_name' => $r->guest_name ?? null,
                        'room_id' => $r->room_id,
                        'check_in_date' => $r->check_in_date,
                        'check_out_date' => $r->check_out_date,
                        'status' => $r->status ?? 'booked',
                    ]);
            }
        } catch (\Throwable $e) {
            // Keep defaults
        }

        return Inertia::render('Admin/Reports/Occupancy', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'byType' => $byType,
            'recentReservations' => $recentReservations,
        ]);
    })->name('reports.occupancy');

    // Reports - Occupancy Export
    Route::get('/reports/occupancy/export/{format}', function ($format) {
        // Reuse the same computation as the occupancy page
        $stats = [
            'total_rooms' => 0,
            'occupied_today' => 0,
            'available_today' => 0,
            'occupancy_today_pct' => 0.0,
            'occupancy_month_pct' => 0.0,
        ];
        $byType = [];
        $recentReservations = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\Room::class)) {
                $stats['total_rooms'] = \App\Models\Room::count();
                if (\Schema::hasColumn('rooms', 'type')) {
                    $byType = \App\Models\Room::select('type', \DB::raw('count(*) as total'))
                        ->groupBy('type')
                        ->get()
                        ->map(fn($r) => ['type' => $r->type, 'total' => (int) $r->total])
                        ->toArray();
                }
            }

            if (class_exists(\App\Models\Reservation::class)) {
                $occupiedTodayQuery = \App\Models\Reservation::query();
                if (\Schema::hasColumn('reservations', 'check_in_date') && \Schema::hasColumn('reservations', 'check_out_date')) {
                    $occupiedTodayQuery->where('check_in_date', '<=', $today)
                        ->where('check_out_date', '>', $today);
                }
                if (\Schema::hasColumn('reservations', 'status')) {
                    $occupiedTodayQuery->whereNotIn('status', ['cancelled', 'canceled']);
                }
                $stats['occupied_today'] = (int) $occupiedTodayQuery->distinct('room_id')->count('room_id');
                $stats['available_today'] = max(0, $stats['total_rooms'] - $stats['occupied_today']);
                $stats['occupancy_today_pct'] = $stats['total_rooms'] > 0 ? round(($stats['occupied_today'] / $stats['total_rooms']) * 100, 1) : 0.0;

                if ($stats['total_rooms'] > 0 && \Schema::hasColumn('reservations', 'check_in_date') && \Schema::hasColumn('reservations', 'check_out_date')) {
                    $monthNights = (int) now()->endOfMonth()->day;
                    $totalRoomNights = $stats['total_rooms'] * $monthNights;
                    $occupiedNights = (int) \App\Models\Reservation::query()
                        ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('check_in_date', [$startOfMonth, $endOfMonth])
                              ->orWhereBetween('check_out_date', [$startOfMonth, $endOfMonth])
                              ->orWhere(function ($qq) use ($startOfMonth, $endOfMonth) {
                                  $qq->where('check_in_date', '<=', $startOfMonth)
                                     ->where('check_out_date', '>=', $endOfMonth);
                              });
                        })
                        ->when(\Schema::hasColumn('reservations', 'status'), function ($q) {
                            $q->whereNotIn('status', ['cancelled', 'canceled']);
                        })
                        ->sum(\DB::raw('DATEDIFF(LEAST(check_out_date, "'.$endOfMonth.'"), GREATEST(check_in_date, "'.$startOfMonth.'"))'));
                    $stats['occupancy_month_pct'] = $totalRoomNights > 0 ? round(($occupiedNights / $totalRoomNights) * 100, 1) : 0.0;
                }

                $recentReservations = \App\Models\Reservation::orderByDesc('created_at')
                    ->limit(50)
                    ->get(['id','reservation_code','guest_name','room_id','check_in_date','check_out_date','status'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'reservation_code' => $r->reservation_code ?? $r->id,
                        'guest_name' => $r->guest_name ?? null,
                        'room_id' => $r->room_id,
                        'check_in_date' => $r->check_in_date,
                        'check_out_date' => $r->check_out_date,
                        'status' => $r->status ?? 'booked',
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'byType' => $byType,
                'recentReservations' => $recentReservations,
            ]);
        }

        // default CSV
        $csv = fopen('php://temp', 'w+');
        // Stats
        fputcsv($csv, ['Metric', 'Value']);
        foreach ($stats as $k => $v) {
            fputcsv($csv, [$k, $v]);
        }
        fputcsv($csv, []);
        // By Type
        fputcsv($csv, ['Room Type', 'Total Rooms']);
        foreach ($byType as $row) {
            fputcsv($csv, [$row['type'], $row['total']]);
        }
        fputcsv($csv, []);
        // Recent Reservations
        fputcsv($csv, ['Reservation Code', 'Guest Name', 'Room ID', 'Check In', 'Check Out', 'Status']);
        foreach ($recentReservations as $r) {
            fputcsv($csv, [
                $r['reservation_code'],
                $r['guest_name'],
                $r['room_id'],
                $r['check_in_date'],
                $r['check_out_date'],
                $r['status'],
            ]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'occupancy-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.occupancy.export');

    // Reports - Revenue
    Route::get('/reports/revenue', function () {
        $user  = auth()->user()->load('roles');
        $role  = $user->roles->first()?->name ?? 'staff';
        $start = request('start_date') ?: now()->subDays(29)->toDateString();
        $end   = request('end_date')   ?: now()->toDateString();
        $fmt   = fn($v) => number_format((float)$v, 2);

        $stats         = ['start_date' => $start, 'end_date' => $end, 'total_revenue' => 0.0, 'total_sales' => 0, 'avg_order_value' => 0.0];
        $daily         = [];
        $recentSales   = [];
        $posRevenue    = 0.0;
        $roomRevenue   = 0.0;
        $hallRevenue   = 0.0;
        $revByCategory = [];
        $totalExpenses = 0.0;
        $expByCategory = [];

        try {
            // ── POS Sales ─────────────────────────────────────────────
            if (class_exists(\App\Models\Sale::class)) {
                $base = \App\Models\Sale::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
                $stats['total_sales']     = (int)   $base->count();
                // Pre-tax: subtract tax_amount from total_amount
                $posRevenue               = (float) (\App\Models\Sale::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->selectRaw('COALESCE(SUM(total_amount - COALESCE(tax_amount, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue']   = $posRevenue;
                $stats['avg_order_value'] = $stats['total_sales'] > 0 ? round($posRevenue / $stats['total_sales'], 2) : 0.0;

                $daily = \App\Models\Sale::selectRaw('DATE(created_at) as d, COUNT(*) as orders, SUM(total_amount - COALESCE(tax_amount, 0)) as revenue')
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->groupBy('d')->orderBy('d')->get()
                    ->map(fn($r) => ['date' => (string)$r->d, 'orders' => (int)$r->orders, 'revenue' => (float)$r->revenue])->toArray();

                $recentSales = \App\Models\Sale::orderByDesc('created_at')->limit(10)
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->get(['id','sale_number','sale_date','total_amount','tax_amount','created_at'])
                    ->map(fn($s) => ['id' => $s->id, 'sale_number' => $s->sale_number, 'sale_date' => $s->sale_date ?? $s->created_at, 'total_amount' => (float)($s->total_amount - ($s->tax_amount ?? 0))]);
            }

            // ── Room Revenue (Reservations) ───────────────────────────
            if (class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'total_amount')) {
                // Pre-tax: subtract taxes column from total_amount
                $roomRevenue = (float) (\App\Models\Reservation::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))
                    ->selectRaw('COALESCE(SUM(total_amount - COALESCE(taxes, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue'] += $roomRevenue;
            }

            // ── Hall Booking Revenue ──────────────────────────────────
            if (class_exists(\App\Models\HallBooking::class)) {
                $hallRevenue = (float) \App\Models\HallBooking::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->sum('total_amount');
                $stats['total_revenue'] += $hallRevenue;
            }

            $revByCategory = array_values(array_filter([
                $roomRevenue > 0 ? ['category' => 'Room Revenue', 'amount' => $roomRevenue, 'formatted_amount' => $fmt($roomRevenue)] : null,
                $hallRevenue > 0 ? ['category' => 'Hall Revenue', 'amount' => $hallRevenue, 'formatted_amount' => $fmt($hallRevenue)] : null,
                $posRevenue  > 0 ? ['category' => 'POS / F&B',   'amount' => $posRevenue,  'formatted_amount' => $fmt($posRevenue)]  : null,
            ]));

            // ── Expenses ──────────────────────────────────────────────
            if (class_exists(\App\Models\Expense::class)) {
                $totalExpenses = (float) \App\Models\Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
                $expByCategory = \App\Models\Expense::selectRaw('expense_category_id, SUM(amount) as total')
                    ->whereBetween('expense_date', [$start, $end])
                    ->groupBy('expense_category_id')->with('category:id,name')->get()
                    ->map(fn($e) => ['category' => $e->category?->name ?? 'Uncategorized', 'amount' => (float)$e->total, 'formatted_amount' => $fmt($e->total)])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        $netProfit = $stats['total_revenue'] - $totalExpenses;
        $netMargin = $stats['total_revenue'] > 0 ? round(($netProfit / $stats['total_revenue']) * 100, 1) : 0.0;
        $adr       = $stats['total_sales'] > 0 ? round($stats['total_revenue'] / $stats['total_sales'], 2) : 0.0;
        $currency  = ['code' => \App\Models\Setting::get('currency', 'USD'), 'symbol' => '', 'position' => 'before'];

        return Inertia::render('Admin/Reports/Revenue', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'stats'          => $stats,
            'daily'          => $daily,
            'recentSales'    => $recentSales,
            'revenueData'    => ['total_revenue' => $stats['total_revenue'], 'room_revenue' => $roomRevenue, 'hall_revenue' => $hallRevenue, 'average_daily_rate' => $adr, 'revenue_by_category' => $revByCategory, 'currency' => $currency],
            'expenseData'    => ['total_expenses' => $totalExpenses, 'expenses_by_category' => $expByCategory, 'currency' => $currency],
            'profitLossData' => ['net_profit' => $netProfit, 'net_margin' => $netMargin],
            'dateRange'      => ['start' => $start, 'end' => $end],
        ]);
    })->name('reports.revenue');

    // Reports - Financial (high-level KPIs)
    Route::get('/reports/financial', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $kpis = [
            'total_revenue' => 0.0,
            'total_expenses' => 0.0,
            'net_profit' => 0.0,
            'net_margin' => 0.0,
        ];
        $recentTransactions = [];

        try {
            // Revenue from sales
            if (class_exists(\App\Models\Sale::class)) {
                // Pre-tax revenue: exclude tax_amount
                $kpis['total_revenue'] += (float) (\App\Models\Sale::selectRaw('COALESCE(SUM(total_amount - COALESCE(tax_amount, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $recentTransactions = \App\Models\Sale::orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id','sale_number','total_amount','tax_amount','created_at'])
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'type' => 'sale',
                        'ref' => $s->sale_number,
                        'amount' => (float) ($s->total_amount - ($s->tax_amount ?? 0)),
                        'created_at' => $s->created_at,
                    ]);
            }

            // Revenue from hall bookings
            if (class_exists(\App\Models\HallBooking::class)) {
                $hallRevenue = (float) \App\Models\HallBooking::whereIn('status', ['confirmed', 'completed'])->sum('total_amount');
                $kpis['total_revenue'] += $hallRevenue;
                $kpis['hall_booking_revenue'] = $hallRevenue;
                $recentHallBookings = \App\Models\HallBooking::whereIn('status', ['confirmed', 'completed'])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id','booking_number','total_amount','created_at'])
                    ->map(fn($b) => [
                        'id' => $b->id,
                        'type' => 'hall_booking',
                        'ref' => $b->booking_number,
                        'amount' => (float) $b->total_amount,
                        'created_at' => $b->created_at,
                    ]);
                $recentTransactions = collect($recentTransactions)->merge($recentHallBookings)->sortByDesc('created_at')->values();
            }

            // Expenses (optional: use an Expense model if it exists)
            if (class_exists(\App\Models\Expense::class)) {
                $kpis['total_expenses'] = (float) \App\Models\Expense::sum('amount');
                $recentExpenses = \App\Models\Expense::orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id','reference','amount','created_at'])
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'type' => 'expense',
                        'ref' => $e->reference,
                        'amount' => (float) $e->amount,
                        'created_at' => $e->created_at,
                    ]);
                $recentTransactions = collect($recentTransactions)->merge($recentExpenses)->sortByDesc('created_at')->values();
            }

            $kpis['net_profit'] = round($kpis['total_revenue'] - $kpis['total_expenses'], 2);
            $kpis['net_margin'] = $kpis['total_revenue'] > 0 ? round(($kpis['net_profit'] / $kpis['total_revenue']) * 100, 1) : 0.0;
        } catch (\Throwable $e) {
            // keep defaults
        }

        return Inertia::render('Admin/Reports/Financial', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'kpis' => $kpis,
            'recentTransactions' => $recentTransactions,
        ]);
    })->name('reports.financial');

    // Reports - Financial Export
    Route::get('/reports/financial/export/{format}', function ($format) {
        $kpis = [
            'total_revenue' => 0.0,
            'total_expenses' => 0.0,
            'net_profit' => 0.0,
            'net_margin' => 0.0,
        ];
        $recentTransactions = [];

        try {
            if (class_exists(\App\Models\Sale::class)) {
                // Pre-tax revenue: exclude tax_amount
                $kpis['total_revenue'] += (float) (\App\Models\Sale::selectRaw('COALESCE(SUM(total_amount - COALESCE(tax_amount, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $recentTransactions = \App\Models\Sale::orderByDesc('created_at')
                    ->limit(100)
                    ->get(['id','sale_number','total_amount','tax_amount','created_at'])
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'type' => 'sale',
                        'ref' => $s->sale_number,
                        'amount' => (float) ($s->total_amount - ($s->tax_amount ?? 0)),
                        'created_at' => $s->created_at,
                    ])->toArray();
            }
            if (class_exists(\App\Models\HallBooking::class)) {
                $hallRevenue = (float) \App\Models\HallBooking::whereIn('status', ['confirmed', 'completed'])->sum('total_amount');
                $kpis['total_revenue'] += $hallRevenue;
                $kpis['hall_booking_revenue'] = $hallRevenue;
                $hallTxns = \App\Models\HallBooking::whereIn('status', ['confirmed', 'completed'])
                    ->orderByDesc('created_at')
                    ->limit(100)
                    ->get(['id','booking_number','total_amount','created_at'])
                    ->map(fn($b) => [
                        'id' => $b->id,
                        'type' => 'hall_booking',
                        'ref' => $b->booking_number,
                        'amount' => (float) $b->total_amount,
                        'created_at' => $b->created_at,
                    ])->toArray();
                $recentTransactions = collect($recentTransactions)->merge($hallTxns)->sortByDesc('created_at')->values()->toArray();
            }
            if (class_exists(\App\Models\Expense::class)) {
                $kpis['total_expenses'] = (float) \App\Models\Expense::sum('amount');
                $recentExpenses = \App\Models\Expense::orderByDesc('created_at')
                    ->limit(100)
                    ->get(['id','reference','amount','created_at'])
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'type' => 'expense',
                        'ref' => $e->reference,
                        'amount' => (float) $e->amount,
                        'created_at' => $e->created_at,
                    ])->toArray();
                $recentTransactions = collect($recentTransactions)->merge($recentExpenses)->sortByDesc('created_at')->values()->toArray();
            }
            $kpis['net_profit'] = round($kpis['total_revenue'] - $kpis['total_expenses'], 2);
            $kpis['net_margin'] = $kpis['total_revenue'] > 0 ? round(($kpis['net_profit'] / $kpis['total_revenue']) * 100, 1) : 0.0;
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'kpis' => $kpis,
                'recentTransactions' => $recentTransactions,
            ]);
        }

        // default CSV
        $eol = "\r\n";
        $csv = fopen('php://temp', 'w+');
        // KPIs
        fputcsv($csv, ['KPI', 'Value']);
        foreach ($kpis as $k => $v) {
            fputcsv($csv, [$k, $v]);
        }
        fwrite($csv, $eol);
        // Transactions
        fputcsv($csv, ['Type','Reference','Amount','Created At']);
        foreach ($recentTransactions as $t) {
            fputcsv($csv, [
                $t['type'] ?? '',
                $t['ref'] ?? '',
                $t['amount'] ?? 0,
                $t['created_at'] ?? '',
            ]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'financial-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.financial.export');

    // Reports - Inventory
    Route::get('/reports/inventory', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $threshold = (int) request('threshold', 5);

        $stats = [
            'total_items' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'stock_value' => 0.0,
        ];
        $lowStockItems = [];
        $recentMovements = [];

        try {
            // If you have a Product or InventoryItem model
            if (class_exists(\App\Models\Product::class)) {
                $stats['total_items'] = \App\Models\Product::count();
                if (\Schema::hasColumn('products', 'quantity')) {
                    $stats['out_of_stock'] = \App\Models\Product::where('quantity', '<=', 0)->count();
                    // Low stock: strictly less than threshold, exclude out-of-stock
                    $stats['low_stock'] = \App\Models\Product::where('quantity', '>', 0)
                        ->where('quantity', '<', $threshold)
                        ->count();
                }
                // Stock value if price and quantity exist
                if (\Schema::hasColumn('products', 'quantity') && \Schema::hasColumn('products', 'price')) {
                    $stats['stock_value'] = (float) \App\Models\Product::query()
                        ->selectRaw('SUM(quantity * price) as v')
                        ->value('v');
                }
                // Low stock list
                if (\Schema::hasColumn('products', 'quantity')) {
                    $lowStockItems = \App\Models\Product::where('quantity', '>', 0)
                        ->where('quantity', '<', $threshold)
                        ->orderBy('quantity')
                        ->limit(10)
                        ->get(['id','name','sku','quantity','price'])
                        ->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'sku' => $p->sku ?? null,
                            'quantity' => $p->quantity,
                            'price' => (float) ($p->price ?? 0),
                        ]);
                }
            }

            // Stock movements if a model exists
            if (class_exists(\App\Models\StockMovement::class)) {
                $recentMovements = \App\Models\StockMovement::orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id','product_id','type','quantity','created_at'])
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'product_id' => $m->product_id,
                        'type' => $m->type,
                        'quantity' => (int) $m->quantity,
                        'created_at' => $m->created_at,
                    ]);
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return Inertia::render('Admin/Reports/Inventory', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'lowStockItems' => $lowStockItems,
            'recentMovements' => $recentMovements,
            'filters' => [ 'threshold' => $threshold ],
        ]);
    })->name('reports.inventory');

    // Reports - Inventory Export
    Route::get('/reports/inventory/export/{format}', function ($format) {
        $threshold = (int) request('threshold', 5);
        $stats = [
            'total_items' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'stock_value' => 0.0,
        ];
        $lowStockItems = [];
        $recentMovements = [];

        try {
            if (class_exists(\App\Models\Product::class)) {
                $stats['total_items'] = \App\Models\Product::count();
                if (\Schema::hasColumn('products', 'quantity')) {
                    $stats['out_of_stock'] = \App\Models\Product::where('quantity', '<=', 0)->count();
                    $stats['low_stock'] = \App\Models\Product::where('quantity', '>', 0)
                        ->where('quantity', '<', $threshold)
                        ->count();
                }
                if (\Schema::hasColumn('products', 'quantity') && \Schema::hasColumn('products', 'price')) {
                    $stats['stock_value'] = (float) \App\Models\Product::query()->selectRaw('SUM(quantity * price) as v')->value('v');
                }
                if (\Schema::hasColumn('products', 'quantity')) {
                    $lowStockItems = \App\Models\Product::where('quantity', '>', 0)
                        ->where('quantity', '<', $threshold)
                        ->orderBy('quantity')
                        ->limit(200)
                        ->get(['id','name','sku','quantity','price'])
                        ->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'sku' => $p->sku ?? null,
                            'quantity' => $p->quantity,
                            'price' => (float) ($p->price ?? 0),
                        ])->toArray();
                }
            }
            if (class_exists(\App\Models\StockMovement::class)) {
                $recentMovements = \App\Models\StockMovement::orderByDesc('created_at')
                    ->limit(200)
                    ->get(['id','product_id','type','quantity','created_at'])
                    ->map(fn($m) => [
                        'id' => $m->id,
                        'product_id' => $m->product_id,
                        'type' => $m->type,
                        'quantity' => (int) $m->quantity,
                        'created_at' => $m->created_at,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'lowStockItems' => $lowStockItems,
                'recentMovements' => $recentMovements,
            ]);
        }

        // CSV
        $eol = "\r\n";
        $csv = fopen('php://temp', 'w+');
        fputcsv($csv, ['Metric','Value']);
        foreach ($stats as $k => $v) { fputcsv($csv, [$k, $v]); }
        fwrite($csv, $eol);
        fputcsv($csv, ['Low Stock Items']);
        fputcsv($csv, ['ID','Name','SKU','Quantity','Price']);
        foreach ($lowStockItems as $p) {
            fputcsv($csv, [$p['id'], $p['name'], $p['sku'], $p['quantity'], $p['price']]);
        }
        fwrite($csv, $eol);
        fputcsv($csv, ['Recent Movements']);
        fputcsv($csv, ['ID','Product ID','Type','Quantity','Created At']);
        foreach ($recentMovements as $m) {
            fputcsv($csv, [$m['id'], $m['product_id'], $m['type'], $m['quantity'], $m['created_at']]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'inventory-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.inventory.export');

    // Reports - Staff
    Route::get('/reports/staff', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $start = request('start_date');
        $end = request('end_date');
        if (!$start) { $start = now()->startOfMonth()->toDateString(); }
        if (!$end) { $end = now()->toDateString(); }

        $stats = [
            'total_staff' => 0,
            'new_today' => 0,
            'new_month' => 0,
            'active_staff' => 0,
        ];
        $byRole = [];
        $recentHires = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\User::class)) {
                $stats['total_staff'] = \App\Models\User::count();
                $stats['new_today'] = \App\Models\User::whereDate('created_at', $today)->count();
                $stats['new_month'] = \App\Models\User::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->count();
                if (\Schema::hasColumn('users', 'is_active')) {
                    $stats['active_staff'] = \App\Models\User::where('is_active', true)->count();
                }

                $recentHires = \App\Models\User::with('roles')
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get(['id','first_name','last_name','email','created_at'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => trim($u->first_name . ' ' . $u->last_name),
                        'email' => $u->email,
                        'roles' => $u->roles?->pluck('name')->values() ?? [],
                        'created_at' => $u->created_at,
                    ]);
            }

            if (class_exists(\App\Models\Role::class)) {
                $byRole = \App\Models\Role::select('id','name')
                    ->withCount('users')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'total' => (int) $r->users_count,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return Inertia::render('Admin/Reports/Staff', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'byRole' => $byRole,
            'recentHires' => $recentHires,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    })->name('reports.staff');

    // Reports - Staff Export
    Route::get('/reports/staff/export/{format}', function ($format) {
        $start = request('start_date');
        $end = request('end_date');
        if (!$start) { $start = now()->startOfMonth()->toDateString(); }
        if (!$end) { $end = now()->toDateString(); }

        $stats = [
            'total_staff' => 0,
            'new_today' => 0,
            'new_month' => 0,
            'active_staff' => 0,
        ];
        $byRole = [];
        $recentHires = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\User::class)) {
                $stats['total_staff'] = \App\Models\User::count();
                $stats['new_today'] = \App\Models\User::whereDate('created_at', $today)->count();
                $stats['new_month'] = \App\Models\User::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->count();
                if (\Schema::hasColumn('users', 'is_active')) {
                    $stats['active_staff'] = \App\Models\User::where('is_active', true)->count();
                }

                $recentHires = \App\Models\User::with('roles')
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('created_at')
                    ->limit(500)
                    ->get(['id','first_name','last_name','email','created_at'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => trim($u->first_name . ' ' . $u->last_name),
                        'email' => $u->email,
                        'roles' => $u->roles?->pluck('name')->values() ?? [],
                        'created_at' => $u->created_at,
                    ])->toArray();
            }

            if (class_exists(\App\Models\Role::class)) {
                $byRole = \App\Models\Role::select('id','name')
                    ->withCount('users')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'total' => (int) $r->users_count,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'byRole' => $byRole,
                'recentHires' => $recentHires,
                'filters' => [
                    'start_date' => $start,
                    'end_date' => $end,
                ],
            ]);
        }

        $eol = "\r\n";
        $csv = fopen('php://temp', 'w+');
        // Stats
        fputcsv($csv, ['Metric','Value']);
        foreach ($stats as $k => $v) { fputcsv($csv, [$k, $v]); }
        fwrite($csv, $eol);
        // By Role
        fputcsv($csv, ['Staff by Role']);
        fputcsv($csv, ['Role','Total Staff']);
        foreach ($byRole as $r) {
            fputcsv($csv, [$r['name'] ?? '', $r['total'] ?? 0]);
        }
        fwrite($csv, $eol);
        // Recent Hires
        fputcsv($csv, ['Recent Hires']);
        fputcsv($csv, ['ID','Name','Email','Roles','Created At']);
        foreach ($recentHires as $u) {
            fputcsv($csv, [
                $u['id'] ?? '',
                $u['name'] ?? '',
                $u['email'] ?? '',
                isset($u['roles']) ? implode('|', $u['roles']) : '',
                $u['created_at'] ?? '',
            ]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'staff-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.staff.export');

    // Reports - Maintenance
    Route::get('/reports/maintenance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $start = request('start_date');
        $end = request('end_date');
        if (!$start) { $start = now()->startOfMonth()->toDateString(); }
        if (!$end) { $end = now()->toDateString(); }

        $stats = [
            'total_requests' => 0,
            'open_requests' => 0,
            'in_progress' => 0,
            'completed_today' => 0,
        ];
        $recentOpen = [];
        $recentCompleted = [];
        $allRequests = [];

        try {
            if (class_exists(\App\Models\MaintenanceRequest::class)) {
                // Base query for date range KPIs
                $rangeQuery = \App\Models\MaintenanceRequest::query()
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);

                $stats['total_requests'] = (clone $rangeQuery)->count();

                if (\Schema::hasColumn('maintenance_requests', 'status')) {
                    $stats['open_requests'] = (clone $rangeQuery)
                        ->whereIn('status', ['open','pending'])
                        ->count();

                    $stats['in_progress'] = (clone $rangeQuery)
                        ->where('status', 'in_progress')
                        ->count();

                    // Completed within range (based on updated_at)
                    $stats['completed_today'] = \App\Models\MaintenanceRequest::query()
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$start.' 00:00:00', $end.' 23:59:59'])
                        ->count();
                }

                // Recent open/in-progress within range
                $recentOpen = \App\Models\MaintenanceRequest::query()
                    ->when(\Schema::hasColumn('maintenance_requests', 'status'), function ($q) {
                        $q->whereIn('status', ['open','pending','in_progress']);
                    })
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('created_at')
                    ->limit(25)
                    ->get(['id','title','location','status','created_at'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'location' => $r->location,
                        'status' => $r->status,
                        'created_at' => $r->created_at,
                    ]);

                // Recent completed within range
                $recentCompleted = \App\Models\MaintenanceRequest::query()
                    ->when(\Schema::hasColumn('maintenance_requests', 'status'), function ($q) {
                        $q->where('status', 'completed');
                    })
                    ->whereBetween('updated_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('updated_at')
                    ->limit(25)
                    ->get(['id','title','location','status','updated_at'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'location' => $r->location,
                        'status' => $r->status,
                        'updated_at' => $r->updated_at,
                    ]);

                // All requests in range (for full listing)
                $allRequests = \App\Models\MaintenanceRequest::query()
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('created_at')
                    ->limit(200)
                    ->get(['id','title','location','status','created_at','updated_at'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'location' => $r->location,
                        'status' => $r->status,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ]);
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return Inertia::render('Admin/Reports/Maintenance', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'recentOpen' => $recentOpen,
            'recentCompleted' => $recentCompleted,
            'allRequests' => $allRequests,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    })->name('reports.maintenance');

    // Reports - Maintenance Export
    Route::get('/reports/maintenance/export/{format}', function ($format) {
        $start = request('start_date');
        $end = request('end_date');
        if (!$start) { $start = now()->startOfMonth()->toDateString(); }
        if (!$end) { $end = now()->toDateString(); }

        $stats = [
            'total_requests' => 0,
            'open_requests' => 0,
            'in_progress' => 0,
            'completed_today' => 0,
        ];
        $recentOpen = [];
        $recentCompleted = [];

        try {
            if (class_exists(\App\Models\MaintenanceRequest::class)) {
                $rangeQuery = \App\Models\MaintenanceRequest::query()
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);

                $stats['total_requests'] = (clone $rangeQuery)->count();

                if (\Schema::hasColumn('maintenance_requests', 'status')) {
                    $stats['open_requests'] = (clone $rangeQuery)
                        ->whereIn('status', ['open','pending'])
                        ->count();

                    $stats['in_progress'] = (clone $rangeQuery)
                        ->where('status', 'in_progress')
                        ->count();

                    $stats['completed_today'] = \App\Models\MaintenanceRequest::query()
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$start.' 00:00:00', $end.' 23:59:59'])
                        ->count();
                }

                $recentOpen = \App\Models\MaintenanceRequest::query()
                    ->when(\Schema::hasColumn('maintenance_requests', 'status'), function ($q) {
                        $q->whereIn('status', ['open','pending','in_progress']);
                    })
                    ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('created_at')
                    ->limit(500)
                    ->get(['id','title','location','status','created_at'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'location' => $r->location,
                        'status' => $r->status,
                        'created_at' => $r->created_at,
                    ])->toArray();

                $recentCompleted = \App\Models\MaintenanceRequest::query()
                    ->when(\Schema::hasColumn('maintenance_requests', 'status'), function ($q) {
                        $q->where('status', 'completed');
                    })
                    ->whereBetween('updated_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->orderByDesc('updated_at')
                    ->limit(500)
                    ->get(['id','title','location','status','updated_at'])
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'location' => $r->location,
                        'status' => $r->status,
                        'updated_at' => $r->updated_at,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'recentOpen' => $recentOpen,
                'recentCompleted' => $recentCompleted,
                'filters' => [
                    'start_date' => $start,
                    'end_date' => $end,
                ],
            ]);
        }

        $eol = "\r\n";
        $csv = fopen('php://temp', 'w+');
        // Stats
        fputcsv($csv, ['Metric','Value']);
        foreach ($stats as $k => $v) { fputcsv($csv, [$k, $v]); }
        fwrite($csv, $eol);
        // Recent Open
        fputcsv($csv, ['Open & In-progress Requests']);
        fputcsv($csv, ['ID','Title','Location','Status','Created']);
        foreach ($recentOpen as $r) {
            fputcsv($csv, [
                $r['id'] ?? '',
                $r['title'] ?? '',
                $r['location'] ?? '',
                $r['status'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }
        fwrite($csv, $eol);
        // Recent Completed
        fputcsv($csv, ['Recently Completed']);
        fputcsv($csv, ['ID','Title','Location','Status','Completed']);
        foreach ($recentCompleted as $r) {
            fputcsv($csv, [
                $r['id'] ?? '',
                $r['title'] ?? '',
                $r['location'] ?? '',
                $r['status'] ?? '',
                $r['updated_at'] ?? '',
            ]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'maintenance-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.maintenance.export');

    // Reports - Guests
    Route::get('/reports/guests', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = [
            'total_guests' => 0,
            'new_today' => 0,
            'new_month' => 0,
        ];
        $byGroup = [];
        $recentGuests = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\Customer::class)) {
                $stats['total_guests'] = \App\Models\Customer::count();
                $stats['new_today'] = \App\Models\Customer::whereDate('created_at', $today)->count();
                $stats['new_month'] = \App\Models\Customer::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->count();

                $recentGuests = \App\Models\Customer::orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id','first_name','last_name','customer_code','email','phone','created_at','customer_group_id'])
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'first_name' => $c->first_name,
                        'last_name' => $c->last_name,
                        'customer_code' => $c->customer_code,
                        'email' => $c->email,
                        'phone' => $c->phone,
                        'created_at' => $c->created_at,
                        'customer_group_id' => $c->customer_group_id,
                    ]);
            }

            if (class_exists(\App\Models\CustomerGroup::class) && class_exists(\App\Models\Customer::class)) {
                $byGroup = \App\Models\CustomerGroup::select('id','name')
                    ->withCount('customers')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($g) => [
                        'id' => $g->id,
                        'name' => $g->name,
                        'total' => (int) $g->customers_count,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return Inertia::render('Admin/Reports/Guests', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'byGroup' => $byGroup,
            'recentGuests' => $recentGuests,
        ]);
    })->name('reports.guests');

    // Reports - Guests Export
    Route::get('/reports/guests/export/{format}', function ($format) {
        $stats = [
            'total_guests' => 0,
            'new_today' => 0,
            'new_month' => 0,
        ];
        $byGroup = [];
        $recentGuests = [];

        try {
            $today = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\Customer::class)) {
                $stats['total_guests'] = \App\Models\Customer::count();
                $stats['new_today'] = \App\Models\Customer::whereDate('created_at', $today)->count();
                $stats['new_month'] = \App\Models\Customer::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->count();

                $recentGuests = \App\Models\Customer::orderByDesc('created_at')
                    ->limit(200)
                    ->get(['id','first_name','last_name','customer_code','email','phone','created_at','customer_group_id'])
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'first_name' => $c->first_name,
                        'last_name' => $c->last_name,
                        'customer_code' => $c->customer_code,
                        'email' => $c->email,
                        'phone' => $c->phone,
                        'created_at' => $c->created_at,
                        'customer_group_id' => $c->customer_group_id,
                    ])->toArray();
            }

            if (class_exists(\App\Models\CustomerGroup::class) && class_exists(\App\Models\Customer::class)) {
                $byGroup = \App\Models\CustomerGroup::select('id','name')
                    ->withCount('customers')
                    ->orderBy('name')
                    ->get()
                    ->map(fn($g) => [
                        'id' => $g->id,
                        'name' => $g->name,
                        'total' => (int) $g->customers_count,
                    ])->toArray();
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        if ($format === 'json') {
            return response()->json([
                'stats' => $stats,
                'byGroup' => $byGroup,
                'recentGuests' => $recentGuests,
            ]);
        }

        // default CSV with CRLF for Windows-friendly line endings
        $eol = "\r\n";
        $csv = fopen('php://temp', 'w+');
        // Write CRLF separators manually when needed

        // Stats
        fputcsv($csv, ['Metric', 'Value']);
        foreach ($stats as $k => $v) {
            fputcsv($csv, [$k, $v]);
        }
        fwrite($csv, $eol);

        // By Group
        fputcsv($csv, ['Group', 'Total']);
        foreach ($byGroup as $row) {
            fputcsv($csv, [$row['name'], $row['total']]);
        }
        fwrite($csv, $eol);

        // Recent Guests
        fputcsv($csv, ['First Name','Last Name','Code','Email','Phone','Created At','Group ID']);
        foreach ($recentGuests as $g) {
            fputcsv($csv, [
                $g['first_name'],
                $g['last_name'],
                $g['customer_code'],
                $g['email'],
                $g['phone'],
                $g['created_at'],
                $g['customer_group_id'],
            ]);
        }

        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        $filename = 'guests-report-'.now()->format('Ymd_His').'.csv';
        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    })->name('reports.guests.export');

    // Expenses
    Route::get('/expenses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();

        $query = \App\Models\Expense::with(['category', 'submittedBy'])->latest();

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('category')) {
            $query->where('expense_category_id', request('category'));
        }
        if (request('start_date')) {
            $query->whereDate('expense_date', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $query->whereDate('expense_date', '<=', request('end_date'));
        }

        $expenses = $query->paginate(20)->through(fn($e) => [
            'id'             => $e->id,
            'description'    => $e->description,
            'vendor'         => $e->vendor_name,
            'category'       => $e->category?->name ?? 'Uncategorized',
            'category_id'    => $e->expense_category_id,
            'category_color' => $e->category?->color,
            'amount'         => (float) $e->amount,
            'date'           => $e->expense_date?->toDateString(),
            'status'         => $e->status,
            'payment_method' => $e->payment_method,
            'receipt_number' => $e->receipt_number,
            'notes'          => $e->notes,
        ]);

        $expenseStats = [
            'thisMonth'  => (float) \App\Models\Expense::whereMonth('expense_date', now()->month)
                                ->whereYear('expense_date', now()->year)->sum('amount'),
            'pending'    => \App\Models\Expense::where('status', 'pending')->count(),
            'total'      => \App\Models\Expense::count(),
            'categories' => \App\Models\ExpenseCategory::where('is_active', true)->count(),
        ];

        return Inertia::render('Admin/Expenses/Index', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'expenses'       => $expenses,
            'categories'     => $categories,
            'expenseStats'   => $expenseStats,
            'filters'        => request()->only(['status', 'category', 'start_date', 'end_date']),
        ]);
    })->name('expenses.index');

    // Transactions
    Route::get('/transactions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // ── Transaction Statistics ─────────────────────────────────────────────
        $today = now()->toDateString();

        $todayRevenue   = (float) \App\Models\Payment::whereDate('processed_at', $today)->where('status', 'completed')->sum('amount')
                        + \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
        $totalRevenue   = (float) \App\Models\Payment::where('status', 'completed')->sum('amount')
                        + \App\Models\Sale::sum('total_amount');
        $todayRoomRev   = (float) \App\Models\Payment::whereDate('processed_at', $today)->where('status', 'completed')->whereHas('reservation')->sum('amount');
        $todayPosRev    = (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
        $allRoomRev     = (float) \App\Models\Payment::where('status', 'completed')->whereHas('reservation')->sum('amount');
        $allPosRev      = (float) \App\Models\Sale::sum('total_amount');

        $transactionStats = [
            'todayRevenue'           => $todayRevenue,
            'totalRevenue'           => $totalRevenue,
            'pending'                => \App\Models\Payment::where('status', 'pending')->count() + \App\Models\Expense::where('status', 'pending')->count(),
            'failed'                 => \App\Models\Payment::where('status', 'failed')->count(),
            'todayByRevenueCenter'   => [
                'room'          => ['label' => 'Room Revenue',   'amount' => $todayRoomRev, 'count' => \App\Models\Payment::whereDate('processed_at', $today)->whereHas('reservation')->count()],
                'food_beverage' => ['label' => 'Food & Beverage','amount' => $todayPosRev,  'count' => \App\Models\Sale::whereDate('created_at', $today)->count()],
                'other'         => ['label' => 'Other',          'amount' => max(0, $todayRevenue - $todayRoomRev - $todayPosRev), 'count' => 0],
            ],
            'allTimeByRevenueCenter' => [
                'room'          => ['label' => 'Room Revenue',   'amount' => $allRoomRev,  'count' => \App\Models\Payment::where('status', 'completed')->whereHas('reservation')->count()],
                'food_beverage' => ['label' => 'Food & Beverage','amount' => $allPosRev,   'count' => \App\Models\Sale::count()],
                'other'         => ['label' => 'Other',          'amount' => max(0, $totalRevenue - $allRoomRev - $allPosRev), 'count' => 0],
            ],
        ];

        // ── Recent Transactions ──────────────────────────────────────────────────
        $recentTransactions = collect();

        // Add recent payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'source_id'     => $p->id,
                'transaction_id' => $p->payment_number ?? 'PAY-' . $p->id,
                'guest_name'    => $p->reservation?->guest
                    ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Guest',
                'reference'     => $p->reservation?->id ? 'Reservation #' . $p->reservation->id : 'Direct Payment',
                'type'          => 'payment',
                'amount'        => (float)$p->amount,
                'status'        => $p->status ?? 'completed',
                'payment_method' => $p->payment_method,
                'date'          => $p->processed_at?->format('Y-m-d H:i:s') ?? $p->created_at->format('Y-m-d H:i:s'),
                'created_at'    => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add recent sales
        if (class_exists(\App\Models\Sale::class)) {
            $sales = \App\Models\Sale::with(['guest', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($s) => [
                    'id'            => $s->id,
                    'source_id'     => $s->id,
                    'transaction_id' => $s->sale_number ?? 'SALE-' . $s->id,
                    'guest_name'    => $s->guest
                        ? trim($s->guest->first_name . ' ' . $s->guest->last_name)
                        : 'Customer',
                    'reference'     => 'Sale #' . $s->id,
                    'type'          => 'sale',
                    'amount'        => (float)$s->total_amount,
                    'status'        => $s->payment_status ?? 'completed',
                    'payment_method' => $s->payment_method ?? 'cash',
                    'date'          => $s->created_at->format('Y-m-d H:i:s'),
                    'created_at'    => $s->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($sales);
        }

        // Add recent POS transactions
        if (class_exists(\App\Models\PosTransaction::class)) {
            $posTransactions = \App\Models\PosTransaction::with(['sale', 'cashDrawerSession.user'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($pt) => [
                    'id'            => $pt->id,
                    'source_id'     => $pt->id,
                    'transaction_id' => 'POS-' . $pt->id,
                    'guest_name'    => 'POS Customer',
                    'reference'     => $pt->sale?->sale_number ? 'POS Sale #' . $pt->sale->sale_number : 'POS Transaction',
                    'type'          => 'pos_transaction',
                    'amount'        => (float)$pt->amount,
                    'status'        => 'completed',
                    'payment_method' => $pt->payment_method ?? 'cash',
                    'date'          => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($posTransactions);
        }

        // Add recent folio charges (room charges)
        if (class_exists(\App\Models\FolioCharge::class)) {
            $folioCharges = \App\Models\FolioCharge::with(['folio.reservation.guest', 'folio.reservation.room'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($fc) => [
                    'id'            => $fc->id,
                    'source_id'     => $fc->id,
                    'transaction_id' => 'FOLIO-' . $fc->id,
                    'guest_name'    => $fc->folio?->reservation?->guest
                        ? trim($fc->folio->reservation->guest->first_name . ' ' . $fc->folio->reservation->guest->last_name)
                        : 'Guest',
                    'reference'     => $fc->folio?->reservation?->id ? 'Room #' . $fc->folio->reservation->room?->room_number . ' Folio' : 'Room Charge',
                    'type'          => 'folio_charge',
                    'amount'        => (float)$fc->net_amount,
                    'status'        => 'active',
                    'payment_method' => 'room_charge',
                    'date'          => $fc->charge_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $fc->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($folioCharges);
        }

        // Add recent expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($e) => [
                    'id'            => $e->id,
                    'source_id'     => $e->id,
                    'transaction_id' => $e->expense_number ?? 'EXP-' . $e->id,
                    'guest_name'    => $e->vendor_name ?? 'Vendor',
                    'reference'     => 'Expense #' . $e->id,
                    'type'          => 'expense',
                    'amount'        => (float)$e->amount,
                    'status'        => $e->status ?? 'pending',
                    'payment_method' => $e->payment_method ?? 'cash',
                    'date'          => $e->expense_date->format('Y-m-d H:i:s'),
                    'created_at'    => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->take(50)->values();

        return Inertia::render('Admin/Transactions/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'transactionStats' => $transactionStats,
            'transactions' => $recentTransactions->toArray(),
            'recentTransactions' => $recentTransactions->toArray(),
            'settings' => ['currency' => 'USD'],
            'filters' => request()->only(['status', 'type', 'start_date', 'end_date']),
        ]);
    })->name('transactions.index');

    Route::get('/transactions/export', function (\Illuminate\Http\Request $request) {
        $format = $request->get('format', 'csv');

        // Get transactions data
        $recentTransactions = collect();

        // Add payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('processed_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'ID' => $p->id,
                'Type' => 'Payment',
                'Description' => $p->reservation?->guest
                    ? 'Payment from ' . trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Payment Received',
                'Amount' => (float)$p->amount,
                'Status' => $p->status ?? 'completed',
                'Payment Method' => $p->payment_method,
                'Date' => $p->processed_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'Created At' => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->get()
                ->map(fn($e) => [
                    'ID' => $e->id,
                    'Type' => 'Expense',
                    'Description' => $e->description ?? $e->vendor_name ?? 'Expense',
                    'Amount' => (float)$e->amount,
                    'Status' => $e->status ?? 'pending',
                    'Payment Method' => $e->payment_method ?? 'cash',
                    'Date' => $e->expense_date->format('Y-m-d H:i:s'),
                    'Created At' => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->values();

        if ($format === 'csv') {
            $filename = 'transactions-' . now()->format('Ymd-His') . '.csv';
            $data = $recentTransactions;
            return response()->streamDownload(function () use ($data) {
                $handle = fopen('php://output', 'wb');
                if ($data->isNotEmpty()) {
                    fputcsv($handle, array_keys($data->first()));
                }
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        // For other formats, redirect back for now
        return back()->with('info', 'Export format not implemented yet');
    })->name('transactions.export');

    Route::post('/transactions/process/{payment}', function (\App\Models\Payment $payment) {
        if ($payment->status === 'pending') {
            $payment->update(['status' => 'completed']);
        }
        return back()->with('success', 'Transaction processed successfully.');
    })->name('transactions.process');

    Route::post('/transactions/refund/{payment}', function (\App\Models\Payment $payment) {
        $payment->update(['status' => 'refunded']);
        return back()->with('success', 'Refund initiated successfully.');
    })->name('transactions.refund');

    // Static expense routes MUST come before {expense} wildcard
    Route::get('/expenses/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Admin/Expenses/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories,
        ]);
    })->name('expenses.create');

    Route::get('/expenses/categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::withCount('expenses')->orderBy('name')->get();
        return Inertia::render('Admin/Expenses/Categories', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories,
        ]);
    })->name('expenses.categories');

    Route::post('/expenses/categories', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);
        $validated['is_active'] = true;
        \App\Models\ExpenseCategory::create($validated);
        return redirect()->route('admin.expenses.categories')->with('success', 'Category created.');
    })->name('expenses.categories.store');

    Route::post('/expenses', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vendor_name'         => 'nullable|string|max:255',
            'description'         => 'required|string',
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'nullable|string|max:50',
            'receipt_number'      => 'nullable|string|max:100',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes'               => 'nullable|string',
        ]);
        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file_path'] = $request->file('receipt_file')
                ->store('expenses/receipts', 'public');
        }
        unset($validated['receipt_file']);
        $validated['submitted_by']   = request()->user()->id;
        $validated['status']         = 'pending';
        $validated['expense_number'] = 'EXP-' . strtoupper(uniqid());
        $validated['currency']       = \App\Models\Setting::get('currency', 'USD');
        \App\Models\Expense::create($validated);
        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully.');
    })->name('expenses.store');

    // Wildcard {expense} routes come last
    Route::get('/expenses/{expense}', function (\App\Models\Expense $expense) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $exp  = $expense->load(['category', 'submittedBy', 'approvedBy']);
        $receiptUrl = $exp->receipt_file_path
            ? '/storage/' . $exp->receipt_file_path
            : null;
        return Inertia::render('Admin/Expenses/Show', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'expense'        => $exp,
            'receipt_url'    => $receiptUrl,
            'submitter_name' => $exp->submittedBy ? trim($exp->submittedBy->first_name . ' ' . $exp->submittedBy->last_name) : (\App\Models\User::find($exp->submitted_by) ? trim(\App\Models\User::find($exp->submitted_by)->first_name . ' ' . \App\Models\User::find($exp->submitted_by)->last_name) : null),
            'approver_name'  => $exp->approvedBy  ? trim($exp->approvedBy->first_name  . ' ' . $exp->approvedBy->last_name)  : (\App\Models\User::find($exp->approved_by)  ? trim(\App\Models\User::find($exp->approved_by)->first_name  . ' ' . \App\Models\User::find($exp->approved_by)->last_name)  : null),
        ]);
    })->name('expenses.show');

    Route::get('/expenses/{expense}/edit', function (\App\Models\Expense $expense) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Admin/Expenses/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'expense' => $expense->load('category'),
            'categories' => $categories,
        ]);
    })->name('expenses.edit');

    Route::put('/expenses/{expense}', function (\Illuminate\Http\Request $request, \App\Models\Expense $expense) {
        $validated = $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vendor_name'         => 'nullable|string|max:255',
            'description'         => 'required|string',
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'nullable|string|max:50',
            'receipt_number'      => 'nullable|string|max:100',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes'               => 'nullable|string',
        ]);
        if ($request->hasFile('receipt_file')) {
            if ($expense->receipt_file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->receipt_file_path);
            }
            $validated['receipt_file_path'] = $request->file('receipt_file')
                ->store('expenses/receipts', 'public');
        }
        unset($validated['receipt_file']);
        $expense->update($validated);
        return redirect()->route('admin.expenses.show', $expense)->with('success', 'Expense updated.');
    })->name('expenses.update');

    Route::delete('/expenses/{expense}', function (\App\Models\Expense $expense) {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    })->name('expenses.destroy');

    Route::post('/expenses/{expense}/approve', function (\App\Models\Expense $expense) {
        $expense->update([
            'status'         => 'approved',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'approval_notes' => request('approval_notes'),
        ]);
        return redirect()->back()->with('success', 'Expense approved successfully.');
    })->name('expenses.approve');

    Route::post('/expenses/{expense}/reject', function (\App\Models\Expense $expense) {
        $expense->update([
            'status'         => 'rejected',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'approval_notes' => request('approval_notes'),
        ]);
        return redirect()->back()->with('success', 'Expense rejected.');
    })->name('expenses.reject');

    // Locations
    Route::get('/locations', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $locations = \App\Models\Location::orderBy('name')->get();
        return Inertia::render('Admin/Locations/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'locations'  => $locations,
        ]);
    })->name('locations.index');

    Route::post('/locations', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:locations,name',
            'type'        => 'required|in:warehouse,restaurant,frontdesk,bar,kitchen,other',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        \App\Models\Location::create($validated);
        return redirect()->route('admin.locations.index')->with('success', 'Location created.');
    })->name('locations.store');

    Route::put('/locations/{location}', function (\Illuminate\Http\Request $request, \App\Models\Location $location) {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:locations,name,' . $location->id,
            'type'        => 'required|in:warehouse,restaurant,frontdesk,bar,kitchen,other',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        $location->update($validated);
        return redirect()->route('admin.locations.index')->with('success', 'Location updated.');
    })->name('locations.update');

    Route::delete('/locations/{location}', function (\App\Models\Location $location) {
        $location->delete();
        return redirect()->route('admin.locations.index')->with('success', 'Location deleted.');
    })->name('locations.destroy');

    // Departments
    Route::get('/departments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $departments = \App\Models\Department::with('positions')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Departments/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'departments' => $departments
        ]);
    })->name('departments.index');

    // Positions
    Route::get('/positions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $positions = \App\Models\Position::with('department')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Positions/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'positions' => $positions
        ]);
    })->name('positions.index');

    // Attendance
    Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'markAttendance'])->name('attendance.mark');
    Route::post('/attendance/check-out', [\App\Http\Controllers\Admin\AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/bulk-mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');



    // Performance
    Route::get('/performance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $performance = \App\Models\User::with(['roles', 'department', 'position'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'department' => is_object($user->department) ? $user->department->name : ($user->department ?? 'N/A'),
                    'position' => is_object($user->position) ? $user->position->name : ($user->position ?? 'N/A'),
                    'performance_score' => rand(70, 95),
                    'attendance_rate' => rand(85, 98),
                    'tasks_completed' => rand(20, 45),
                    'efficiency' => rand(80, 95)
                ];
            });

        return Inertia::render('Admin/Performance/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'performance' => $performance
        ]);
    })->name('performance.index');

    // Products
    Route::get('/products', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Products/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('products.index');

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

    // Service Charges - MUST be before wildcard routes to avoid 404
    Route::get('/reservations/service-charges', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Reservations/ServiceCharges', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reservations.service-charges');

    // Wildcard routes - MUST be after static routes
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/send-confirmation', [ReservationController::class, 'sendConfirmation'])->name('reservations.send-confirmation');

    // Guests
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::post('/guests/quick', [GuestController::class, 'quickStore'])->name('guests.quick-store');

    // Check-ins
    Route::get('/checkin', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $today = now()->toDateString();

        // Shared mapper — produces the structure Admin/Checkin/Index.vue expects
        $mapReservation = function ($r) {
            $reservedRoom = $r->room;
            $isRoomAvailable = $reservedRoom
                && in_array($reservedRoom->status, ['available', 'reserved'])
                && $reservedRoom->housekeeping_status === 'clean';

            $checkIn  = \Carbon\Carbon::parse($r->check_in_date);
            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
            $nights   = max(1, $checkIn->diffInDays($checkOut));

            return [
                'id'               => $r->id,
                'reservation_number' => $r->reservation_number,
                'guest_name'       => $r->guest
                    ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? ''))
                    : 'Unknown Guest',
                'guest_email'      => $r->guest?->email ?? 'N/A',
                'guest_phone'      => $r->guest?->phone ?? 'N/A',
                // nested room object — component uses reservation.room?.number
                'room'             => $reservedRoom ? [
                    'id'     => $reservedRoom->id,
                    'number' => $reservedRoom->room_number,
                ] : null,
                // nested room_type object — component uses reservation.room_type?.name
                'room_type'        => $r->roomType ? ['name' => $r->roomType->name] : null,
                // flat alias used by some conditions in the modal
                'roomNumber'       => $reservedRoom?->room_number ?? 'TBA',
                'check_in_date'    => $checkIn->format('Y-m-d'),
                'check_out_date'   => $checkOut->format('Y-m-d'),
                'nights'           => $nights,
                'total_amount'     => $r->total_amount,
                'balance_amount'   => $r->balance_amount,
                'guests_count'     => ($r->number_of_adults ?? $r->adults ?? 1)
                                    + ($r->number_of_children ?? $r->children ?? 0),
                'status'           => $r->status,
                'reservedRoomAvailable'          => $isRoomAvailable,
                'reservedRoomStatus'             => $reservedRoom?->status,
                'reservedRoomHousekeepingStatus' => $reservedRoom?->housekeeping_status,
            ];
        };

        // All pending/confirmed reservations due for check-in (today or overdue)
        $pendingReservations = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', $today)
            ->orderBy('check_in_date', 'asc')
            ->get()
            ->map($mapReservation)
            ->values()
            ->toArray();

        // Today's card view: pending/confirmed arriving today + already checked in today
        $todayCheckIns = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->where(function ($q) use ($today) {
                $q->whereIn('status', ['confirmed', 'pending'])
                  ->whereDate('check_in_date', $today);
            })
            ->orWhere(function ($q) use ($today) {
                $q->where('status', 'checked_in')
                  ->whereDate('check_in_date', $today);
            })
            ->orderBy('check_in_date', 'asc')
            ->get()
            ->map($mapReservation)
            ->values()
            ->toArray();

        // Available rooms — component uses room.number and room.type
        $availableRooms = \App\Models\Room::with('roomType')
            ->whereIn('status', ['available', 'reserved'])
            ->orderBy('room_number')
            ->get()
            ->map(fn ($rm) => [
                'id'     => $rm->id,
                'number' => $rm->room_number,
                'type'   => $rm->roomType?->name ?? 'Standard',
            ])
            ->values()
            ->toArray();

        return Inertia::render('Admin/Checkin/Index', [
            'user'                => $user,
            'navigation'          => app(DashboardController::class)->getNavigationForRole($role),
            'pendingReservations' => $pendingReservations,
            'availableRooms'      => $availableRooms,
            'todayCheckIns'       => $todayCheckIns,
        ]);
    })->name('checkin');

    Route::post('/checkin', [\App\Http\Controllers\FrontDesk\CheckInController::class, 'store'])->name('checkin.store');

    Route::post('/checkout', [\App\Http\Controllers\FrontDesk\CheckOutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/print', [\App\Http\Controllers\FrontDesk\CheckOutController::class, 'printReceipt'])->name('checkout.print');

    // Check-outs
    Route::get('/checkout', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $mapReservation = fn($r) => [
            'id'                   => $r->id,
            'guestName'            => optional($r->guest)->full_name ?? (optional($r->guest)->first_name . ' ' . optional($r->guest)->last_name),
            'roomNumber'           => optional($r->room)->room_number ?? 'N/A',
            'roomType'             => optional(optional($r->room)->roomType)->name ?? 'Standard',
            'reservation_number'   => $r->reservation_number ?? '#' . $r->id,
            'nights'               => $r->check_in_date && $r->check_out_date
                                        ? max(1, \Carbon\Carbon::parse($r->check_in_date)->diffInDays(\Carbon\Carbon::parse($r->check_out_date)))
                                        : 0,
            'check_in_date'        => $r->check_in_date,
            'check_out_date'       => $r->check_out_date,
            'departureTime'        => $r->check_out_date,
            'status'               => $r->status,
            'totalAmount'          => number_format((float) ($r->total_amount ?? 0), 2),
            'paidAmount'           => number_format((float) ($r->paid_amount ?? 0), 2),
            'balanceAmount'        => number_format(max(0, (float) ($r->total_amount ?? 0) - (float) ($r->paid_amount ?? 0)), 2),
            'unifiedTotal'         => number_format((float) ($r->total_amount ?? 0), 2),
            'unifiedBalance'       => number_format(max(0, (float) ($r->total_amount ?? 0) - (float) ($r->paid_amount ?? 0)), 2),
            'roomCharges'          => number_format((float) ($r->total_room_charges ?? $r->total_amount ?? 0), 2),
            'posCharges'           => '0.00',
            'serviceCharges'       => '0.00',
            'is_early_checkout'    => false,
            'actual_nights'        => 0,
            'scheduled_nights'     => 0,
            'key_card'             => null,
            'hasUnpaidBills'       => false,
            'posSales'             => [],
            'folio'                => null,
            'guest'                => $r->guest,
            'room'                 => $r->room,
        ];

        $todaysDepartures = \App\Models\Reservation::with(['guest', 'room.roomType'])
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->orderBy('check_out_date')
            ->get()->map($mapReservation)->values()->all();

        $allCheckedIn = \App\Models\Reservation::with(['guest', 'room.roomType'])
            ->where('status', 'checked_in')
            ->orderBy('check_out_date')
            ->get()->map($mapReservation)->values()->all();

        return Inertia::render('Admin/CheckOut', [
            'user'                  => $user,
            'navigation'            => app(DashboardController::class)->getNavigationForRole($role),
            'selectedReservationId' => $request->query('reservation_id'),
            'todaysDepartures'      => $todaysDepartures,
            'allCheckedIn'          => $allCheckedIn,
        ]);
    })->name('checkout');

    // Room Status
    Route::get('/rooms/status', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get all rooms with comprehensive data
        $rooms = \App\Models\Room::with([
            'roomType',
            'floorRelation',
            'bedType',
            'currentReservation.guest',
            'pendingReservations.guest'
        ])
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                // Get current reservation if room is occupied
                $currentReservation = $room->currentReservation;

                // Get pending reservation for check-in (if room is available)
                $pendingReservation = null;
                if ($room->status === 'available') {
                    $pendingReservation = \App\Models\Reservation::where('room_id', $room->id)
                        ->whereIn('status', ['confirmed', 'pending'])
                        ->whereDate('check_in_date', '<=', now()->addDay())
                        ->whereDate('check_out_date', '>', now())
                        ->with('guest')
                        ->orderBy('check_in_date')
                        ->first();
                }

                // Get key card info if occupied
                $keyCard = null;
                if ($room->status === 'occupied' && $currentReservation) {
                    $keyCard = \App\Models\KeyCard::where('reservation_id', $currentReservation->id)
                        ->where('is_active', true)
                        ->first();
                }

                // Calculate nights and totals
                $nights = 0;
                $totalAmount = 0;
                $balance = 0;
                $totalRoomCharges = 0;

                if ($currentReservation) {
                    $checkIn = \Carbon\Carbon::parse($currentReservation->check_in_date);
                    $checkOut = \Carbon\Carbon::parse($currentReservation->check_out_date);
                    $nights = $checkIn->diffInDays($checkOut);

                    $totalAmount = $currentReservation->total_amount ?? ($currentReservation->room_rate * $nights);
                    $totalRoomCharges = $totalAmount;

                    // Calculate payments made
                    $paymentsMade = $currentReservation->payments ? $currentReservation->payments->sum('amount') : 0;
                    $balance = max(0, $totalAmount - $paymentsMade);
                }

                // Get room type amenities
                $amenities = [];
                if ($room->roomType && $room->roomType->amenities) {
                    $amenitiesCollection = collect($room->roomType->amenities);
                    $amenities = $amenitiesCollection->pluck('name')->toArray();
                }

                // Determine housekeeping status
                $housekeepingStatus = $room->housekeeping_status;
                if (!$housekeepingStatus) {
                    if ($room->status === 'occupied') {
                        $housekeepingStatus = 'occupied';
                    } elseif ($room->status === 'available') {
                        $housekeepingStatus = 'clean';
                    }
                }

                return [
                    'id' => $room->id,
                    'number' => $room->room_number,
                    'status' => $room->status,
                    'type' => $room->roomType ? $room->roomType->name : 'Standard',
                    'floor' => $room->floorRelation ? 'Floor ' . $room->floorRelation->name : ($room->floor ? 'Floor ' . $room->floor : 'Ground Floor'),
                    'guest' => $currentReservation && $currentReservation->guest
                        ? trim($currentReservation->guest->first_name . ' ' . $currentReservation->guest->last_name)
                        : null,
                    'guest_phone' => $currentReservation && $currentReservation->guest
                        ? $currentReservation->guest->phone
                        : null,
                    'guest_email' => $currentReservation && $currentReservation->guest
                        ? $currentReservation->guest->email
                        : null,
                    'check_in' => $currentReservation ? $currentReservation->check_in_date : null,
                    'check_out' => $currentReservation ? $currentReservation->check_out_date : null,
                    'reservation_id' => $currentReservation ? $currentReservation->id : null,
                    'nights' => $nights,
                    'room_rate' => $currentReservation ? $currentReservation->room_rate : null,
                    'total_amount' => $totalAmount > 0 ? $totalAmount : null,
                    'total_room_charges' => $totalRoomCharges > 0 ? $totalRoomCharges : null,
                    'balance' => $balance,
                    'key_card' => $keyCard ? [
                        'card_number' => $keyCard->card_number,
                        'card_type' => $keyCard->card_type ?? 'Standard',
                    ] : null,
                    'pending_reservation' => $pendingReservation ? [
                        'id' => $pendingReservation->id,
                        'guest_name' => trim($pendingReservation->guest->first_name . ' ' . $pendingReservation->guest->last_name),
                        'reservation_number' => $pendingReservation->id,
                        'check_in_date' => $pendingReservation->check_in_date,
                        'check_out_date' => $pendingReservation->check_out_date,
                    ] : null,
                    'amenities' => $amenities,
                    'capacity' => $room->roomType ? $room->roomType->capacity : 2,
                    'bed_type' => $room->bedType ? $room->bedType->name : 'Standard',
                    'view_type' => $room->roomType ? ($room->roomType->view_type ?? 'Standard') : 'Standard',
                    'price' => $room->roomType ? $room->roomType->base_rate : 0,
                    'last_cleaned' => $room->last_cleaned_at ? $room->last_cleaned_at->format('Y-m-d H:i:s') : null,
                    'housekeeping_status' => $housekeepingStatus,
                    'updated_at' => $room->updated_at
                ];
            });

        // Calculate room status counts
        $roomStatus = [
            'available' => $rooms->where('status', 'available')->count(),
            'occupied' => $rooms->whereIn('status', ['occupied', 'checked_in'])->count(),
            'cleaning' => $rooms->where('status', 'cleaning')->count(),
            'maintenance' => $rooms->where('status', 'maintenance')->count(),
        ];

        // Get available key cards
        $availableKeyCards = \App\Models\KeyCard::where('is_active', false)
            ->whereNull('reservation_id')
            ->orderBy('card_number')
            ->get(['id', 'card_number', 'card_type']);

        return Inertia::render('Admin/Rooms/Status', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'rooms' => $rooms->values(),
            'roomStatus' => $roomStatus,
            'availableKeyCards' => $availableKeyCards
        ]);
    })->name('rooms.status');

    // Rooms
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    // Room Actions
    Route::post('/rooms/{room}/manual-checkout', [RoomController::class, 'manualCheckout'])->name('rooms.manual-checkout');
    Route::post('/rooms/{room}/mark-clean', [RoomController::class, 'markClean'])->name('rooms.mark-clean');

    // Admin mark-dirty route
    Route::post('/rooms/{id}/mark-dirty', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);
            $updateData = ['housekeeping_status' => 'dirty'];
            if ($room->status !== 'occupied') {
                $updateData['status'] = 'cleaning';
            }
            $room->update($updateData);
            return back()->with('success', "Room {$room->room_number} marked as dirty. Housekeeping will be notified.");
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to mark room as dirty: ' . $e->getMessage()]);
        }
    })->name('rooms.mark-dirty');

    // Room Types
    Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
    Route::get('/room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create');
    Route::post('/room-types', [RoomTypeController::class, 'store'])->name('room-types.store');
    Route::get('/room-types/{roomType}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit');
    Route::put('/room-types/{roomType}', [RoomTypeController::class, 'update'])->name('room-types.update');
    Route::delete('/room-types/{roomType}', [RoomTypeController::class, 'destroy'])->name('room-types.destroy');

    // Floors
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
    Route::get('/floors/create', [FloorController::class, 'create'])->name('floors.create');
    Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
    Route::get('/floors/{floor}/edit', [FloorController::class, 'edit'])->name('floors.edit');
    Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

    // Building Wings
    Route::get('/building-wings', [BuildingWingController::class, 'index'])->name('building-wings.index');
    Route::get('/building-wings/create', [BuildingWingController::class, 'create'])->name('building-wings.create');
    Route::post('/building-wings', [BuildingWingController::class, 'store'])->name('building-wings.store');
    Route::get('/building-wings/{buildingWing}/edit', [BuildingWingController::class, 'edit'])->name('building-wings.edit');
    Route::put('/building-wings/{buildingWing}', [BuildingWingController::class, 'update'])->name('building-wings.update');
    Route::delete('/building-wings/{buildingWing}', [BuildingWingController::class, 'destroy'])->name('building-wings.destroy');

    // Bed Types
    Route::get('/bed-types', [BedTypeController::class, 'index'])->name('bed-types.index');
    Route::get('/bed-types/create', [BedTypeController::class, 'create'])->name('bed-types.create');
    Route::post('/bed-types', [BedTypeController::class, 'store'])->name('bed-types.store');
    Route::get('/bed-types/{bedType}/edit', [BedTypeController::class, 'edit'])->name('bed-types.edit');
    Route::put('/bed-types/{bedType}', [BedTypeController::class, 'update'])->name('bed-types.update');
    Route::delete('/bed-types/{bedType}', [BedTypeController::class, 'destroy'])->name('bed-types.destroy');

    // Work Shifts
    Route::get('/work-shifts', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get work shifts from database
        $workShifts = \App\Models\WorkShift::with(['employeeShifts.user'])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($shift) {
                return [
                    'id' => $shift->id ?? null,
                    'shift_name' => $shift->name ?? 'Unnamed Shift',
                    'start_time' => $shift->start_time ?? null,
                    'end_time' => $shift->end_time ?? null,
                    'hours' => $shift->hours ?? 0,
                    'break_minutes' => $shift->break_minutes ?? 0,
                    'is_overnight' => $shift->is_overnight ?? false,
                    'is_active' => $shift->is_active ?? false,
                    'date' => $shift->date ?? null,
                    'department' => $shift->department ?? 'general', // Add department field
                    'type' => $shift->is_overnight ? 'night' : 'regular', // Add type based on is_overnight
                    'duration' => $shift->hours ?? 8, // Add duration field
                    'employees_count' => $shift->employeeShifts ? $shift->employeeShifts->count() : 0,
                    'employees' => $shift->employeeShifts ? $shift->employeeShifts->map(function ($employeeShift) {
                        return [
                            'id' => $employeeShift->id ?? null,
                            'user_id' => $employeeShift->user_id ?? null,
                            'effective_date' => $employeeShift->effective_date ?? null,
                            'days_of_week' => $employeeShift->days_of_week ?? [],
                            'user' => $employeeShift->user ? [
                                'name' => $employeeShift->user->first_name . ' ' . $employeeShift->user->last_name,
                                'email' => $employeeShift->user->email
                            ] : null
                        ];
                    })->toArray() : [],
                    'created_at' => $shift->created_at ?? null,
                    'updated_at' => $shift->updated_at ?? null
                ];
            });

        // Get statistics
        $stats = [
            'total_shifts' => $workShifts->count(),
            'active_shifts' => $workShifts->where('is_active', true)->count(),
            'completed_shifts' => $workShifts->where('is_active', false)->count(),
            'today_shifts' => $workShifts->filter(function ($shift) {
                return \Carbon\Carbon::parse($shift['created_at'])->isToday();
            })->count()
        ];

        return Inertia::render('Admin/WorkShifts/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'workShifts' => $workShifts,
            'shiftStats' => $stats,
            'shiftTemplates' => $workShifts, // Use workShifts as templates for now
            'currentShifts' => [], // Empty array for current shifts
            'staffUsers' => \App\Models\User::with('roles')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'email', 'employee_id'])
        ]);
    })->name('work-shifts.index');

    Route::post('/work-shifts', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i',
            'hours'         => 'nullable|numeric|min:0|max:24',
            'break_minutes' => 'nullable|integer|min:0|max:120',
            'is_overnight'  => 'boolean',
            'is_active'     => 'boolean',
        ]);
        $shift = \App\Models\WorkShift::create($validated);
        return back()->with('success', 'Work shift created.');
    })->name('work-shifts.store');

    Route::put('/work-shifts/{workShift}', function (\Illuminate\Http\Request $request, \App\Models\WorkShift $workShift) {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i',
            'hours'         => 'nullable|numeric|min:0|max:24',
            'break_minutes' => 'nullable|integer|min:0|max:120',
            'is_overnight'  => 'boolean',
            'is_active'     => 'boolean',
        ]);
        $workShift->update($validated);
        return back()->with('success', 'Work shift updated.');
    })->name('work-shifts.update');

    Route::delete('/work-shifts/{workShift}', function (\App\Models\WorkShift $workShift) {
        $workShift->delete();
        return back()->with('success', 'Work shift deleted.');
    })->name('work-shifts.destroy');

    Route::post('/work-shifts/{workShift}/assign', function (\Illuminate\Http\Request $request, \App\Models\WorkShift $workShift) {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'effective_date'=> 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:effective_date',
            'days_of_week'  => 'required|array',
        ]);
        \App\Models\EmployeeShift::updateOrCreate(
            ['user_id' => $validated['user_id'], 'work_shift_id' => $workShift->id],
            array_merge($validated, ['work_shift_id' => $workShift->id, 'is_active' => true])
        );
        return back()->with('success', 'Employee assigned to shift.');
    })->name('work-shifts.assign');

    Route::delete('/work-shifts/{workShift}/unassign/{employeeShift}', function (\App\Models\WorkShift $workShift, \App\Models\EmployeeShift $employeeShift) {
        $employeeShift->delete();
        return back()->with('success', 'Employee removed from shift.');
    })->name('work-shifts.unassign');

    // Schedules
    Route::get('/schedules', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get work shifts
        $workShifts = \App\Models\WorkShift::with(['employeeShifts.user'])
            ->orderBy('start_time', 'asc')
            ->get();

        // Get all users for assignment
        $staffUsers = \App\Models\User::with('roles')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'employee_id']);

        // Get current week's employee shifts
        $currentWeekStart = \Carbon\Carbon::now()->startOfWeek();
        $currentWeekEnd = \Carbon\Carbon::now()->endOfWeek();

        $employeeShifts = \App\Models\EmployeeShift::with(['user', 'workShift'])
            ->whereBetween('effective_date', [$currentWeekStart, $currentWeekEnd])
            ->get();

        return Inertia::render('Admin/Schedules/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'workShifts' => $workShifts,
            'staffUsers' => $staffUsers,
            'employeeShifts' => $employeeShifts,
            'currentWeekStart' => $currentWeekStart->format('Y-m-d'),
            'currentWeekEnd' => $currentWeekEnd->format('Y-m-d')
        ]);
    })->name('schedules.index');

    // Employee Shifts
    Route::post('/employee-shifts', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'work_shift_id'  => 'required|exists:work_shifts,id',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);
        \App\Models\EmployeeShift::updateOrCreate(
            ['user_id' => $data['user_id'], 'effective_date' => $data['effective_date']],
            ['work_shift_id' => $data['work_shift_id'], 'notes' => $data['notes'] ?? null]
        );
        return back()->with('success', 'Shift assigned successfully.');
    })->name('employee-shifts.store');
    Route::delete('/employee-shifts/{employeeShift}', function ($id) {
        \App\Models\EmployeeShift::findOrFail($id)->delete();
        return back()->with('success', 'Shift removed.');
    })->name('employee-shifts.destroy');

    // Housekeeping Schedules - Using Controller
    Route::get('/housekeeping-schedules', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'index'])->name('housekeeping-schedules.index');
    Route::get('/housekeeping-schedules/create', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'create'])->name('housekeeping-schedules.create');
    Route::post('/housekeeping-schedules', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'store'])->name('housekeeping-schedules.store');
    Route::get('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'show'])->name('housekeeping-schedules.show');
    Route::get('/housekeeping-schedules/{housekeepingSchedule}/edit', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'edit'])->name('housekeeping-schedules.edit');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'update'])->name('housekeeping-schedules.update');
    Route::delete('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'destroy'])->name('housekeeping-schedules.destroy');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}/status', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'updateStatus'])->name('housekeeping-schedules.update-status');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}/rooms/{room}/status', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'updateRoomStatus'])->name('housekeeping-schedules.update-room-status');
    Route::get('/housekeeping-schedules-calendar', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'calendar'])->name('housekeeping-schedules.calendar');
    Route::post('/housekeeping-schedules/{housekeepingSchedule}/duplicate', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'duplicate'])->name('housekeeping-schedules.duplicate');

    // Maintenance Dashboard
    Route::get('/maintenance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = [
            'total' => \App\Models\MaintenanceRequest::count(),
            'open' => \App\Models\MaintenanceRequest::where('status', 'open')->count(),
            'assigned' => \App\Models\MaintenanceRequest::where('status', 'assigned')->count(),
            'in_progress' => \App\Models\MaintenanceRequest::where('status', 'in_progress')->count(),
            'on_hold' => \App\Models\MaintenanceRequest::where('status', 'on_hold')->count(),
            'resolved' => \App\Models\MaintenanceRequest::where('status', 'resolved')->count(),
            'closed' => \App\Models\MaintenanceRequest::where('status', 'closed')->count(),
            'urgent' => \App\Models\MaintenanceRequest::where('priority', 'urgent')->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'high' => \App\Models\MaintenanceRequest::where('priority', 'high')->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'avg_resolution_hours' => round(\App\Models\MaintenanceRequest::whereNotNull('resolved_at')->whereNotNull('reported_at')->selectRaw('AVG(TIMESTAMPDIFF(HOUR, reported_at, resolved_at)) as avg_hours')->value('avg_hours') ?? 0, 1),
        ];

        $recentRequests = \App\Models\MaintenanceRequest::with(['room', 'assignedTo'])
            ->orderBy('reported_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'request_number' => $r->request_number,
                'title' => $r->title,
                'category' => $r->category,
                'priority' => $r->priority,
                'status' => $r->status,
                'location' => $r->location ?? ($r->room?->room_number ? 'Room ' . $r->room->room_number : null),
                'assigned_to' => $r->assignedTo?->full_name,
                'reported_at' => $r->reported_at?->format('Y-m-d H:i'),
            ]);

        $categories = \App\Models\MaintenanceCategory::orderBy('sort_order')
            ->get(['id', 'name', 'code', 'color', 'icon', 'is_active', 'sort_order']);

        $categoryBreakdown = \App\Models\MaintenanceRequest::selectRaw('category, count(*) as count')
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->groupBy('category')
            ->pluck('count', 'category');

        return Inertia::render('Admin/Maintenance/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'categories' => $categories,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    })->name('maintenance');

    // Maintenance Requests
    Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index'])->name('maintenance-requests.index');
    Route::get('/maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance-requests.create');
    Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance-requests.store');
    Route::get('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])->name('maintenance-requests.show');
    Route::post('/maintenance-requests/{maintenanceRequest}/assign', [MaintenanceRequestController::class, 'assign'])->name('maintenance-requests.assign');
    Route::post('/maintenance-requests/{maintenanceRequest}/update-status', [MaintenanceRequestController::class, 'updateStatus'])->name('maintenance-requests.update-status');
    Route::delete('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'destroy'])->name('maintenance-requests.destroy');

    // Maintenance Categories
    Route::get('/maintenance-categories', [MaintenanceCategoryController::class, 'index'])->name('maintenance-categories.index');
    Route::get('/maintenance-categories/create', [MaintenanceCategoryController::class, 'create'])->name('maintenance-categories.create');
    Route::post('/maintenance-categories', [MaintenanceCategoryController::class, 'store'])->name('maintenance-categories.store');
    Route::get('/maintenance-categories/{maintenanceCategory}/edit', [MaintenanceCategoryController::class, 'edit'])->name('maintenance-categories.edit');
    Route::put('/maintenance-categories/{maintenanceCategory}', [MaintenanceCategoryController::class, 'update'])->name('maintenance-categories.update');
    Route::delete('/maintenance-categories/{maintenanceCategory}', [MaintenanceCategoryController::class, 'destroy'])->name('maintenance-categories.destroy');
    Route::post('/maintenance-categories/{maintenanceCategory}/toggle-active', [MaintenanceCategoryController::class, 'toggleActive'])->name('maintenance-categories.toggle-active');

    // Devices
    Route::get('/devices', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Devices/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('devices.index');

    // Preventive Maintenance (admin view uses same data as maintenance module)
    Route::get('/maintenance/preventive/scheduled', [\App\Http\Controllers\Maintenance\PreventiveController::class, 'scheduled'])
        ->name('maintenance.preventive.scheduled');

    // Store preventive maintenance task (admin)
    Route::post('/maintenance/preventive', [\App\Http\Controllers\Maintenance\PreventiveController::class, 'store'])
        ->name('maintenance.preventive.store');

    // Housekeeping Tasks (admin)
    Route::get('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'index'])
        ->name('housekeeping-tasks.index');
    Route::get('/housekeeping-tasks/create', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'create'])
        ->name('housekeeping-tasks.create');
    Route::post('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'store'])
        ->name('housekeeping-tasks.store');
    Route::get('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'show'])
        ->name('housekeeping-tasks.show');
    Route::get('/housekeeping-tasks/{housekeepingTask}/edit', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'edit'])
        ->name('housekeeping-tasks.edit');
    Route::put('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'update'])
        ->name('housekeeping-tasks.update');
    Route::delete('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'destroy'])
        ->name('housekeeping-tasks.destroy');
    Route::post('/housekeeping-tasks/generate-daily', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'generateDailyTasks'])
        ->name('housekeeping-tasks.generate-daily');

    // Time Tracking (admin)
    Route::get('/time-tracking', [\App\Http\Controllers\Admin\TimeTrackingController::class, 'index'])
        ->name('time-tracking.index');

    // Export time tracking CSV
    Route::get('/time-tracking/export', [\App\Http\Controllers\Admin\TimeTrackingController::class, 'export'])
        ->name('time-tracking.export');

    // View a single staff time entry from admin time tracking page
    Route::get('/staff/time-tracking/{timeEntry}', [\App\Http\Controllers\Manager\TimeTrackingController::class, 'show'])
        ->name('staff.time-tracking.show');

    // Services
    Route::get('/services', [HotelServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [HotelServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [HotelServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [HotelServiceController::class, 'destroy'])->name('services.destroy');

    // Concierge Services (Admin)
    Route::get('/services/concierge', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $requests = \App\Models\ConciergeRequest::orderBy('requested_at', 'desc')
            ->paginate(20)->withQueryString();

        $stats = [
            'pending'     => \App\Models\ConciergeRequest::where('status', 'pending')->count(),
            'in_progress' => \App\Models\ConciergeRequest::where('status', 'in_progress')->count(),
            'completed'   => \App\Models\ConciergeRequest::where('status', 'completed')->count(),
            'total'       => \App\Models\ConciergeRequest::count(),
        ];

        return Inertia::render('Admin/Services/Concierge', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'requests'   => $requests,
            'stats'      => $stats,
        ]);
    })->name('services.concierge');

    Route::post('/services/concierge', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'guest_name'   => 'required|string|max:255',
            'room_number'  => 'required|string|max:50',
            'service_type' => 'required|string|max:100',
            'details'      => 'nullable|string',
        ]);
        \App\Models\ConciergeRequest::create([
            'request_number' => 'CON-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'guest_name'     => $validated['guest_name'],
            'room_number'    => $validated['room_number'],
            'service_type'   => $validated['service_type'],
            'details'        => $validated['details'] ?? null,
            'status'         => 'pending',
            'requested_at'   => now(),
            'created_by'     => auth()->id(),
        ]);
        return back()->with('success', 'Concierge request created successfully.');
    })->name('services.concierge.store');

    Route::post('/services/concierge/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $concierge = \App\Models\ConciergeRequest::findOrFail($id);
        $concierge->update(['status' => $request->input('status', 'in_progress')]);
        return back()->with('success', 'Concierge request status updated.');
    })->name('services.concierge.update-status');

    // Laundry Management
    Route::get('/laundry', [LaundryController::class, 'index'])->name('laundry.index');
    Route::get('/laundry/create', [LaundryController::class, 'create'])->name('laundry.create');
    Route::post('/laundry', [LaundryController::class, 'store'])->name('laundry.store');
    Route::get('/laundry/{laundry}', [LaundryController::class, 'show'])->name('laundry.show');
    Route::patch('/laundry/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundry.update-status');
    Route::delete('/laundry/{laundry}', [LaundryController::class, 'destroy'])->name('laundry.destroy');

    // Packages
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/availability', [PackageController::class, 'availability'])->name('packages.availability');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');
    Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

    // Group Bookings
    Route::get('/group-bookings', [GroupBookingController::class, 'index'])->name('group-bookings.index');
    Route::get('/group-bookings/create', [GroupBookingController::class, 'create'])->name('group-bookings.create');
    Route::post('/group-bookings', [GroupBookingController::class, 'store'])->name('group-bookings.store');
    Route::get('/group-bookings/{groupBooking}', [GroupBookingController::class, 'show'])->name('group-bookings.show');
    Route::get('/group-bookings/{groupBooking}/edit', [GroupBookingController::class, 'edit'])->name('group-bookings.edit');
    Route::put('/group-bookings/{groupBooking}', [GroupBookingController::class, 'update'])->name('group-bookings.update');
    Route::delete('/group-bookings/{groupBooking}', [GroupBookingController::class, 'destroy'])->name('group-bookings.destroy');
    Route::get('/group-bookings/{groupBooking}/details', [GroupBookingController::class, 'details'])->name('group-bookings.details');
    Route::get('/group-bookings/{groupBooking}/invoices', [GroupBookingController::class, 'invoices'])->name('group-bookings.invoices');
    Route::post('/group-bookings/{groupBooking}/process-payment', [GroupBookingController::class, 'processPayment'])->name('group-bookings.process-payment');

    // Hall Bookings (Standalone)
    Route::get('/hall-bookings', [HallBookingController::class, 'index'])->name('hall-bookings.index');
    Route::get('/hall-bookings/create', [HallBookingController::class, 'create'])->name('hall-bookings.create');
    Route::post('/hall-bookings', [HallBookingController::class, 'store'])->name('hall-bookings.store');
    Route::get('/hall-bookings/{hallBooking}', [HallBookingController::class, 'show'])->name('hall-bookings.show');
    Route::get('/hall-bookings/{hallBooking}/edit', [HallBookingController::class, 'edit'])->name('hall-bookings.edit');
    Route::put('/hall-bookings/{hallBooking}', [HallBookingController::class, 'update'])->name('hall-bookings.update');
    Route::delete('/hall-bookings/{hallBooking}', [HallBookingController::class, 'destroy'])->name('hall-bookings.destroy');
    Route::post('/hall-bookings/{hallBooking}/process-payment', [HallBookingController::class, 'processPayment'])->name('hall-bookings.process-payment');

    // Halls
    Route::get('/halls', [HallController::class, 'index'])->name('halls.index');
    Route::get('/halls/availability', [HallController::class, 'availability'])->name('halls.availability');
    Route::get('/halls/create', [HallController::class, 'create'])->name('halls.create');
    Route::post('/halls', [HallController::class, 'store'])->name('halls.store');
    Route::get('/halls/{hall}', [HallController::class, 'show'])->name('halls.show');
    Route::get('/halls/{hall}/edit', [HallController::class, 'edit'])->name('halls.edit');
    Route::put('/halls/{hall}', [HallController::class, 'update'])->name('halls.update');
    Route::delete('/halls/{hall}', [HallController::class, 'destroy'])->name('halls.destroy');

    // Channel Manager - Using Controller
    Route::get('/channel-manager', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'index'])->name('channel-manager.index');
    Route::get('/channel-manager/create', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'create'])->name('channel-manager.create');
    Route::post('/channel-manager', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'store'])->name('channel-manager.store');
    Route::get('/channel-manager/{reservation}', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'show'])->name('channel-manager.show');
    Route::get('/channel-manager/{reservation}/edit', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'edit'])->name('channel-manager.edit');
    Route::put('/channel-manager/{reservation}', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'update'])->name('channel-manager.update');
    Route::post('/channel-manager/sync-inventory', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'syncInventory'])->name('channel-manager.sync-inventory');

    // Waitlist
    Route::get('/waitlist', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get waitlist entries (using reservations as waitlist for now)
        $waitlistEntries = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->where('status', 'waitlist')
            ->orWhere(function($query) {
                $query->where('status', 'pending')
                     ->whereDate('check_in_date', '>', today());
            })
            ->orderBy('check_in_date', 'asc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest ? $reservation->guest->first_name . ' ' . $reservation->guest->last_name : 'Unknown Guest',
                    'guest_email' => $reservation->guest ? $reservation->guest->email : 'N/A',
                    'guest_phone' => $reservation->guest ? $reservation->guest->phone : 'N/A',
                    'room_type' => $reservation->roomType ? $reservation->roomType->name : 'Any',
                    'check_in_date' => $reservation->check_in_date,
                    'check_out_date' => $reservation->check_out_date,
                    'guests_count' => $reservation->guests_count ?? 1,
                    'total_amount' => $reservation->total_amount,
                    'status' => $reservation->status,
                    'priority' => 'normal', // Could be added to database later
                    'notes' => $reservation->notes ?? '',
                    'created_at' => $reservation->created_at
                ];
            });

        return Inertia::render('Admin/Waitlist/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'waitlists' => [
                'data' => $waitlistEntries->toArray(),
                'total' => $waitlistEntries->count(),
                'from' => 1,
                'to' => $waitlistEntries->count(),
                'links' => []
            ],
            'stats' => [
                'total' => $waitlistEntries->count(),
                'active' => $waitlistEntries->where('status', 'waitlist')->count(),
                'pending' => $waitlistEntries->where('status', 'pending')->count(),
                'this_week' => $waitlistEntries->filter(function($entry) {
                    $entryDate = \Carbon\Carbon::parse($entry['check_in_date']);
                    $weekFromNow = \Carbon\Carbon::now()->addDays(7);
                    return $entryDate <= $weekFromNow;
                })->count()
            ]
        ]);
    })->name('waitlist.index');

    Route::get('/waitlist/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get guests for selection
        $guests = \App\Models\Guest::orderBy('first_name')->get();

        // Get room types for selection
        $roomTypes = \App\Models\RoomType::orderBy('name')->get();

        return Inertia::render('Admin/Waitlist/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guests' => $guests,
            'roomTypes' => $roomTypes
        ]);
    })->name('waitlist.create');

    Route::post('/waitlist/check-availability', function () {
        // Check room availability for waitlist
        $checkInDate = request('check_in_date');
        $checkOutDate = request('check_out_date');
        $roomTypeId = request('room_type_id');

        $availableRooms = \App\Models\Room::where('room_type_id', $roomTypeId)
            ->where('status', 'available')
            ->whereDoesntHave('reservations', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('status', '!=', 'cancelled')
                      ->where(function ($q) use ($checkInDate, $checkOutDate) {
                          $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                            ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                            ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                                $subQ->where('check_in_date', '<=', $checkInDate)
                                     ->where('check_out_date', '>=', $checkOutDate);
                            });
                      });
            })
            ->count();

        return response()->json([
            'available' => $availableRooms > 0,
            'available_rooms' => $availableRooms
        ]);
    })->name('waitlist.check-availability');

    Route::post('/waitlist/auto-notify', function () {
        try {
            // Get eligible waitlist entries
            $eligibleEntries = \App\Models\Reservation::with(['guest'])
                ->where('status', 'waitlist')
                ->whereDate('check_in_date', '>=', today())
                ->whereDate('check_in_date', '<=', today()->addDays(7))
                ->get();

            $notificationsSent = 0;

            foreach ($eligibleEntries as $entry) {
                // Check if rooms are now available for their requested dates
                $availableRooms = \App\Models\Room::where('room_type_id', $entry->room_type_id ?? 1)
                    ->where('status', 'available')
                    ->whereDoesntHave('reservations', function ($query) use ($entry) {
                        $query->where('status', '!=', 'cancelled')
                              ->where(function ($q) use ($entry) {
                                  $q->whereBetween('check_in_date', [$entry->check_in_date, $entry->check_out_date])
                                    ->orWhereBetween('check_out_date', [$entry->check_in_date, $entry->check_out_date])
                                    ->orWhere(function ($subQ) use ($entry) {
                                        $subQ->where('check_in_date', '<=', $entry->check_in_date)
                                             ->where('check_out_date', '>=', $entry->check_out_date);
                                    });
                              });
                    })
                    ->count();

                if ($availableRooms > 0 && $entry->guest) {
                    // In a real implementation, this would send actual notifications
                    // For now, we'll just log that a notification would be sent
                    \Log::info("Notification would be sent to guest: {$entry->guest->email} for waitlist entry #{$entry->id}");
                    $notificationsSent++;

                    // Update the entry status to notified
                    $entry->status = 'notified';
                    $entry->save();
                }
            }

            return response()->json([
                'success' => true,
                'notifications_sent' => $notificationsSent,
                'message' => "Successfully sent {$notificationsSent} notifications"
            ]);

        } catch (\Exception $e) {
            \Log::error('Auto-notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete auto-notification: ' . $e->getMessage()
            ], 500);
        }
    })->name('waitlist.auto-notify');

    // Settings
    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings/Index');
    })->name('settings.index');
    Route::post('/settings', function () {
        return redirect()->back()->with('success', 'Settings updated successfully');
    })->name('settings.store');
    Route::put('/settings', function (Request $request) {
        $settings = $request->input('settings', []);

        $booleanKeys = [
            'auto_apply_guest_type_discount', 'auto_apply_vip_discount',
            'require_2fa', 'force_password_change',
            'pos_print_show_logo', 'frontdesk_print_show_logo',
            'enable_vod', 'enable_parental_controls',
        ];

        foreach ($settings as $key => $value) {
            if (strpos($key, 'theme_') === 0) {
                \App\Models\Setting::set($key, $value, 'string', 'theme');
            } elseif (in_array($key, $booleanKeys, true)) {
                \App\Models\Setting::set($key, $value ? '1' : '0', 'boolean', 'general');
            } elseif (is_numeric($value) && strpos((string) $value, '.') !== false) {
                \App\Models\Setting::set($key, $value, 'float', 'general');
            } elseif (is_int($value)) {
                \App\Models\Setting::set($key, $value, 'integer', 'general');
            } else {
                \App\Models\Setting::set($key, $value, 'string', 'general');
            }
        }

        return response()->json(['success' => true, 'message' => 'Settings updated successfully']);
    })->name('settings.update');

    // Logo upload / remove
    Route::post('/settings/logo', [\App\Http\Controllers\SettingsController::class, 'uploadLogo'])->name('settings.logo.upload');
    Route::delete('/settings/logo', [\App\Http\Controllers\SettingsController::class, 'removeLogo'])->name('settings.logo.remove');

    // Backup
    Route::get('/settings/backup', [BackupController::class, 'index'])->name('settings.backup');
    Route::post('/settings/backup', [BackupController::class, 'create'])->name('settings.backup.create');
    Route::get('/settings/backup/download/{backup}', [BackupController::class, 'download'])->name('settings.backup.download');
    Route::post('/settings/backup/restore/{backup}', [BackupController::class, 'restore'])->name('settings.backup.restore');
    Route::delete('/settings/backup/{backup}', [BackupController::class, 'delete'])->name('settings.backup.delete');

    // License
    Route::get('/settings/license', [LicenseController::class, 'index'])->name('settings.license');
    Route::get('/license/info', [LicenseController::class, 'info'])->name('license.info');
    Route::post('/settings/license/activate', [LicenseController::class, 'activate'])->name('settings.license.activate');
    Route::post('/settings/license/refresh', [LicenseController::class, 'refresh'])->name('settings.license.refresh');
    Route::post('/settings/license/deactivate', [LicenseController::class, 'deactivate'])->name('settings.license.deactivate');

    // Settings (main route for frontend)
    Route::get('/settings/main', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Load theme settings from database
        $themeSettings = [];
        $themeKeys = [
            'theme_mode', 'theme_primary_color', 'theme_secondary_color', 'theme_success_color',
            'theme_warning_color', 'theme_danger_color', 'theme_background_color', 'theme_sidebar_color',
            'theme_card_color', 'theme_text_primary', 'theme_text_secondary', 'theme_text_tertiary',
            'theme_border_color', 'theme_radius', 'theme_shadow', 'theme_transition'
        ];

        foreach ($themeKeys as $key) {
            $setting = \App\Models\Setting::where('key', $key)->first();
            if ($setting) {
                $themeSettings[$key] = $setting->value;
            }
        }

        // Load general settings from database
        $generalKeys = [
            'hotel_name', 'hotel_address', 'hotel_phone', 'hotel_email', 'timezone',
            'currency', 'currency_position', 'tax_rate', 'hotel_logo',
            'auto_apply_guest_type_discount', 'auto_apply_vip_discount',
            'vip_discount_percentage', 'discount_combination_mode',
            'session_timeout', 'password_min_length', 'require_2fa', 'force_password_change',
            'pos_print_paper_width', 'pos_print_font_size', 'pos_print_show_logo',
            'frontdesk_print_paper_width', 'frontdesk_print_font_size', 'frontdesk_print_show_logo',
            'iptv_server_url', 'default_channel_package', 'enable_vod', 'enable_parental_controls',
            'backup_frequency', 'backup_retention_days',
        ];
        $generalSettings = [];
        foreach ($generalKeys as $key) {
            $setting = \App\Models\Setting::where('key', $key)->first();
            if ($setting) {
                $generalSettings[$key] = $setting->value;
            }
        }

        return Inertia::render('Admin/Settings/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'settings' => [
                'theme'   => $themeSettings,
                'general' => $generalSettings,
            ]
        ]);
    })->name('settings');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = ['total_customers' => 0, 'total_sales' => 0, 'revenue_today' => 0, 'revenue_month' => 0];
        $recentSales     = [];
        $recentCustomers = [];

        try {
            if (class_exists(\App\Models\Customer::class)) {
                $stats['total_customers'] = \App\Models\Customer::count();
                $recentCustomers = \App\Models\Customer::orderByDesc('created_at')->limit(5)
                    ->get(['id','first_name','last_name','customer_code','created_at'])
                    ->map(fn($c) => ['id' => $c->id, 'first_name' => $c->first_name, 'last_name' => $c->last_name, 'customer_code' => $c->customer_code, 'created_at' => $c->created_at]);
            }
            if (class_exists(\App\Models\Sale::class)) {
                $stats['total_sales']   = \App\Models\Sale::count();
                $today        = now()->toDateString();
                $startOfMonth = now()->startOfMonth()->toDateString();
                $endOfMonth   = now()->endOfMonth()->toDateString();
                $stats['revenue_today'] = (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount');
                $stats['revenue_month'] = (float) \App\Models\Sale::whereBetween('created_at', [$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])->sum('total_amount');
                $recentSales = \App\Models\Sale::orderByDesc('created_at')->limit(5)
                    ->get(['id','sale_number','sale_date','created_at','total_amount'])
                    ->map(fn($s) => ['id' => $s->id, 'sale_number' => $s->sale_number, 'sale_date' => $s->sale_date ?? $s->created_at, 'total_amount' => (float)$s->total_amount]);
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Admin/Reports/Index', [
            'user'            => $user,
            'navigation'      => app(DashboardController::class)->getNavigationForRole($role),
            'stats'           => $stats,
            'recentSales'     => $recentSales,
            'recentCustomers' => $recentCustomers,
        ]);
    })->name('reports.index');

    // Occupancy Reports
    Route::get('/reports/occupancy', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $stats = ['total_rooms' => 0, 'occupied_today' => 0, 'available_today' => 0, 'occupancy_today_pct' => 0.0, 'occupancy_month_pct' => 0.0];
        $byType = [];
        $recentReservations = [];

        try {
            $today        = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            if (class_exists(\App\Models\Room::class)) {
                $stats['total_rooms'] = \App\Models\Room::count();
                if (\Schema::hasColumn('rooms', 'type')) {
                    $byType = \App\Models\Room::select('type', \DB::raw('count(*) as total'))
                        ->groupBy('type')->get()
                        ->map(fn($r) => ['type' => $r->type, 'total' => (int)$r->total])->toArray();
                }
            }

            if (class_exists(\App\Models\Reservation::class)) {
                $q = \App\Models\Reservation::query();
                if (\Schema::hasColumn('reservations', 'check_in_date') && \Schema::hasColumn('reservations', 'check_out_date')) {
                    $q->where('check_in_date', '<=', $today)->where('check_out_date', '>', $today);
                }
                if (\Schema::hasColumn('reservations', 'status')) {
                    $q->whereNotIn('status', ['cancelled', 'canceled']);
                }
                $stats['occupied_today']      = (int) $q->distinct('room_id')->count('room_id');
                $stats['available_today']     = max(0, $stats['total_rooms'] - $stats['occupied_today']);
                $stats['occupancy_today_pct'] = $stats['total_rooms'] > 0 ? round(($stats['occupied_today'] / $stats['total_rooms']) * 100, 1) : 0.0;

                if ($stats['total_rooms'] > 0 && \Schema::hasColumn('reservations', 'check_in_date')) {
                    $monthNights    = (int) now()->endOfMonth()->day;
                    $totalRoomNights = $stats['total_rooms'] * $monthNights;
                    $occupiedNights = (int) \App\Models\Reservation::query()
                        ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->whereBetween('check_in_date', [$startOfMonth, $endOfMonth])
                              ->orWhereBetween('check_out_date', [$startOfMonth, $endOfMonth])
                              ->orWhere(fn($qq) => $qq->where('check_in_date', '<=', $startOfMonth)->where('check_out_date', '>=', $endOfMonth));
                        })
                        ->when(\Schema::hasColumn('reservations', 'status'), fn($q) => $q->whereNotIn('status', ['cancelled', 'canceled']))
                        ->sum(\DB::raw('DATEDIFF(LEAST(check_out_date,"'.$endOfMonth.'"),GREATEST(check_in_date,"'.$startOfMonth.'"))'));
                    $stats['occupancy_month_pct'] = $totalRoomNights > 0 ? round(($occupiedNights / $totalRoomNights) * 100, 1) : 0.0;
                }

                $recentReservations = \App\Models\Reservation::orderByDesc('created_at')->limit(8)
                    ->get(['id','reservation_code','guest_name','room_id','check_in_date','check_out_date','status'])
                    ->map(fn($r) => [
                        'id'               => $r->id,
                        'reservation_code' => $r->reservation_code ?? $r->id,
                        'guest_name'       => $r->guest_name ?? null,
                        'room_id'          => $r->room_id,
                        'check_in_date'    => $r->check_in_date,
                        'check_out_date'   => $r->check_out_date,
                        'status'           => $r->status ?? 'booked',
                    ]);
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Admin/Reports/Occupancy', [
            'user'               => $user,
            'navigation'         => app(DashboardController::class)->getNavigationForRole($role),
            'stats'              => $stats,
            'byType'             => $byType,
            'recentReservations' => $recentReservations,
        ]);
    })->name('reports.occupancy');

    // Revenue Reports
    Route::get('/reports/revenue', function () {
        $user  = auth()->user()->load('roles');
        $role  = $user->roles->first()?->name ?? 'staff';
        $start = request('start_date') ?: now()->subDays(29)->toDateString();
        $end   = request('end_date')   ?: now()->toDateString();

        $fmt = fn($v) => number_format((float)$v, 2);

        $stats       = ['start_date' => $start, 'end_date' => $end, 'total_revenue' => 0.0, 'total_sales' => 0, 'avg_order_value' => 0.0];
        $daily       = [];
        $recentSales = [];

        // revenueData structure expected by Revenue.vue
        $posRevenue  = 0.0;
        $roomRevenue = 0.0;
        $hallRevenue = 0.0;
        $revByCategory = [];

        // expenseData
        $totalExpenses   = 0.0;
        $expByCategory   = [];

        try {
            // ── Sales (POS) ───────────────────────────────────────────
            if (class_exists(\App\Models\Sale::class)) {
                $dateCol = \Schema::hasColumn('sales', 'sale_date') ? 'sale_date' : 'created_at';
                $base = \App\Models\Sale::query();
                if ($dateCol === 'sale_date') $base->whereBetween('sale_date', [$start, $end]);
                else                          $base->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);

                $stats['total_sales']     = (int)   $base->count();
                // Pre-tax: subtract tax_amount from total_amount
                $posRevenue               = (float) (\App\Models\Sale::query()
                    ->when($dateCol === 'sale_date',  fn($q) => $q->whereBetween('sale_date',  [$start, $end]))
                    ->when($dateCol === 'created_at', fn($q) => $q->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']))
                    ->selectRaw('COALESCE(SUM(total_amount - COALESCE(tax_amount, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue']   = $posRevenue;
                $stats['avg_order_value'] = $stats['total_sales'] > 0 ? round($posRevenue / $stats['total_sales'], 2) : 0.0;

                $rawDate = $dateCol === 'created_at' ? 'DATE(created_at)' : $dateCol;
                $daily = \App\Models\Sale::selectRaw("$rawDate as d, COUNT(*) as orders, SUM(total_amount - COALESCE(tax_amount, 0)) as revenue")
                    ->when($dateCol === 'sale_date',  fn($q) => $q->whereBetween('sale_date',  [$start, $end]))
                    ->when($dateCol === 'created_at', fn($q) => $q->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']))
                    ->groupBy('d')->orderBy('d')->get()
                    ->map(fn($r) => ['date' => (string)$r->d, 'orders' => (int)$r->orders, 'revenue' => (float)$r->revenue])->toArray();

                $recentSales = \App\Models\Sale::query()
                    ->when($dateCol === 'sale_date',  fn($q) => $q->whereBetween('sale_date',  [$start, $end]))
                    ->when($dateCol === 'created_at', fn($q) => $q->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']))
                    ->orderByDesc($dateCol)->limit(10)
                    ->get(['id','sale_number','sale_date','total_amount','tax_amount','created_at'])
                    ->map(fn($s) => ['id' => $s->id, 'sale_number' => $s->sale_number, 'sale_date' => $s->sale_date ?? $s->created_at, 'total_amount' => (float)($s->total_amount - ($s->tax_amount ?? 0))]);
            }

            // ── Room Revenue (Reservations) ───────────────────────────
            if (class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'total_amount')) {
                // Pre-tax revenue: subtract taxes column from total_amount
                $roomRevenue = (float) (\App\Models\Reservation::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))
                    ->selectRaw('COALESCE(SUM(total_amount - COALESCE(taxes, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue'] += $roomRevenue;
            }

            // ── Hall Booking Revenue ──────────────────────────────────
            if (class_exists(\App\Models\HallBooking::class)) {
                // Hall bookings: total_amount = base_price × hours, no tax component
                $hallRevenue = (float) \App\Models\HallBooking::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->sum('total_amount');
                $stats['total_revenue'] += $hallRevenue;
            }

            // Revenue by category
            $revByCategory = array_filter([
                $roomRevenue > 0 ? ['category' => 'Room Revenue', 'amount' => $roomRevenue, 'formatted_amount' => $fmt($roomRevenue)] : null,
                $hallRevenue > 0 ? ['category' => 'Hall Revenue', 'amount' => $hallRevenue, 'formatted_amount' => $fmt($hallRevenue)] : null,
                $posRevenue  > 0 ? ['category' => 'POS / F&B',    'amount' => $posRevenue,  'formatted_amount' => $fmt($posRevenue)]  : null,
            ]);

            // ── Expenses ──────────────────────────────────────────────
            if (class_exists(\App\Models\Expense::class)) {
                $expBase = \App\Models\Expense::whereBetween('expense_date', [$start, $end]);
                $totalExpenses = (float) $expBase->sum('amount');

                $expByCategory = \App\Models\Expense::selectRaw('expense_category_id, SUM(amount) as total')
                    ->whereBetween('expense_date', [$start, $end])
                    ->groupBy('expense_category_id')
                    ->with('category:id,name')
                    ->get()
                    ->map(fn($e) => [
                        'category'         => $e->category?->name ?? 'Uncategorized',
                        'amount'           => (float)$e->total,
                        'formatted_amount' => $fmt($e->total),
                    ])->toArray();
            }
        } catch (\Throwable $e) {}

        $netProfit = $stats['total_revenue'] - $totalExpenses;
        $netMargin = $stats['total_revenue'] > 0 ? round(($netProfit / $stats['total_revenue']) * 100, 1) : 0.0;
        $adr       = $stats['total_sales'] > 0 ? round($stats['total_revenue'] / $stats['total_sales'], 2) : 0.0;

        return Inertia::render('Admin/Reports/Revenue', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'stats'       => $stats,
            'daily'       => $daily,
            'recentSales' => $recentSales,
            'revenueData' => [
                'total_revenue'        => $stats['total_revenue'],
                'room_revenue'         => $roomRevenue,
                'hall_revenue'         => $hallRevenue,
                'average_daily_rate'   => $adr,
                'revenue_by_category'  => array_values($revByCategory),
                'currency'             => ['code' => \App\Models\Setting::get('currency', 'USD'), 'symbol' => '', 'position' => 'before'],
            ],
            'expenseData' => [
                'total_expenses'        => $totalExpenses,
                'expenses_by_category'  => $expByCategory,
                'currency'              => ['code' => \App\Models\Setting::get('currency', 'USD'), 'symbol' => '', 'position' => 'before'],
            ],
            'profitLossData' => [
                'net_profit' => $netProfit,
                'net_margin' => $netMargin,
            ],
            'dateRange' => ['start' => $start, 'end' => $end],
        ]);
    })->name('reports.revenue');

    // Analytics
    Route::get('/analytics', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $now  = now();

        $today        = $now->toDateString();
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd   = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();
        $calcGrowth     = fn($cur, $prev) => $prev > 0 ? round((($cur - $prev) / $prev) * 100, 1) : null;
        $growthText     = function ($pct) {
            if ($pct === null) return 'No prior data';
            return ($pct >= 0 ? '+' : '') . $pct . '% vs last month';
        };

        // ── Rooms & occupancy ─────────────────────────────────────────
        $totalRooms    = 0;
        $occupiedToday = 0;
        $lastMonthOcc  = 0.0;

        try {
            if (class_exists(\App\Models\Room::class)) {
                $totalRooms = \App\Models\Room::count();
            }
            if (class_exists(\App\Models\Reservation::class) && $totalRooms > 0) {
                $activeStatuses = ['cancelled', 'canceled'];
                // Today's occupancy
                $occupiedToday = (int) \App\Models\Reservation::query()
                    ->when(\Schema::hasColumn('reservations','check_in_date'), fn($q) => $q->where('check_in_date', '<=', $today)->where('check_out_date', '>', $today))
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', $activeStatuses))
                    ->distinct('room_id')->count('room_id');

                // Last month mid-point occupancy approximation
                $midLastMonth = $lastMonthStart->copy()->addDays(14)->toDateString();
                $lastMonthOccRooms = (int) \App\Models\Reservation::query()
                    ->when(\Schema::hasColumn('reservations','check_in_date'), fn($q) => $q->where('check_in_date', '<=', $midLastMonth)->where('check_out_date', '>', $midLastMonth))
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', $activeStatuses))
                    ->distinct('room_id')->count('room_id');
                $lastMonthOcc = round(($lastMonthOccRooms / $totalRooms) * 100, 1);
            }
        } catch (\Throwable $e) {}

        $occupancyRate    = $totalRooms > 0 ? round(($occupiedToday / $totalRooms) * 100, 1) : 0.0;
        $occupancyGrowth  = $calcGrowth($occupancyRate, $lastMonthOcc);

        // ── Revenue (Reservations + POS Sales) ───────────────────────
        $thisMonthRev = 0.0;
        $lastMonthRev = 0.0;
        $thisResRev   = 0.0;
        $lastResRev   = 0.0;
        $thisPosRev   = 0.0;
        $lastPosRev   = 0.0;

        try {
            if (class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'total_amount')) {
                $thisResRev = (float) \App\Models\Reservation::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->sum('total_amount');
                $lastResRev = (float) \App\Models\Reservation::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');
            }
            if (class_exists(\App\Models\Sale::class)) {
                $thisPosRev = (float) \App\Models\Sale::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->sum('total_amount');
                $lastPosRev = (float) \App\Models\Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');
            }
        } catch (\Throwable $e) {}

        $thisMonthRev  = $thisResRev + $thisPosRev;
        $lastMonthRev  = $lastResRev + $lastPosRev;
        $revenueGrowth = $calcGrowth($thisMonthRev, $lastMonthRev);

        // ── ADR: room revenue ÷ occupied room-nights this month ───────
        $thisAdr    = 0.0;
        $lastAdr    = 0.0;
        $adrGrowth  = null;

        try {
            if (class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'total_amount') && \Schema::hasColumn('reservations', 'check_in_date')) {
                // This month: sum nights × price
                $thisNights = (int) \App\Models\Reservation::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))
                    ->sum(\DB::raw('DATEDIFF(check_out_date, check_in_date)'));
                $thisAdr = $thisNights > 0 ? round($thisResRev / $thisNights, 2) : 0.0;

                $lastNights = (int) \App\Models\Reservation::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))
                    ->sum(\DB::raw('DATEDIFF(check_out_date, check_in_date)'));
                $lastAdr = $lastNights > 0 ? round($lastResRev / $lastNights, 2) : 0.0;
                $adrGrowth = $calcGrowth($thisAdr, $lastAdr);
            }
        } catch (\Throwable $e) {}

        // ── Top Performing Rooms ──────────────────────────────────────
        $topRooms = [];
        try {
            if (class_exists(\App\Models\Reservation::class) && class_exists(\App\Models\Room::class)
                && \Schema::hasColumn('reservations', 'room_id') && \Schema::hasColumn('reservations', 'total_amount')) {
                $topRooms = \App\Models\Reservation::selectRaw('room_id, COUNT(*) as stays, SUM(total_amount) as revenue')
                    ->whereNotNull('room_id')
                    ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))
                    ->groupBy('room_id')->orderByDesc('revenue')->limit(5)
                    ->get()->map(function ($r) use ($today) {
                        $room     = \App\Models\Room::find($r->room_id);
                        $occupied = (int) \App\Models\Reservation::where('room_id', $r->room_id)
                            ->where('check_in_date', '<=', $today)->where('check_out_date', '>', $today)
                            ->whereNotIn('status', ['cancelled','canceled'])->count();
                        return [
                            'id'        => $r->room_id,
                            'number'    => $room?->room_number ?? $room?->number ?? 'Room '.$r->room_id,
                            'type'      => $room?->type ?? $room?->roomType?->name ?? 'Standard',
                            'revenue'   => (float) $r->revenue,
                            'occupancy' => $occupied ? 100 : 0,
                        ];
                    })->toArray();
            } elseif (class_exists(\App\Models\Room::class)) {
                // Fallback: list rooms with stays count only
                $topRooms = \App\Models\Room::limit(5)->get()
                    ->map(fn($r) => [
                        'id' => $r->id, 'number' => $r->room_number ?? $r->number ?? $r->id,
                        'type' => $r->type ?? 'Standard', 'revenue' => 0.0, 'occupancy' => 0,
                    ])->toArray();
            }
        } catch (\Throwable $e) {}

        // ── Guest Demographics ────────────────────────────────────────
        $guestDemographics = [];
        try {
            // Option 1: Guest table with nationality
            if (class_exists(\App\Models\Guest::class) && \Schema::hasColumn('guests', 'nationality')) {
                $gTotal = \App\Models\Guest::count();
                if ($gTotal > 0) {
                    $guestDemographics = \App\Models\Guest::selectRaw('nationality, COUNT(*) as cnt')
                        ->whereNotNull('nationality')->where('nationality', '!=', '')
                        ->groupBy('nationality')->orderByDesc('cnt')->limit(6)->get()
                        ->map(fn($g) => ['category' => $g->nationality, 'percentage' => round(($g->cnt / $gTotal) * 100, 1)])->toArray();
                }
            }
            // Option 2: Reservation status breakdown
            if (empty($guestDemographics) && class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'status')) {
                $resTotal = \App\Models\Reservation::count();
                if ($resTotal > 0) {
                    $guestDemographics = \App\Models\Reservation::selectRaw('status, COUNT(*) as cnt')
                        ->whereNotNull('status')->groupBy('status')->orderByDesc('cnt')->limit(6)->get()
                        ->map(fn($r) => ['category' => ucfirst($r->status), 'percentage' => round(($r->cnt / $resTotal) * 100, 1)])->toArray();
                }
            }
            // Option 3: Customer count (new vs returning based on reservation count)
            if (empty($guestDemographics) && class_exists(\App\Models\Customer::class)) {
                $custTotal = \App\Models\Customer::count();
                if ($custTotal > 0) {
                    $guestDemographics = [['category' => 'Total Customers', 'percentage' => 100]];
                }
            }
        } catch (\Throwable $e) {}

        // ── Recent Activity ───────────────────────────────────────────
        $recentActivity = [];
        try {
            $activities = collect();
            if (class_exists(\App\Models\Reservation::class)) {
                \App\Models\Reservation::orderByDesc('created_at')->limit(5)
                    ->get(['id','reservation_code','status','created_at'])
                    ->each(fn($r) => $activities->push([
                        'id'          => 'res-'.$r->id,
                        'description' => 'Reservation '.($r->reservation_code ?? '#'.$r->id).' — '.ucfirst($r->status ?? 'created'),
                        'timestamp'   => $r->created_at,
                    ]));
            }
            if (class_exists(\App\Models\Sale::class)) {
                \App\Models\Sale::orderByDesc('created_at')->limit(5)
                    ->get(['id','sale_number','total_amount','created_at'])
                    ->each(fn($s) => $activities->push([
                        'id'          => 'sale-'.$s->id,
                        'description' => 'POS Sale '.($s->sale_number ?? '#'.$s->id).' — '.number_format((float)$s->total_amount, 2),
                        'timestamp'   => $s->created_at,
                    ]));
            }
            $recentActivity = $activities->sortByDesc('timestamp')->values()->take(8)->toArray();
        } catch (\Throwable $e) {}

        // ── Detailed Metrics Table ────────────────────────────────────
        $detailedMetrics = [];
        try {
            $thisResCount  = class_exists(\App\Models\Reservation::class) ? (int)\App\Models\Reservation::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count() : 0;
            $lastResCount  = class_exists(\App\Models\Reservation::class) ? (int)\App\Models\Reservation::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count() : 0;

            $detailedMetrics = [
                [
                    'name'     => 'Monthly Revenue',
                    'current'  => $thisMonthRev,
                    'previous' => $lastMonthRev,
                    'change'   => $calcGrowth($thisMonthRev, $lastMonthRev) ?? 0,
                    'trend'    => $thisMonthRev >= $lastMonthRev ? 'Increasing' : 'Decreasing',
                ],
                [
                    'name'     => 'Reservations',
                    'current'  => $thisResCount,
                    'previous' => $lastResCount,
                    'change'   => $calcGrowth($thisResCount, $lastResCount) ?? 0,
                    'trend'    => $thisResCount >= $lastResCount ? 'Increasing' : 'Decreasing',
                ],
                [
                    'name'     => 'Occupancy Rate (%)',
                    'current'  => $occupancyRate,
                    'previous' => $lastMonthOcc,
                    'change'   => $calcGrowth($occupancyRate, $lastMonthOcc) ?? 0,
                    'trend'    => $occupancyRate >= $lastMonthOcc ? 'Increasing' : 'Decreasing',
                ],
                [
                    'name'     => 'Avg Daily Rate (ADR)',
                    'current'  => $thisAdr,
                    'previous' => $lastAdr,
                    'change'   => $calcGrowth($thisAdr, $lastAdr) ?? 0,
                    'trend'    => $thisAdr >= $lastAdr ? 'Increasing' : 'Decreasing',
                ],
            ];
        } catch (\Throwable $e) {}

        // ── Chart Data ────────────────────────────────────────────────
        $charts = ['occupancy' => [], 'revenue' => []];
        try {
            for ($i = 6; $i >= 0; $i--) {
                $d   = $now->copy()->subDays($i)->toDateString();
                $occ = 0.0;
                if (class_exists(\App\Models\Reservation::class) && $totalRooms > 0) {
                    $cnt = (int) \App\Models\Reservation::where('check_in_date', '<=', $d)->where('check_out_date', '>', $d)
                        ->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status',['cancelled','canceled']))
                        ->distinct('room_id')->count('room_id');
                    $occ = round(($cnt / $totalRooms) * 100, 1);
                }
                $charts['occupancy'][] = ['date' => $d, 'rate' => $occ];
            }

            $charts['revenue'] = array_values(array_filter([
                $thisResRev > 0 ? ['category' => 'Room Revenue', 'amount' => $thisResRev] : null,
                $thisPosRev > 0 ? ['category' => 'POS / F&B',   'amount' => $thisPosRev] : null,
            ]));
            if (empty($charts['revenue'])) {
                $charts['revenue'] = [['category' => 'No revenue this month', 'amount' => 0]];
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Admin/Analytics/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'analytics'  => [
                'occupancyRate'     => $occupancyRate,
                'occupancyGrowth'   => $occupancyGrowth,
                'occupancyGrowthText' => $growthText($occupancyGrowth),
                'revenue'           => $thisMonthRev,
                'revenueGrowth'     => $revenueGrowth,
                'revenueGrowthText' => $growthText($revenueGrowth),
                'guestSatisfaction' => 'N/A',
                'averageDailyRate'  => $thisAdr,
                'adrGrowth'         => $adrGrowth,
                'adrGrowthText'     => $growthText($adrGrowth),
            ],
            'topRooms'          => $topRooms,
            'guestDemographics' => $guestDemographics,
            'recentActivity'    => $recentActivity,
            'detailedMetrics'   => $detailedMetrics,
            'charts'            => $charts,
        ]);
    })->name('analytics.index');

    // Users Export
    Route::get('/users/export', function (Request $request) {
        try {
            $format = $request->get('format', 'excel');

            // Get users with their roles
            $users = \App\Models\User::with('roles')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'employee_id' => $user->employee_id ?? 'N/A',
                    'department' => $user->department ?? 'N/A',
                    'position' => $user->position ?? 'N/A',
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'role' => $user->roles->first()?->name ?? 'No Role',
                    'hire_date' => $user->hire_date ? $user->hire_date->format('Y-m-d') : 'N/A',
                    'created_at' => $user->created_at->format('Y-m-d H:i:s')
                ];
            });

            $filename = 'users_export_' . date('Y_m_d_His');

            switch ($format) {
                case 'excel':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo \App\Helpers\ExportHelper::generateUsersExcelExport($users, $filename);
                    }, $filename . '.html', [
                        'Content-Type' => 'text/html',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.html"'
                    ]);

                case 'pdf':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo \App\Helpers\ExportHelper::generateUsersPDFExport($users, $filename);
                    }, $filename . '.pdf', [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"'
                    ]);

                case 'word':
                    return response()->streamDownload(function () use ($users, $filename) {
                        echo \App\Helpers\ExportHelper::generateUsersWordExport($users, $filename);
                    }, $filename . '.doc', [
                        'Content-Type' => 'application/msword',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.doc"'
                    ]);

                default:
                    return response()->json(['error' => 'Unsupported export format'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export users'], 500);
        }
    })->name('users.export');

    // Helper functions have been moved to App\Helpers\ExportHelper
    // This prevents function redeclaration errors when route caching is enabled

    // ── Budget ────────────────────────────────────────────────────────────────
    Route::get('/budget', [\App\Http\Controllers\Admin\BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/dashboard', [\App\Http\Controllers\Admin\BudgetController::class, 'dashboard'])->name('budget.dashboard');
    Route::get('/budget/create', [\App\Http\Controllers\Admin\BudgetController::class, 'create'])->name('budget.create');
    Route::post('/budget', [\App\Http\Controllers\Admin\BudgetController::class, 'store'])->name('budget.store');
    Route::get('/budget/reports', [\App\Http\Controllers\Admin\BudgetController::class, 'reports'])->name('budget.reports');
    Route::get('/budget/alerts', [\App\Http\Controllers\Admin\BudgetController::class, 'alerts'])->name('budget.alerts');
    Route::get('/budget/archived', [\App\Http\Controllers\Admin\BudgetController::class, 'archived'])->name('budget.archived');
    Route::get('/budget/expenses', [\App\Http\Controllers\Admin\BudgetController::class, 'expenses'])->name('budget.expenses.index');
    Route::get('/budget/expenses/create', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'create'])->name('budget.expenses.create');
    Route::post('/budget/expenses', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'store'])->name('budget.expenses.store');
    Route::get('/budget/expenses/pending-approvals', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'pendingApprovals'])->name('budget.expenses.pending-approvals');
    Route::get('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'show'])->name('budget.show');
    Route::get('/budget/{budget}/edit', [\App\Http\Controllers\Admin\BudgetController::class, 'edit'])->name('budget.edit');
    Route::put('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'update'])->name('budget.update');
    Route::delete('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::post('/budget/{budget}/approve', [\App\Http\Controllers\Admin\BudgetController::class, 'approve'])->name('budget.approve');
    Route::post('/budget/{budget}/reject', [\App\Http\Controllers\Admin\BudgetController::class, 'reject'])->name('budget.reject');
    Route::post('/budget/{budget}/archive', [\App\Http\Controllers\Admin\BudgetController::class, 'archive'])->name('budget.archive');
    Route::post('/budget/export', [\App\Http\Controllers\Admin\BudgetController::class, 'export'])->name('budget.export');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\Admin\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{folio}/edit', [\App\Http\Controllers\Admin\InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{folio}', [\App\Http\Controllers\Admin\InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{folio}', [\App\Http\Controllers\Admin\InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('/invoices/{folio}/mark-paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::get('/invoices/overdue', [\App\Http\Controllers\Admin\InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::get('/invoices/paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::post('/invoices/send-reminders', [\App\Http\Controllers\Admin\InvoiceController::class, 'sendReminders'])->name('invoices.sendReminders');

    // ── Quotes ───────────────────────────────────────────────────────────────
    Route::get('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [\App\Http\Controllers\Admin\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/quotes/{id}/edit', [\App\Http\Controllers\Admin\QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'destroy'])->name('quotes.destroy');
});

// Front Desk Routes
Route::middleware(['auth', 'role:front_desk'])->prefix('front-desk')->name('front-desk.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $today = today();

        // Get today's arrivals (confirmed or already checked in today)
        $arrivalsRaw = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_in_date', $today)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('check_in_date')
            ->get();

        // Get today's departures
        $departuresRaw = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', $today)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('check_out_date')
            ->get();

        // Get today's activities for front desk
        $todaysActivities = [
            'arrivals' => $arrivalsRaw->count(),
            'departures' => $departuresRaw->count(),
            'currentGuests' => \App\Models\Reservation::where('status', 'checked_in')->count(),
            'availableRooms' => \App\Models\Room::where('status', 'available')->count(),
        ];

        // Map arrivals to the format expected by the Vue component
        $arrivals = $arrivalsRaw->map(function ($r) {
            $expectedArrival = $r->preferred_check_in_time
                ? $r->check_in_date->format('Y-m-d') . 'T' . $r->preferred_check_in_time
                : $r->check_in_date->format('Y-m-d') . 'T14:00:00';
            return [
                'id' => $r->id,
                'guest_name' => $r->guest?->full_name ?? ($r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) : 'Guest'),
                'room_number' => $r->room?->room_number ?? 'TBD',
                'room_type' => $r->roomType?->name ?? 'N/A',
                'expected_arrival' => $expectedArrival,
                'checked_in' => in_array($r->status, ['checked_in', 'checked_out']) || !empty($r->actual_check_in),
            ];
        });

        // Map departures to the format expected by the Vue component
        $departures = $departuresRaw->map(function ($r) {
            $expectedDeparture = $r->preferred_check_out_time
                ? $r->check_out_date->format('Y-m-d') . 'T' . $r->preferred_check_out_time
                : $r->check_out_date->format('Y-m-d') . 'T11:00:00';
            return [
                'id' => $r->id,
                'guest_name' => $r->guest?->full_name ?? ($r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) : 'Guest'),
                'room_number' => $r->room?->room_number ?? 'TBD',
                'room_type' => $r->roomType?->name ?? 'N/A',
                'expected_departure' => $expectedDeparture,
                'checked_out' => in_array($r->status, ['checked_out']) || !empty($r->actual_check_out),
            ];
        });

        // Get room status
        $roomStatus = [
            'total' => \App\Models\Room::count(),
            'available' => \App\Models\Room::where('status', 'available')->count(),
            'occupied' => \App\Models\Room::where('status', 'occupied')->count(),
            'maintenance' => \App\Models\Room::where('status', 'maintenance')->count(),
            'cleaning' => \App\Models\Room::where('housekeeping_status', 'dirty')->count(),
        ];

        // Get guest requests (mock data for now)
        $guestRequests = [];

        return Inertia::render('FrontDesk/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'todaysActivities' => $todaysActivities,
            'arrivals' => $arrivals,
            'departures' => $departures,
            'roomStatus' => $roomStatus,
            'guestRequests' => $guestRequests,
        ]);
    })->name('dashboard');

    // Customers
    Route::get('/customers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $stats = [
            'total'   => \App\Models\Guest::count(),
            'active'  => \App\Models\Reservation::where('status', 'checked_in')
                            ->distinct('guest_id')->count('guest_id'),
            'pending' => \App\Models\Reservation::whereDate('check_in_date', today())
                            ->whereNotIn('status', ['cancelled', 'no_show', 'checked_in', 'checked_out'])
                            ->count(),
        ];

        return Inertia::render('FrontDesk/Customers/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats'      => $stats,
        ]);
    })->name('customers.index');

    // Customer Groups
    Route::get('/customer-groups', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $customerGroups = \App\Models\CustomerGroup::withCount('customers')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'          => \App\Models\CustomerGroup::count(),
            'active'         => \App\Models\CustomerGroup::where('is_active', true)->count(),
            'inactive'       => \App\Models\CustomerGroup::where('is_active', false)->count(),
            'totalCustomers' => \App\Models\Customer::count(),
        ];

        return Inertia::render('FrontDesk/CustomerGroups/Index', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroups' => $customerGroups,
            'stats'          => $stats,
        ]);
    })->name('customer-groups.index');

    // Reservations
    Route::get('/reservations', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $reservations = \App\Models\Reservation::with(['guest', 'room.roomType'])
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id'                  => $r->id,
                    'confirmation_number' => $r->reservation_number,
                    'guest_name'          => $r->guest
                        ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) ?: ($r->guest->full_name ?? 'N/A')
                        : 'N/A',
                    'guest_email'         => $r->guest?->email ?? '',
                    'check_in_date'       => $r->check_in_date,
                    'check_out_date'      => $r->check_out_date,
                    'room_number'         => $r->room?->room_number ?? null,
                    'room_type'           => $r->room?->roomType?->name ?? null,
                    'adults'              => $r->number_of_adults ?? $r->adults ?? 0,
                    'children'            => $r->number_of_children ?? $r->children ?? 0,
                    'status'              => $r->status,
                    'total_amount'        => $r->total_amount ?? 0,
                    'balance_amount'      => $r->balance_amount ?? 0,
                ];
            });

        $reservationStats = [
            'arrivals'        => \App\Models\Reservation::whereDate('check_in_date', today())->whereNotIn('status', ['cancelled', 'no_show'])->count(),
            'departures'      => \App\Models\Reservation::whereDate('check_out_date', today())->whereNotIn('status', ['cancelled', 'no_show'])->count(),
            'pendingCheckins' => \App\Models\Reservation::whereDate('check_in_date', today())->whereIn('status', ['confirmed', 'pending'])->count(),
            'checkedIn'       => \App\Models\Reservation::where('status', 'checked_in')->count(),
        ];

        return Inertia::render('FrontDesk/Reservations/Index', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'reservations'     => $reservations,
            'reservationStats' => $reservationStats,
        ]);
    })->name('reservations.index');

    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

    // Static sub-routes MUST come before wildcard /{id}
    Route::get('/reservations/arrivals', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $arrivals = \App\Models\Reservation::with(['guest', 'room'])
            ->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('check_in_date')
            ->get()
            ->map(fn($r) => [
                'id'                  => $r->id,
                'confirmation_number' => $r->reservation_number,
                'guest_name'          => $r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) : 'N/A',
                'guest_email'         => $r->guest?->email ?? '',
                'check_in_date'       => $r->check_in_date,
                'check_out_date'      => $r->check_out_date,
                'room_number'         => $r->room?->room_number ?? null,
                'total_amount'        => $r->total_amount,
                'status'              => $r->status,
            ]);

        $arrivalStats = [
            'total'     => $arrivals->count(),
            'confirmed' => $arrivals->where('status', 'confirmed')->count(),
            'pending'   => $arrivals->where('status', 'pending')->count(),
            'checkedIn' => 0,
        ];

        return Inertia::render('FrontDesk/Reservations/Arrivals', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'arrivals'     => $arrivals,
            'arrivalStats' => $arrivalStats,
        ]);
    })->name('reservations.arrivals');

    Route::get('/reservations/departures', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $departures = \App\Models\Reservation::with(['guest', 'room'])
            ->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->orderBy('check_out_date')
            ->get()
            ->map(fn($r) => [
                'id'                  => $r->id,
                'confirmation_number' => $r->reservation_number,
                'guest_name'          => $r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) : 'N/A',
                'guest_email'         => $r->guest?->email ?? '',
                'check_in_date'       => $r->check_in_date,
                'check_out_date'      => $r->check_out_date,
                'room_number'         => $r->room?->room_number ?? 'N/A',
                'total_amount'        => $r->total_amount,
                'balance'             => $r->balance_amount,
                'status'              => $r->status,
            ]);

        $departureStats = [
            'total'      => $departures->count(),
            'checkedOut' => 0,
            'pending'    => $departures->count(),
            'overdue'    => 0,
        ];

        return Inertia::render('FrontDesk/Reservations/Departures', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'departures'     => $departures,
            'departureStats' => $departureStats,
        ]);
    })->name('reservations.departures');

    Route::get('/reservations/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $r = \App\Models\Reservation::with([
            'guest', 'room', 'roomType', 'checkedInBy', 'checkedOutBy',
        ])->findOrFail($id);

        $checkIn  = \Carbon\Carbon::parse($r->check_in_date);
        $checkOut = \Carbon\Carbon::parse($r->check_out_date);

        $reservation = array_merge($r->toArray(), [
            'nights'   => $r->nights ?? max(1, $checkIn->diffInDays($checkOut)),
            'adults'   => $r->number_of_adults ?? 1,
            'children' => $r->number_of_children ?? 0,
            'room_type' => $r->roomType ? [
                'id'   => $r->roomType->id,
                'name' => $r->roomType->name,
            ] : null,
            'room' => $r->room ? [
                'id'          => $r->room->id,
                'room_number' => $r->room->room_number,
                'floor'       => $r->room->getAttributes()['floor'] ?? null,
            ] : null,
            'guest' => $r->guest ? array_merge($r->guest->toArray(), [
                'full_name' => trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? ''))
                               ?: ($r->guest->full_name ?? 'N/A'),
            ]) : null,
            'checked_in_by'  => $r->checkedInBy  ? ['name' => $r->checkedInBy->name  ?? trim(($r->checkedInBy->first_name  ?? '') . ' ' . ($r->checkedInBy->last_name  ?? ''))]  : null,
            'checked_out_by' => $r->checkedOutBy ? ['name' => $r->checkedOutBy->name ?? trim(($r->checkedOutBy->first_name ?? '') . ' ' . ($r->checkedOutBy->last_name ?? ''))] : null,
        ]);

        return Inertia::render('FrontDesk/Reservations/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'reservation' => $reservation,
        ]);
    })->name('reservations.show');

    Route::get('/reservations/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';
        $reservation = \App\Models\Reservation::with(['guest', 'room', 'roomType'])->findOrFail($id);
        $guests = \App\Models\Guest::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $roomTypes = \App\Models\RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'base_price']);
        $bookingSources = [
            'walk_in' => 'Walk-in', 'phone' => 'Phone', 'email' => 'Email',
            'website' => 'Website', 'booking_com' => 'Booking.com',
            'expedia' => 'Expedia', 'agoda' => 'Agoda',
            'travel_agent' => 'Travel Agent', 'corporate' => 'Corporate',
        ];
        return Inertia::render('FrontDesk/Reservations/Edit', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'reservation'    => $reservation,
            'guests'         => $guests,
            'roomTypes'      => $roomTypes,
            'bookingSources' => $bookingSources,
        ]);
    })->name('reservations.edit');

    Route::put('/reservations/{id}', function (\Illuminate\Http\Request $request, $id) {
        $reservation = \App\Models\Reservation::findOrFail($id);
        $validated = $request->validate([
            'guest_id'           => 'required|exists:guests,id',
            'room_type_id'       => 'nullable|exists:room_types,id',
            'check_in_date'      => 'required|date',
            'check_out_date'     => 'required|date|after:check_in_date',
            'number_of_adults'   => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'booking_source'     => 'nullable|string',
            'room_rate'          => 'nullable|numeric|min:0',
            'special_requests'   => 'nullable|string',
            'status'             => 'nullable|string',
        ]);
        $reservation->update($validated);
        return redirect()->route('front-desk.reservations.show', $reservation->id)
            ->with('success', 'Reservation updated successfully.');
    })->name('reservations.update');

    // Guests
    Route::get('/guests', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $checkedInGuestIds = \App\Models\Reservation::where('status', 'checked_in')
            ->whereNotNull('guest_id')
            ->pluck('guest_id')
            ->flip();

        $guests = \App\Models\Guest::latest()
            ->get()
            ->map(fn($g) => [
                'id'          => $g->id,
                'first_name'  => $g->first_name ?? '',
                'last_name'   => $g->last_name ?? '',
                'email'       => $g->email,
                'phone'       => $g->phone,
                'nationality' => $g->nationality,
                'is_vip'      => $g->is_vip ?? false,
                'guest_type'  => null,
                'status'      => isset($checkedInGuestIds[$g->id]) ? 'checked_in' : 'inactive',
            ]);

        return Inertia::render('FrontDesk/Guests/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guests'     => $guests,
        ]);
    })->name('guests.index');

    Route::get('/guests/current', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';
        $currentGuests = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->orderBy('check_in_date')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'guest_name'     => $r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) : 'N/A',
                'guest_email'    => $r->guest?->email,
                'room_number'    => $r->room?->room_number ?? 'N/A',
                'check_in_date'  => $r->check_in_date,
                'check_out_date' => $r->check_out_date,
                'status'         => $r->status,
            ]);
        return Inertia::render('FrontDesk/Guests/Current', [
            'user'          => $user,
            'navigation'    => app(DashboardController::class)->getNavigationForRole($role),
            'currentGuests' => $currentGuests,
        ]);
    })->name('guests.current');

    Route::get('/guests/history', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';
        $guests = \App\Models\Guest::latest()->paginate(20)->withQueryString();
        return Inertia::render('FrontDesk/Guests/History', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guests'     => $guests,
        ]);
    })->name('guests.history');

    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');

    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');

    Route::get('/guests/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('FrontDesk/Guests/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('guests.show');

    Route::get('/guests/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('FrontDesk/Guests/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('guests.edit');

    // Check-in/Check-out
    Route::get('/checkin', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        // Get today's arrivals for check-in
        $todaysArrivals = \App\Models\Reservation::with(['guest', 'room'])
            ->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('check_in_date')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest ? $reservation->guest->first_name . ' ' . $reservation->guest->last_name : 'Unknown',
                    'guest_email' => $reservation->guest ? $reservation->guest->email : '',
                    'guest_phone' => $reservation->guest ? $reservation->guest->phone : '',
                    'roomNumber' => $reservation->room ? $reservation->room->room_number : 'TBA',
                    'check_in_time' => $reservation->check_in_date,
                    'status' => $reservation->status,
                    'total_amount' => $reservation->total_amount,
                ];
            });

        // Get available rooms
        $availableRooms = \App\Models\Room::where('status', 'available')
            ->with('roomType')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->roomType ? $room->roomType->name : 'Standard',
                    'floor' => $room->floor ?? 1,
                ];
            });

        return Inertia::render('FrontDesk/CheckIn', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'todaysArrivals' => $todaysArrivals,
            'availableRooms' => $availableRooms,
        ]);
    })->name('checkin');

    Route::post('/checkin', [\App\Http\Controllers\FrontDesk\CheckInController::class, 'store'])->name('checkin.store');

    Route::get('/checkout', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $mapReservation = function ($r) {
            $checkIn  = \Carbon\Carbon::parse($r->check_in_date);
            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
            $nights   = max(1, $checkIn->diffInDays($checkOut));
            return [
                'id'            => $r->id,
                'guestName'     => $r->guest?->full_name ?? ($r->guest?->first_name . ' ' . $r->guest?->last_name),
                'roomNumber'    => $r->room?->room_number ?? 'N/A',
                'roomType'      => $r->room?->roomType?->name ?? 'Standard',
                'checkInDate'   => $r->check_in_date,
                'checkOutDate'  => $r->check_out_date,
                'departureTime' => $r->check_out_date,
                'nights'        => $nights,
                'totalAmount'   => $r->total_amount ?? 0,
                'balanceAmount' => $r->balance_amount ?? 0,
                'status'        => $r->status,
                'guest'         => $r->guest,
                'room'          => $r->room,
            ];
        };

        $todaysDepartures = \App\Models\Reservation::with(['guest', 'room.roomType'])
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->orderBy('check_out_date')
            ->get()
            ->map($mapReservation)
            ->values()
            ->all();

        $allCheckedIn = \App\Models\Reservation::with(['guest', 'room.roomType'])
            ->where('status', 'checked_in')
            ->orderBy('check_out_date')
            ->get()
            ->map($mapReservation)
            ->values()
            ->all();

        return Inertia::render('FrontDesk/CheckOut', [
            'user'                  => $user,
            'navigation'            => app(DashboardController::class)->getNavigationForRole($role),
            'todaysDepartures'      => $todaysDepartures,
            'allCheckedIn'          => $allCheckedIn,
            'selectedReservationId' => $request->query('reservation_id'),
        ]);
    })->name('checkout');

    Route::post('/checkout', [\App\Http\Controllers\FrontDesk\CheckOutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/print', [\App\Http\Controllers\FrontDesk\CheckOutController::class, 'printReceipt'])->name('checkout.print');

    // Rooms
    Route::get('/rooms', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $rooms = \App\Models\Room::with([
                'roomType',
                'floorRelation',
                'bedType',
                'currentReservation.guest',
                'pendingReservations.guest',
            ])
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                $currentReservation = $room->currentReservation;
                $pendingReservation = null;
                if ($room->status === 'available') {
                    $pendingReservation = \App\Models\Reservation::where('room_id', $room->id)
                        ->whereIn('status', ['confirmed', 'pending'])
                        ->whereDate('check_in_date', '<=', now()->addDay())
                        ->whereDate('check_out_date', '>', now())
                        ->with('guest')
                        ->orderBy('check_in_date')
                        ->first();
                }
                $keyCard = null;
                if ($room->status === 'occupied' && $currentReservation) {
                    $keyCard = \App\Models\KeyCard::where('reservation_id', $currentReservation->id)
                        ->where('is_active', true)
                        ->first();
                }
                $nights = 0;
                $totalAmount = 0;
                $balance = 0;
                $totalRoomCharges = 0;
                if ($currentReservation) {
                    $checkIn = \Carbon\Carbon::parse($currentReservation->check_in_date);
                    $checkOut = \Carbon\Carbon::parse($currentReservation->check_out_date);
                    $nights = $checkIn->diffInDays($checkOut);
                    $totalAmount = $currentReservation->total_amount ?? ($currentReservation->room_rate * $nights);
                    $totalRoomCharges = $totalAmount;
                    $paymentsMade = $currentReservation->paid_amount ?? 0;
                    $balance = max(0, $totalAmount - $paymentsMade);
                }
                $amenities = [];
                if ($room->roomType && $room->roomType->amenities) {
                    $amenities = collect($room->roomType->amenities)->pluck('name')->toArray();
                }
                $housekeepingStatus = $room->housekeeping_status;
                if (!$housekeepingStatus) {
                    $housekeepingStatus = $room->status === 'occupied' ? 'occupied' : ($room->status === 'available' ? 'clean' : null);
                }
                return [
                    'id'                  => $room->id,
                    'number'              => $room->room_number,
                    'status'              => $room->status,
                    'type'                => $room->roomType ? $room->roomType->name : 'Standard',
                    'floor'               => $room->floorRelation ? 'Floor ' . $room->floorRelation->name : ($room->floor ? 'Floor ' . $room->floor : 'Ground Floor'),
                    'guest'               => $currentReservation && $currentReservation->guest
                        ? trim($currentReservation->guest->first_name . ' ' . $currentReservation->guest->last_name)
                        : null,
                    'guest_phone'         => $currentReservation?->guest?->phone ?? null,
                    'guest_email'         => $currentReservation?->guest?->email ?? null,
                    'check_in'            => $currentReservation ? $currentReservation->check_in_date : null,
                    'check_out'           => $currentReservation ? $currentReservation->check_out_date : null,
                    'reservation_id'      => $currentReservation ? $currentReservation->id : null,
                    'nights'              => $nights,
                    'room_rate'           => $currentReservation ? $currentReservation->room_rate : null,
                    'total_amount'        => $totalAmount > 0 ? $totalAmount : null,
                    'total_room_charges'  => $totalRoomCharges > 0 ? $totalRoomCharges : null,
                    'balance'             => $balance,
                    'key_card'            => $keyCard ? ['card_number' => $keyCard->card_number, 'card_type' => $keyCard->card_type ?? 'Standard'] : null,
                    'pending_reservation' => $pendingReservation ? [
                        'id'                 => $pendingReservation->id,
                        'guest_name'         => trim($pendingReservation->guest->first_name . ' ' . $pendingReservation->guest->last_name),
                        'reservation_number' => $pendingReservation->id,
                        'check_in_date'      => $pendingReservation->check_in_date,
                        'check_out_date'     => $pendingReservation->check_out_date,
                    ] : null,
                    'amenities'           => $amenities,
                    'capacity'            => $room->roomType ? ($room->roomType->capacity ?? $room->roomType->max_occupancy ?? 2) : 2,
                    'bed_type'            => $room->bedType ? $room->bedType->name : 'Standard',
                    'view_type'           => $room->roomType ? ($room->roomType->view_type ?? 'Standard') : 'Standard',
                    'price'               => $room->roomType ? ($room->roomType->base_rate ?? $room->roomType->base_price ?? 0) : 0,
                    'last_cleaned'        => $room->last_cleaned_at ? $room->last_cleaned_at->format('Y-m-d H:i:s') : null,
                    'housekeeping_status' => $housekeepingStatus,
                ];
            });

        $roomStatus = [
            'available'   => $rooms->where('status', 'available')->count(),
            'occupied'    => $rooms->whereIn('status', ['occupied', 'checked_in'])->count(),
            'cleaning'    => $rooms->where('status', 'cleaning')->count(),
            'maintenance' => $rooms->where('status', 'maintenance')->count(),
        ];

        $availableKeyCards = \App\Models\KeyCard::where('is_active', false)
            ->whereNull('reservation_id')
            ->orderBy('card_number')
            ->get(['id', 'card_number', 'card_type']);

        return Inertia::render('FrontDesk/Rooms/Index', [
            'user'              => $user,
            'navigation'        => app(DashboardController::class)->getNavigationForRole($role),
            'rooms'             => $rooms->values(),
            'roomStatus'        => $roomStatus,
            'availableKeyCards' => $availableKeyCards,
        ]);
    })->name('rooms.index');

    Route::get('/room-assignment', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $pendingReservations = \App\Models\Reservation::with(['guest', 'roomType'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNull('room_id')
            ->orderBy('check_in_date')
            ->get()
            ->map(fn($r) => [
                'id'                 => $r->id,
                'reservation_number' => $r->reservation_number,
                'guest_name'         => $r->guest
                    ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? ''))
                    : 'N/A',
                'guest'              => $r->guest ? [
                    'full_name' => trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')),
                    'email'     => $r->guest->email ?? null,
                    'phone'     => $r->guest->phone ?? null,
                ] : null,
                'check_in_date'      => $r->check_in_date,
                'check_out_date'     => $r->check_out_date,
                'nights'             => $r->nights ?? (isset($r->check_in_date, $r->check_out_date) ? \Carbon\Carbon::parse($r->check_in_date)->diffInDays(\Carbon\Carbon::parse($r->check_out_date)) : 1),
                'room_type_id'       => $r->room_type_id,
                'room_type'          => $r->roomType ? ['id' => $r->roomType->id, 'name' => $r->roomType->name] : null,
                'number_of_adults'   => $r->number_of_adults ?? 1,
                'number_of_children' => $r->number_of_children ?? 0,
                'total_amount'       => $r->total_amount ?? 0,
                'status'             => $r->status,
            ]);

        $availableRooms = \App\Models\Room::with(['roomType', 'floorRelation'])
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->orderBy('room_number')
            ->get()
            ->map(fn($room) => [
                'id'           => $room->id,
                'room_number'  => $room->room_number,
                'room_type_id' => $room->room_type_id,
                'room_type'    => $room->roomType ? ['id' => $room->roomType->id, 'name' => $room->roomType->name] : null,
                'floor_name'   => $room->floorRelation?->name ?? ($room->getAttributes()['floor'] ?? null),
                'building'     => $room->building ?? null,
                'wing'         => $room->wing ?? null,
                'status'       => $room->status,
            ]);

        $totalRooms    = \App\Models\Room::count();
        $occupiedRooms = \App\Models\Room::where('status', 'occupied')->count();
        $stats = [
            'pending'       => $pendingReservations->count(),
            'available'     => $availableRooms->count(),
            'assigned'      => \App\Models\Reservation::whereIn('status', ['confirmed', 'pending'])->whereNotNull('room_id')->count(),
            'totalRooms'    => $totalRooms,
            'todayCheckins' => \App\Models\Reservation::whereDate('check_in_date', today())->whereIn('status', ['confirmed', 'pending'])->count(),
            'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0,
            'cleanRooms'    => \App\Models\Room::where('housekeeping_status', 'clean')->count(),
            'dirtyRooms'    => \App\Models\Room::where('housekeeping_status', 'dirty')->count(),
        ];

        return Inertia::render('FrontDesk/RoomAssignment', [
            'user'                => $user,
            'navigation'          => app(DashboardController::class)->getNavigationForRole($role),
            'pendingReservations' => $pendingReservations,
            'availableRooms'      => $availableRooms,
            'stats'               => $stats,
        ]);
    })->name('room-assignment');

    Route::post('/room-assignment/assign', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_id'        => 'required|exists:rooms,id',
        ]);

        $reservation = \App\Models\Reservation::findOrFail($data['reservation_id']);
        $room        = \App\Models\Room::findOrFail($data['room_id']);

        $reservation->update(['room_id' => $room->id]);
        $room->update(['status' => 'reserved']);

        return redirect()->route('front-desk.room-assignment')->with('success', 'Room ' . $room->room_number . ' assigned successfully.');
    })->name('room-assignment.assign');

    Route::post('/rooms/{id}/manual-checkout', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);
            if ($room->status !== 'occupied') {
                return back()->withErrors(['message' => 'Room is not currently occupied.']);
            }
            $room->status = 'cleaning';
            $room->housekeeping_status = 'dirty';
            $room->save();
            return back()->with('success', "Room {$room->room_number} checked out successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to check out room: ' . $e->getMessage()]);
        }
    })->name('rooms.manual-checkout');

    Route::post('/rooms/{id}/mark-clean', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);
            $updateData = [
                'housekeeping_status' => 'clean',
                'last_cleaned_at' => now(),
                'last_cleaned_by' => auth()->id(),
            ];
            if ($room->status !== 'occupied') {
                $updateData['status'] = 'available';
            }
            $room->update($updateData);
            $statusMessage = $room->status === 'occupied'
                ? "Room {$room->room_number} marked as clean (still occupied)."
                : "Room {$room->room_number} marked as clean and available.";
            return back()->with('success', $statusMessage);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to mark room as clean: ' . $e->getMessage()]);
        }
    })->name('rooms.mark-clean');

    // Front-desk mark-dirty route
    Route::post('/rooms/{id}/mark-dirty', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);
            $updateData = ['housekeeping_status' => 'dirty'];
            if ($room->status !== 'occupied') {
                $updateData['status'] = 'cleaning';
            }
            $room->update($updateData);
            return back()->with('success', "Room {$room->room_number} marked as dirty. Housekeeping will be notified.");
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to mark room as dirty: ' . $e->getMessage()]);
        }
    })->name('rooms.mark-dirty');

    // Payments
    Route::get('/payments/process', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        // Check if tax is configured in settings
        $taxRate = (float)(\App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0);
        $applyTax = $taxRate > 0;

        $currentGuests = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->orderBy('check_in_date', 'desc')
            ->get()
            ->map(function ($r) use ($applyTax) {
                $paid    = (float)($r->paid_amount ?? 0);
                // Use total_amount (with tax) if tax is configured, otherwise use total_room_charges (without tax)
                $total   = $applyTax
                    ? (float)($r->total_amount ?? 0)
                    : (float)($r->total_room_charges ?? $r->total_amount ?? 0);
                $balance = max(0, $total - $paid);
                return [
                    'id'        => $r->id,
                    'folio_id'  => null,
                    'name'      => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Unknown',
                    'email'     => $r->guest?->email ?? '',
                    'phone'     => $r->guest?->phone ?? '',
                    'room'      => $r->room?->room_number ?? 'N/A',
                    'checkOut'  => $r->check_out_date,
                    'total'     => $total,
                    'paid'      => $paid,
                    'balance'   => $balance,
                ];
            })
            ->values();

        $recentPayments = \App\Models\Payment::with('reservation.guest')
            ->where('processed_by', auth()->id())
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'     => $p->id,
                'guest'  => $p->reservation?->guest
                    ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'N/A',
                'amount' => (float)$p->amount,
                'method' => $p->payment_method,
                'status' => $p->status,
                'time'   => $p->processed_at?->format('Y-m-d H:i') ?? '',
            ]);

        // Get receipt data from session (set after payment processing)
        $paymentReceipt = session()->pull('payment_receipt');

        return Inertia::render('FrontDesk/Payments/Process', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'currentGuests'  => $currentGuests,
            'recentPayments' => $recentPayments,
            'paymentReceipt' => $paymentReceipt,
        ]);
    })->name('payments.process');

    Route::post('/payments/process', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'payment_method' => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'notes'          => 'nullable|string',
        ]);

        $reservation = \App\Models\Reservation::with(['guest', 'room'])->findOrFail($validated['reservation_id']);

        $paymentNumber = 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8));

        $payment = \App\Models\Payment::create([
            'payment_number'  => $paymentNumber,
            'reservation_id'  => $reservation->id,
            'payment_method'  => $validated['payment_method'],
            'amount'          => $validated['amount'],
            'local_amount'    => $validated['amount'],
            'currency'        => 'USD',
            'exchange_rate'   => 1,
            'status'          => 'completed',
            'processed_at'    => now(),
            'processed_by'    => auth()->id(),
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Update reservation paid/balance amounts
        $newPaid    = (float)($reservation->paid_amount ?? 0) + (float)$validated['amount'];
        $newBalance = max(0, (float)($reservation->total_amount ?? 0) - $newPaid);
        $reservation->update([
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
        ]);

        // Store receipt data in session for auto-print
        session(['payment_receipt' => [
            'payment_number' => $paymentNumber,
            'payment_id'     => $payment->id,
            'guest_name'     => $reservation->guest ? trim($reservation->guest->first_name . ' ' . $reservation->guest->last_name) : 'Guest',
            'room_number'    => $reservation->room?->room_number ?? 'N/A',
            'amount'         => (float)$validated['amount'],
            'payment_method' => $validated['payment_method'],
            'processed_at'   => now()->format('Y-m-d H:i:s'),
            'notes'          => $validated['notes'] ?? null,
        ]]);

        return redirect()->route('front-desk.payments.process')
            ->with('success', "Payment {$paymentNumber} processed successfully.");
    })->name('payments.store');

    // Payment History — only shows payments processed by this front desk user
    Route::get('/payments/history', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $payments = \App\Models\Payment::with(['reservation.guest', 'reservation.room'])
            ->where('processed_by', auth()->id())
            ->orderBy('processed_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('FrontDesk/Payments/History', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'payments'   => $payments,
        ]);
    })->name('payments.history');

    // Transactions — only shows transactions processed by this front desk user
    Route::get('/transactions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $userId = auth()->id();

        $transactions = \App\Models\Payment::with(['reservation.guest', 'reservation.room'])
            ->where('processed_by', $userId)
            ->orderBy('processed_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'source_id'      => $p->id,
                'transaction_id' => 'PAY-' . $p->id,
                'payment_number' => $p->payment_number,
                'guest_name'     => $p->reservation?->guest
                    ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'N/A',
                'room_number'    => $p->reservation?->room?->room_number ?? 'N/A',
                'reference'      => $p->reservation_id ? 'Reservation #' . $p->reservation_id : ($p->payment_number ?? 'N/A'),
                'type'           => 'payment',
                'amount'         => (float) $p->amount,
                'status'         => $p->status ?? 'completed',
                'payment_method' => $p->payment_method,
                'date'           => $p->processed_at ?? $p->created_at,
                'created_at'     => $p->created_at,
                'notes'          => $p->notes,
            ]);

        $stats = [
            'total_count'  => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'completed'    => $transactions->where('status', 'completed')->count(),
            'pending'      => $transactions->where('status', 'pending')->count(),
        ];

        return Inertia::render('FrontDesk/Transactions/Index', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'transactions' => $transactions,
            'stats'        => $stats,
        ]);
    })->name('transactions.index');

    // Key Cards
    Route::get('/key-cards', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $keyCards = \App\Models\KeyCard::with(['reservation.guest', 'reservation.room'])
            ->orderBy('card_number')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'      => \App\Models\KeyCard::count(),
            'available'  => \App\Models\KeyCard::where('status', 'available')->count(),
            'assigned'   => \App\Models\KeyCard::where('status', 'assigned')->count(),
            'lost'       => \App\Models\KeyCard::where('status', 'lost')->count(),
            'damaged'    => \App\Models\KeyCard::where('status', 'damaged')->count(),
        ];

        $availableKeyCards = \App\Models\KeyCard::where('status', 'available')
            ->orderBy('card_number')->get(['id', 'card_number', 'card_type']);

        $checkedInReservations = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'room_id'  => $r->room_id,
                'name'     => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Guest',
                'room'     => $r->room?->room_number ?? 'N/A',
            ]);

        $availableRooms = \App\Models\Room::where('status', 'occupied')
            ->orderBy('room_number')->get(['id', 'room_number']);

        return Inertia::render('FrontDesk/KeyCards/Index', [
            'user'                  => $user,
            'navigation'            => app(DashboardController::class)->getNavigationForRole($role),
            'keyCards'              => $keyCards,
            'stats'                 => $stats,
            'availableKeyCards'     => $availableKeyCards,
            'checkedInReservations' => $checkedInReservations,
            'availableRooms'        => $availableRooms,
        ]);
    })->name('key-cards.index');

    Route::get('/key-cards/assignment', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $availableKeyCards = \App\Models\KeyCard::where('status', 'available')
            ->orderBy('card_number')->get(['id', 'card_number', 'card_type']);

        $checkedInReservations = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'room_id' => $r->room_id,
                'name'    => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Guest',
                'room'    => $r->room?->room_number ?? 'N/A',
            ]);

        $availableRooms = \App\Models\Room::where('status', 'occupied')
            ->orderBy('room_number')->get(['id', 'room_number']);

        return Inertia::render('FrontDesk/KeyCards/Assignment', [
            'user'                  => $user,
            'navigation'            => app(DashboardController::class)->getNavigationForRole($role),
            'availableKeyCards'     => $availableKeyCards,
            'checkedInReservations' => $checkedInReservations,
            'availableRooms'        => $availableRooms,
        ]);
    })->name('key-cards.assignment');

    Route::get('/key-cards/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';
        return Inertia::render('FrontDesk/KeyCards/Create', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('key-cards.create');

    Route::post('/key-cards', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'card_number' => 'required|string|unique:key_cards,card_number',
            'card_type'   => 'required|string',
            'notes'       => 'nullable|string',
        ]);
        \App\Models\KeyCard::create([
            'card_number' => $validated['card_number'],
            'card_type'   => $validated['card_type'],
            'status'      => 'available',
            'is_active'   => true,
            'notes'       => $validated['notes'] ?? null,
        ]);
        return redirect()->route('front-desk.key-cards.index')->with('success', 'Key card created successfully.');
    })->name('key-cards.store');

    Route::get('/key-cards/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';
        $keyCard = \App\Models\KeyCard::with(['reservation.guest', 'reservation.room'])->findOrFail($id);
        return Inertia::render('FrontDesk/KeyCards/Show', [
            'user'              => $user,
            'navigation'        => app(DashboardController::class)->getNavigationForRole($role),
            'keyCard'           => $keyCard,
            'assignmentHistory' => [],
        ]);
    })->name('key-cards.show');

    Route::get('/key-cards/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        return Inertia::render('FrontDesk/KeyCards/Edit', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'keyCard'    => $keyCard,
        ]);
    })->name('key-cards.edit');

    Route::put('/key-cards/{id}', function (\Illuminate\Http\Request $request, $id) {
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        $validated = $request->validate([
            'card_number' => 'required|string|unique:key_cards,card_number,' . $id,
            'card_type'   => 'required|string',
            'status'      => 'required|string',
            'is_active'   => 'boolean',
            'notes'       => 'nullable|string',
        ]);
        $keyCard->update($validated);
        return redirect()->route('front-desk.key-cards.show', $id)->with('success', 'Key card updated successfully.');
    })->name('key-cards.update');

    Route::post('/key-cards/assign', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'key_card_id'    => 'required|exists:key_cards,id',
            'reservation_id' => 'required|exists:reservations,id',
            'room_id'        => 'nullable|exists:rooms,id',
        ]);
        $keyCard = \App\Models\KeyCard::findOrFail($validated['key_card_id']);
        $keyCard->update([
            'status'         => 'assigned',
            'reservation_id' => $validated['reservation_id'],
            'is_active'      => true,
        ]);
        return redirect()->route('front-desk.key-cards.index')->with('success', 'Key card assigned successfully.');
    })->name('key-cards.assign');

    Route::post('/key-cards/{id}/return', function ($id) {
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        $keyCard->update(['status' => 'available', 'reservation_id' => null]);
        return back()->with('success', 'Key card returned successfully.');
    })->name('key-cards.return');

    Route::post('/key-cards/{id}/mark-lost', function ($id) {
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        $keyCard->update(['status' => 'lost', 'reservation_id' => null]);
        return back()->with('success', 'Key card marked as lost.');
    })->name('key-cards.mark-lost');

    Route::post('/key-cards/{id}/mark-damaged', function ($id) {
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        $keyCard->update(['status' => 'damaged', 'reservation_id' => null]);
        return back()->with('success', 'Key card marked as damaged.');
    })->name('key-cards.mark-damaged');

    Route::post('/key-cards/{id}/deactivate', function ($id) {
        $keyCard = \App\Models\KeyCard::findOrFail($id);
        $keyCard->update(['status' => 'deactivated', 'is_active' => false, 'reservation_id' => null]);
        return back()->with('success', 'Key card deactivated.');
    })->name('key-cards.deactivate');

    Route::post('/key-cards/bulk-return', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate(['card_ids' => 'required|array']);
        \App\Models\KeyCard::whereIn('id', $validated['card_ids'])
            ->update(['status' => 'available', 'reservation_id' => null]);
        return back()->with('success', count($validated['card_ids']) . ' key cards returned successfully.');
    })->name('key-cards.bulk-return');

    // Services
    Route::get('/services/concierge', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $requests = \App\Models\ConciergeRequest::orderBy('requested_at', 'desc')
            ->paginate(20)->withQueryString();

        $stats = [
            'pending'     => \App\Models\ConciergeRequest::where('status', 'pending')->count(),
            'in_progress' => \App\Models\ConciergeRequest::where('status', 'in_progress')->count(),
            'completed'   => \App\Models\ConciergeRequest::where('status', 'completed')->count(),
            'total'       => \App\Models\ConciergeRequest::whereDate('created_at', today())->count(),
        ];

        // Hall bookings data for front desk
        $hallBookings = \App\Models\HallBooking::with(['hall', 'guest'])
            ->orderBy('event_date', 'desc')
            ->get()
            ->map(fn($b) => [
                'id'             => $b->id,
                'booking_number' => $b->booking_number,
                'hall_name'      => $b->hall?->name ?? 'N/A',
                'contact_name'   => $b->contact_name ?? ($b->guest ? trim($b->guest->first_name . ' ' . $b->guest->last_name) : 'N/A'),
                'event_date'     => $b->event_date?->format('Y-m-d'),
                'start_time'     => $b->start_time,
                'end_time'       => $b->end_time,
                'attendees'      => $b->attendees,
                'total_amount'   => (float) $b->total_amount,
                'paid_amount'    => (float) $b->paid_amount,
                'status'         => $b->status,
                'notes'          => $b->notes,
            ]);

        $hallBookingStats = [
            'total'     => \App\Models\HallBooking::count(),
            'pending'   => \App\Models\HallBooking::where('status', 'pending')->count(),
            'confirmed' => \App\Models\HallBooking::where('status', 'confirmed')->count(),
            'completed' => \App\Models\HallBooking::where('status', 'completed')->count(),
        ];

        // Available halls for creating new hall bookings
        $halls = \App\Models\Hall::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'capacity', 'base_price', 'type']);

        // Checked-in guests for guest lookup in forms
        $currentGuests = \App\Models\Reservation::with('guest')
            ->where('status', 'checked_in')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->guest_id,
                'name'        => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Unknown',
                'room_number' => $r->room?->room_number ?? 'N/A',
            ]);

        return Inertia::render('FrontDesk/Services/Concierge', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'requests'         => $requests,
            'stats'            => $stats,
            'hallBookings'     => $hallBookings,
            'hallBookingStats' => $hallBookingStats,
            'halls'            => $halls,
            'currentGuests'    => $currentGuests,
        ]);
    })->name('services.concierge');

    Route::post('/services/concierge', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'guest_name'   => 'required|string|max:255',
            'room_number'  => 'nullable|string|max:50',
            'service_type' => 'required|string|max:100',
            'details'      => 'nullable|string',
        ]);
        \App\Models\ConciergeRequest::create([
            'request_number' => 'CON-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'guest_name'     => $validated['guest_name'],
            'room_number'    => $validated['room_number'] ?? null,
            'service_type'   => $validated['service_type'],
            'details'        => $validated['details'] ?? null,
            'status'         => 'pending',
            'requested_at'   => now(),
            'created_by'     => auth()->id(),
        ]);
        return back()->with('success', 'Concierge request created successfully.');
    })->name('services.concierge.store');

    Route::post('/services/concierge/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $concierge = \App\Models\ConciergeRequest::findOrFail($id);
        $concierge->update(['status' => $request->input('status', 'in_progress')]);
        return back()->with('success', 'Concierge request status updated.');
    })->name('services.concierge.update-status');

    // Hall Bookings (front desk can create and view)
    Route::get('/services/hall-bookings', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $bookings = \App\Models\HallBooking::with(['hall', 'guest'])
            ->orderByDesc('event_date')
            ->paginate(20)->withQueryString();

        $stats = [
            'total'     => \App\Models\HallBooking::count(),
            'pending'   => \App\Models\HallBooking::where('status', 'pending')->count(),
            'confirmed' => \App\Models\HallBooking::where('status', 'confirmed')->count(),
            'completed' => \App\Models\HallBooking::where('status', 'completed')->count(),
            'cancelled' => \App\Models\HallBooking::where('status', 'cancelled')->count(),
        ];

        $halls = \App\Models\Hall::where('is_active', true)->orderBy('name')->get(['id', 'name', 'capacity', 'base_price']);

        $guests = \App\Models\Guest::orderBy('first_name')->get()
            ->map(fn($g) => ['id' => $g->id, 'name' => trim($g->first_name . ' ' . $g->last_name), 'email' => $g->email]);

        return Inertia::render('FrontDesk/Services/HallBookings', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'bookings'   => $bookings,
            'stats'      => $stats,
            'halls'      => $halls,
            'guests'     => $guests,
        ]);
    })->name('services.hall-bookings.index');

    Route::post('/services/hall-bookings', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'hall_id'      => 'required|exists:halls,id',
            'contact_name' => 'required|string|max:255',
            'contact_email'=> 'nullable|email|max:255',
            'contact_phone'=> 'nullable|string|max:50',
            'event_date'   => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'attendees'    => 'required|integer|min:1',
            'notes'        => 'nullable|string',
            'guest_id'     => 'nullable|exists:guests,id',
        ]);

        $hall  = \App\Models\Hall::findOrFail($validated['hall_id']);
        $start = \Carbon\Carbon::createFromTimeString($validated['start_time']);
        $end   = \Carbon\Carbon::createFromTimeString($validated['end_time']);
        $hours = max(0.5, $start->diffInMinutes($end) / 60);
        $total = round((float) $hall->base_price * $hours, 2);

        \App\Models\HallBooking::create([
            'booking_number' => 'HB-' . now()->format('Ymd') . '-' . random_int(1000, 9999),
            'hall_id'        => $validated['hall_id'],
            'guest_id'       => $validated['guest_id'] ?? null,
            'contact_name'   => $validated['contact_name'],
            'contact_email'  => $validated['contact_email'] ?? null,
            'contact_phone'  => $validated['contact_phone'] ?? null,
            'event_date'     => $validated['event_date'],
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
            'attendees'      => $validated['attendees'],
            'total_amount'   => $total,
            'paid_amount'    => 0,
            'status'         => 'pending',
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        return back()->with('success', 'Hall booking created successfully.');
    })->name('services.hall-bookings.store');

    Route::post('/services/hall-bookings/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $booking = \App\Models\HallBooking::findOrFail($id);
        $booking->update([
            'status'     => $request->input('status'),
            'updated_by' => auth()->id(),
        ]);
        return back()->with('success', 'Hall booking status updated.');
    })->name('services.hall-bookings.update-status');

    Route::get('/services/housekeeping', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $tasks = \App\Models\HousekeepingTask::with('room')
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        $stats = [
            'pending'     => \App\Models\HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => \App\Models\HousekeepingTask::where('status', 'in_progress')->count(),
            'completed'   => \App\Models\HousekeepingTask::where('status', 'completed')->count(),
            'urgent'      => \App\Models\HousekeepingTask::where('priority', 'urgent')->whereNotIn('status', ['completed'])->count(),
        ];

        // Rooms for the form
        $rooms = \App\Models\Room::with('roomType')
            ->orderBy('room_number')
            ->get()
            ->map(fn($r) => [
                'id'                  => $r->id,
                'room_number'         => $r->room_number,
                'room_type'           => $r->roomType ? ['name' => $r->roomType->name] : null,
                'status'              => $r->status,
                'housekeeping_status' => $r->housekeeping_status,
            ]);

        // Housekeeping staff
        $housekeepers = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'housekeeping'))
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => trim($u->first_name . ' ' . $u->last_name)]);

        return Inertia::render('FrontDesk/Services/Housekeeping', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'tasks'        => $tasks,
            'stats'        => $stats,
            'rooms'        => $rooms,
            'housekeepers' => $housekeepers,
        ]);
    })->name('services.housekeeping');

    Route::post('/services/housekeeping', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'room_id'            => 'required|exists:rooms,id',
            'assigned_to'        => 'nullable|exists:users,id',
            'task_type'          => 'required|string|max:100',
            'priority'           => 'required|string|in:low,normal,high,urgent',
            'scheduled_date'     => 'required|date',
            'scheduled_time'     => 'nullable|string',
            'estimated_minutes'  => 'nullable|integer|min:1',
            'instructions'       => 'nullable|string',
        ]);
        \App\Models\HousekeepingTask::create([
            'room_id'           => $validated['room_id'],
            'assigned_to'       => $validated['assigned_to'] ?? null,
            'task_type'         => $validated['task_type'],
            'priority'          => $validated['priority'],
            'status'            => 'pending',
            'scheduled_date'    => $validated['scheduled_date'],
            'scheduled_time'    => $validated['scheduled_time'] ?? null,
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
            'instructions'      => $validated['instructions'] ?? null,
        ]);
        return back()->with('success', 'Housekeeping task created successfully.');
    })->name('services.housekeeping.store');

    Route::post('/services/housekeeping/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $task = \App\Models\HousekeepingTask::findOrFail($id);
        $task->update(['status' => $request->input('status', 'in_progress')]);
        return back()->with('success', 'Housekeeping request status updated.');
    })->name('services.housekeeping.update-status');

    Route::get('/services/maintenance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';

        $requests = \App\Models\MaintenanceRequest::with('room')
            ->orderBy('reported_at', 'desc')
            ->paginate(20)->withQueryString();

        $stats = [
            'total'       => \App\Models\MaintenanceRequest::count(),
            'open'        => \App\Models\MaintenanceRequest::where('status', 'open')->count(),
            'in_progress' => \App\Models\MaintenanceRequest::where('status', 'in_progress')->count(),
            'resolved'    => \App\Models\MaintenanceRequest::where('status', 'resolved')->count(),
            'urgent'      => \App\Models\MaintenanceRequest::where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        // Rooms for the form
        $rooms = \App\Models\Room::orderBy('room_number')
            ->get(['id', 'room_number']);

        // Departments for the form
        $departments = class_exists(\App\Models\Department::class)
            ? \App\Models\Department::orderBy('name')->get(['id', 'name'])
            : collect();

        return Inertia::render('FrontDesk/Services/Maintenance', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'requests'    => $requests,
            'stats'       => $stats,
            'rooms'       => $rooms,
            'departments' => $departments,
        ]);
    })->name('services.maintenance');

    Route::post('/services/maintenance', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'room_id'          => 'nullable|exists:rooms,id',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'category'         => 'required|string|max:100',
            'priority'         => 'required|string|in:low,normal,high,urgent',
            'location'         => 'nullable|string|max:255',
            'location_details' => 'nullable|string',
            'department_id'    => 'nullable|exists:departments,id',
        ]);
        \App\Models\MaintenanceRequest::create([
            'request_number'  => 'MNT-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'room_id'         => $validated['room_id'] ?? null,
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'category'        => $validated['category'],
            'priority'        => $validated['priority'],
            'location'        => $validated['location'] ?? null,
            'location_details'=> $validated['location_details'] ?? null,
            'department_id'   => $validated['department_id'] ?? null,
            'status'          => 'open',
            'reported_at'     => now(),
            'reported_by'     => auth()->id(),
        ]);
        return back()->with('success', 'Maintenance request created successfully.');
    })->name('services.maintenance.store');

    // Hall Bookings (Front Desk)
    Route::get('/services/hall-bookings', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');

        $query = \App\Models\HallBooking::query()
            ->with(['hall', 'guest'])
            ->orderByDesc('event_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->string('date'));
        }

        $bookings = $query->paginate(15)->withQueryString();

        $allBookings = \App\Models\HallBooking::get(['id', 'status']);
        $stats = [
            'total'     => $allBookings->count(),
            'pending'   => $allBookings->where('status', 'pending')->count(),
            'confirmed' => $allBookings->where('status', 'confirmed')->count(),
            'cancelled' => $allBookings->where('status', 'cancelled')->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
        ];

        $halls  = \App\Models\Hall::orderBy('name')->get(['id', 'name', 'code', 'capacity', 'base_price']);
        $guests = \App\Models\Guest::orderBy('first_name')->orderBy('last_name')
                    ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        return Inertia::render('FrontDesk/Services/HallBookings', [
            'user'      => $user,
            'bookings'  => $bookings,
            'stats'     => $stats,
            'halls'     => $halls,
            'guests'    => $guests,
            'filters'   => [
                'status' => $request->get('status'),
                'date'   => $request->get('date'),
            ],
        ]);
    })->name('services.hall-bookings');

    Route::post('/services/hall-bookings', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'hall_id'        => 'required|exists:halls,id',
            'guest_id'       => 'nullable|exists:guests,id',
            'event_date'     => 'required|date',
            'start_time'     => 'required',
            'end_time'       => 'required',
            'event_type'     => 'nullable|string|max:100',
            'attendees'      => 'nullable|integer|min:1',
            'notes'          => 'nullable|string',
        ]);

        $hall  = \App\Models\Hall::findOrFail($validated['hall_id']);
        $start = \Carbon\Carbon::createFromTimeString($validated['start_time']);
        $end   = \Carbon\Carbon::createFromTimeString($validated['end_time']);
        $hours = max(1, $start->diffInMinutes($end) / 60);
        $total = round((float) $hall->base_price * $hours, 2);

        \App\Models\HallBooking::create([
            'booking_number' => 'HB-' . now()->format('Ymd-His') . '-' . random_int(1000, 9999),
            'hall_id'        => $validated['hall_id'],
            'guest_id'       => $validated['guest_id'] ?? null,
            'event_date'     => $validated['event_date'],
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
            'event_type'     => $validated['event_type'] ?? null,
            'attendees'      => $validated['attendees'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'total_amount'   => $total,
            'status'         => 'pending',
            'booked_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Hall booking created successfully.');
    })->name('services.hall-bookings.store');

    Route::post('/services/hall-bookings/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $booking = \App\Models\HallBooking::findOrFail($id);
        $booking->update(['status' => $request->validate(['status' => 'required|string'])['status']]);
        return back()->with('success', 'Booking status updated.');
    })->name('services.hall-bookings.update-status');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Accountant\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\Accountant\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\Accountant\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Accountant\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{folio}/mark-paid', [\App\Http\Controllers\Accountant\InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::get('/invoices/overdue', [\App\Http\Controllers\Accountant\InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::get('/invoices/paid', [\App\Http\Controllers\Accountant\InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::post('/invoices/send-reminders', [\App\Http\Controllers\Accountant\InvoiceController::class, 'sendReminders'])->name('invoices.sendReminders');

    // Transactions
    Route::get('/transactions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // ── Transaction Statistics ─────────────────────────────────────────────
        $today = now()->toDateString();
        $totalTxMgr = \App\Models\Payment::count() + \App\Models\Sale::count() + \App\Models\Expense::count();
        $transactionStats = [
            'total'        => $totalTxMgr,
            'todayRevenue' => (float) \App\Models\Payment::whereDate('processed_at', $today)
                                ->where('status', 'completed')
                                ->sum('amount') + \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount'),
            'totalRevenue' => (float) \App\Models\Payment::where('status', 'completed')
                                ->sum('amount') + \App\Models\Sale::sum('total_amount'),
            'completed'    => \App\Models\Payment::where('status', 'completed')->count() + \App\Models\Sale::where('payment_status', 'completed')->count(),
            'pending'      => \App\Models\Payment::where('status', 'pending')->count() + \App\Models\Expense::where('status', 'pending')->count(),
            'failed'       => \App\Models\Payment::where('status', 'failed')->count(),
        ];

        // ── Recent Transactions ─────────────────────────────────────────────────
        $recentTransactions = collect();

        // Add recent payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'transaction_id' => $p->payment_number ?? 'PAY-' . $p->id,
                'guest_name'    => $p->reservation?->guest
                    ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Guest',
                'reference'     => $p->reservation?->id ? 'Reservation #' . $p->reservation->id : 'Direct Payment',
                'type'          => 'payment',
                'amount'        => (float)$p->amount,
                'status'        => $p->status ?? 'completed',
                'payment_method' => $p->payment_method,
                'date'          => $p->processed_at?->format('Y-m-d H:i:s') ?? $p->created_at->format('Y-m-d H:i:s'),
                'created_at'    => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add recent sales
        if (class_exists(\App\Models\Sale::class)) {
            $sales = \App\Models\Sale::with(['guest', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($s) => [
                    'transaction_id' => $s->sale_number ?? 'SALE-' . $s->id,
                    'guest_name'    => $s->guest
                        ? trim($s->guest->first_name . ' ' . $s->guest->last_name)
                        : 'Customer',
                    'reference'     => 'Sale #' . $s->id,
                    'type'          => 'sale',
                    'amount'        => (float)$s->total_amount,
                    'status'        => $s->payment_status ?? 'completed',
                    'payment_method' => $s->payment_method ?? 'cash',
                    'date'          => $s->created_at->format('Y-m-d H:i:s'),
                    'created_at'    => $s->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($sales);
        }

        // Add recent POS transactions
        if (class_exists(\App\Models\PosTransaction::class)) {
            $posTransactions = \App\Models\PosTransaction::with(['sale', 'cashDrawerSession.user'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($pt) => [
                    'transaction_id' => 'POS-' . $pt->id,
                    'guest_name'    => 'POS Customer',
                    'reference'     => $pt->sale?->sale_number ? 'POS Sale #' . $pt->sale->sale_number : 'POS Transaction',
                    'type'          => 'pos_transaction',
                    'amount'        => (float)$pt->amount,
                    'status'        => 'completed',
                    'payment_method' => $pt->payment_method ?? 'cash',
                    'date'          => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($posTransactions);
        }

        // Add recent folio charges (room charges)
        if (class_exists(\App\Models\FolioCharge::class)) {
            $folioCharges = \App\Models\FolioCharge::with(['folio.reservation.guest', 'folio.reservation.room'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($fc) => [
                    'transaction_id' => 'FOLIO-' . $fc->id,
                    'guest_name'    => $fc->folio?->reservation?->guest
                        ? trim($fc->folio->reservation->guest->first_name . ' ' . $fc->folio->reservation->guest->last_name)
                        : 'Guest',
                    'reference'     => $fc->folio?->reservation?->id ? 'Room #' . $fc->folio->reservation->room?->room_number . ' Folio' : 'Room Charge',
                    'type'          => 'folio_charge',
                    'amount'        => (float)$fc->net_amount,
                    'status'        => 'active',
                    'payment_method' => 'room_charge',
                    'date'          => $fc->charge_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $fc->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($folioCharges);
        }

        // Add recent expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($e) => [
                    'transaction_id' => $e->expense_number ?? 'EXP-' . $e->id,
                    'guest_name'    => $e->vendor_name ?? 'Vendor',
                    'reference'     => 'Expense #' . $e->id,
                    'type'          => 'expense',
                    'amount'        => (float)$e->amount,
                    'status'        => $e->status ?? 'pending',
                    'payment_method' => $e->payment_method ?? 'cash',
                    'date'          => $e->expense_date->format('Y-m-d H:i:s'),
                    'created_at'    => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->take(50)->values();

        return Inertia::render('Manager/Transactions/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'transactionStats' => $transactionStats,
            'recentTransactions' => $recentTransactions->toArray(),
            'filters' => [
                'search'     => request('search', ''),
                'type'       => request('type', ''),
                'status'     => request('status', ''),
                'start_date' => request('start_date', ''),
                'end_date'   => request('end_date', ''),
            ],
        ]);
    })->name('transactions.index');

    Route::get('/transactions/export', function (\Illuminate\Http\Request $request) {
        $format = $request->get('format', 'csv');

        // Get transactions data
        $recentTransactions = collect();

        // Add payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('processed_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'ID' => $p->id,
                'Type' => 'Payment',
                'Description' => $p->reservation?->guest
                    ? 'Payment from ' . trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Payment Received',
                'Amount' => (float)$p->amount,
                'Status' => $p->status ?? 'completed',
                'Payment Method' => $p->payment_method,
                'Date' => $p->processed_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'Created At' => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add sales
        if (class_exists(\App\Models\Sale::class)) {
            $sales = \App\Models\Sale::with(['guest', 'user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($s) => [
                    'ID' => $s->id,
                    'Type' => 'Sale',
                    'Description' => $s->guest
                        ? 'Sale to ' . trim($s->guest->first_name . ' ' . $s->guest->last_name)
                        : 'Customer Sale',
                    'Amount' => (float)$s->total_amount,
                    'Status' => $s->payment_status ?? 'completed',
                    'Payment Method' => $s->payment_method ?? 'cash',
                    'Date' => $s->created_at->format('Y-m-d H:i:s'),
                    'Created At' => $s->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($sales);
        }

        // Add POS transactions
        if (class_exists(\App\Models\PosTransaction::class)) {
            $posTransactions = \App\Models\PosTransaction::with(['sale', 'cashDrawerSession.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($pt) => [
                    'ID' => $pt->id,
                    'Type' => 'POS Transaction',
                    'Description' => $pt->sale?->sale_number
                        ? 'POS Sale #' . $pt->sale->sale_number
                        : 'POS Transaction',
                    'Amount' => (float)$pt->amount,
                    'Status' => $pt->status ?? 'completed',
                    'Payment Method' => $pt->payment_method ?? 'cash',
                    'Date' => $pt->created_at->format('Y-m-d H:i:s'),
                    'Created At' => $pt->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($posTransactions);
        }

        // Add folio charges (room charges)
        if (class_exists(\App\Models\FolioCharge::class)) {
            $folioCharges = \App\Models\FolioCharge::with(['folio.reservation.guest', 'folio.reservation.room'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($fc) => [
                    'ID' => $fc->id,
                    'Type' => 'Room Charge',
                    'Description' => $fc->folio?->reservation?->guest
                        ? 'Room charge for ' . trim($fc->folio->reservation->guest->first_name . ' ' . $fc->folio->reservation->guest->last_name)
                        : 'Room Charge',
                    'Amount' => (float)$fc->net_amount,
                    'Status' => 'active',
                    'Payment Method' => 'room_charge',
                    'Date' => $fc->created_at->format('Y-m-d H:i:s'),
                    'Created At' => $fc->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($folioCharges);
        }

        // Add expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->get()
                ->map(fn($e) => [
                    'ID' => $e->id,
                    'Type' => 'Expense',
                    'Description' => $e->description ?? $e->vendor_name ?? 'Expense',
                    'Amount' => (float)$e->amount,
                    'Status' => $e->status ?? 'pending',
                    'Payment Method' => $e->payment_method ?? 'cash',
                    'Date' => $e->expense_date->format('Y-m-d H:i:s'),
                    'Created At' => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->values();

        if ($format === 'csv') {
            $filename = 'transactions-' . now()->format('Ymd-His') . '.csv';
            $data = $recentTransactions;
            return response()->streamDownload(function () use ($data) {
                $handle = fopen('php://output', 'wb');
                if ($data->isNotEmpty()) {
                    fputcsv($handle, array_keys($data->first()));
                }
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        // For other formats, redirect back for now
        return back()->with('info', 'Export format not implemented yet');
    })->name('transactions.export');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\Admin\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{folio}/mark-paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'markPaid'])->name('invoices.markPaid');

    // ── Quotes ───────────────────────────────────────────────────────────────
    Route::get('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [\App\Http\Controllers\Admin\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/quotes/{id}/edit', [\App\Http\Controllers\Admin\QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'update'])->name('quotes.update');

    // Reports sub-routes (aliases kept for Ziggy/nav compatibility)
});

// Accountant Routes
Route::middleware(['auth', 'role:accountant'])->prefix('accountant')->name('accountant.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $today = now()->toDateString();

        // ── Revenue Calculations ───────────────────────────────────────────────
        // Today's revenue based on actual payments received
        $roomRevenue = (float) \App\Models\Payment::whereDate('processed_at', $today)
                        ->where('status', 'completed')
                        ->sum('amount');
        $posRevenue  = class_exists(\App\Models\Sale::class)
                        ? (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount') : 0.0;
        $hallRevenue = class_exists(\App\Models\HallBooking::class)
                        ? (float) \App\Models\HallBooking::whereDate('created_at', $today)
                            ->whereIn('status', ['confirmed','completed'])->sum('total_amount') : 0.0;
        $todayRevenue = $roomRevenue + $posRevenue + $hallRevenue;

        // ── Expense Calculations ───────────────────────────────────────────────
        $totalExpenses = class_exists(\App\Models\Expense::class)
                        ? (float) \App\Models\Expense::whereDate('expense_date', $today)->sum('amount') : 0.0;

        // ── Historical Data for Averages ───────────────────────────────────────
        $last30Days = now()->subDays(30)->toDateString();

        // Revenue based on actual payments received (more accurate than reservation amounts)
        $monthlyRevenue = (float) \App\Models\Payment::whereDate('processed_at', '>=', $last30Days)
                            ->where('status', 'completed')
                            ->sum('amount');

        // Add POS sales if they exist (these are actual sales, not reservations)
        if (class_exists(\App\Models\Sale::class)) {
            $monthlyRevenue += (float) \App\Models\Sale::whereDate('created_at', '>=', $last30Days)->sum('total_amount');
        }

        // Add hall bookings if they exist
        if (class_exists(\App\Models\HallBooking::class)) {
            $monthlyRevenue += (float) \App\Models\HallBooking::whereDate('created_at', '>=', $last30Days)
                                ->whereIn('status', ['confirmed','completed'])->sum('total_amount');
        }

        $monthlyExpenses = class_exists(\App\Models\Expense::class)
                            ? (float) \App\Models\Expense::whereDate('expense_date', '>=', $last30Days)->sum('amount') : 0.0;

        // ── Metrics ───────────────────────────────────────────────────────────
        $avgDailyRevenue = $monthlyRevenue / 30;
        $netProfit = $monthlyRevenue - $monthlyExpenses;
        $profitMargin = $monthlyRevenue > 0 ? round(($netProfit / $monthlyRevenue) * 100, 1) : 0;
        $expenseRatio = $monthlyRevenue > 0 ? round(($monthlyExpenses / $monthlyRevenue) * 100, 1) : 0;
        $cashFlow = $netProfit;

        $metrics = [
            'avgDailyRevenue' => $avgDailyRevenue,
            'profitMargin'    => $profitMargin,
            'expenseRatio'    => $expenseRatio,
            'cashFlow'        => $cashFlow,
        ];

        // ── Financial Summary ───────────────────────────────────────────────────
        $financialSummary = [
            'todaysRevenue'   => $todayRevenue,
            'monthlyRevenue'  => $monthlyRevenue,
            'monthlyExpenses' => $monthlyExpenses,
            'netProfit'       => $netProfit,
        ];

        // ── Recent Transactions (last 10) ───────────────────────────────────────
        $recentTransactions = collect();

        // Add recent payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'type'        => 'income',
                'description' => $p->reservation?->guest
                    ? 'Payment from ' . trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Payment Received',
                'amount'      => (float)$p->amount,
                'date'        => $p->processed_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add recent expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($e) => [
                    'id'          => $e->id,
                    'type'        => 'expense',
                    'description' => $e->description ?? $e->vendor_name ?? 'Expense',
                    'amount'      => (float)$e->amount,
                    'date'        => $e->expense_date->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->take(10)->values();

        // ── Pending Payments ───────────────────────────────────────────────────
        $pendingPayments = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereRaw('(total_amount - COALESCE(paid_amount, 0)) > 0')
            ->orderBy('check_out_date')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'guest_name'  => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Guest',
                'room_number' => $r->room?->room_number ?? 'N/A',
                'amount'      => max(0, (float)($r->total_amount ?? 0) - (float)($r->paid_amount ?? 0)),
                'due_date'    => $r->check_out_date,
            ]);

        // ── Chart Data ─────────────────────────────────────────────────────────
        // Revenue vs Expenses - Last 7 days
        $revenueExpenseData = [];
        $expenseCategoriesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayName = now()->subDays($i)->format('D');

            // Calculate revenue for this day
            $dayRevenue = (float) \App\Models\Reservation::whereDate('check_in_date', $date)
                            ->whereNotIn('status', ['cancelled','no_show'])
                            ->sum('total_amount');
            if (class_exists(\App\Models\Sale::class)) {
                $dayRevenue += (float) \App\Models\Sale::whereDate('created_at', $date)->sum('total_amount');
            }
            if (class_exists(\App\Models\HallBooking::class)) {
                $dayRevenue += (float) \App\Models\HallBooking::whereDate('created_at', $date)
                                    ->whereIn('status', ['confirmed','completed'])->sum('total_amount');
            }

            // Calculate expenses for this day
            $dayExpenses = class_exists(\App\Models\Expense::class)
                            ? (float) \App\Models\Expense::whereDate('expense_date', $date)->sum('amount') : 0.0;

            $revenueExpenseData[] = [
                'month' => $dayName,
                'revenue' => $dayRevenue,
                'expenses' => $dayExpenses,
            ];
        }

        // Expense Categories - Last 30 days
        $expenseCategoriesData = [];
        if (class_exists(\App\Models\Expense::class) && class_exists(\App\Models\ExpenseCategory::class)) {
            $categories = \App\Models\ExpenseCategory::where('is_active', true)
                            ->withSum(['expenses' => function($query) use ($last30Days) {
                                $query->whereDate('expense_date', '>=', $last30Days);
                            }], 'amount')
                            ->orderByDesc('expenses_sum_amount')
                            ->limit(5)
                            ->get();

            foreach ($categories as $category) {
                $amount = (float)($category->expenses_sum_amount ?? 0);
                if ($amount > 0) {
                    $expenseCategoriesData[] = [
                        'category' => $category->name,
                        'amount' => $amount,
                    ];
                }
            }

            // If no categories found or all amounts are 0, get totals by description/vendor
            if (empty($expenseCategoriesData)) {
                $expensesByCategory = \App\Models\Expense::whereDate('expense_date', '>=', $last30Days)
                    ->selectRaw('COALESCE(description, vendor_name, "Other") as category, SUM(amount) as total')
                    ->groupBy('category')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();

                foreach ($expensesByCategory as $expense) {
                    $expenseCategoriesData[] = [
                        'category' => $expense->category,
                        'amount' => (float)$expense->total,
                    ];
                }
            }
        }

        // If still no data, provide empty structure
        if (empty($expenseCategoriesData)) {
            $expenseCategoriesData = [
                ['category' => 'No expenses', 'amount' => 0],
            ];
        }

        $charts = [
            'revenueExpense' => $revenueExpenseData,
            'expenses' => $expenseCategoriesData,
        ];

        return Inertia::render('Accountant/Dashboard', [
            'user'              => $user,
            'navigation'        => app(DashboardController::class)->getNavigationForRole($role),
            'metrics'           => $metrics,
            'financialSummary'  => $financialSummary,
            'recentTransactions'=> $recentTransactions,
            'pendingPayments'   => $pendingPayments,
            'charts'            => $charts,
        ]);
    })->name('dashboard');

    // Customers
    Route::get('/customers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $search = request('search');
        $groupId = request('group_id');
        $status = request('status');

        $query = \App\Models\Customer::with(['customerGroup'])
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        if ($groupId) {
            $query->where('customer_group_id', $groupId);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $customers = $query->paginate(50)->withQueryString();

        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id', 'name', 'discount_percentage', 'is_active']);

        return Inertia::render('Accountant/Customers/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customers' => $customers,
            'customerGroups' => $customerGroups,
            'filters' => [
                'search' => $search,
                'group_id' => $groupId,
                'status' => $status,
            ],
        ]);
    })->name('customers.index');

    Route::get('/customers/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id','name']);

        return Inertia::render('Accountant/Customers/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroups' => $customerGroups,
        ]);
    })->name('customers.create');

    Route::post('/customers', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'is_active' => 'boolean',
        ]);

        // Generate customer code
        $validated['customer_code'] = 'CUST-' . strtoupper(\Illuminate\Support\Str::random(6));
        $validated['is_active'] = $validated['is_active'] ?? true;

        \App\Models\Customer::create($validated);

        return redirect()->route('accountant.customers.index')->with('success', 'Customer created successfully.');
    })->name('customers.store');

    Route::get('/customers/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customer = \App\Models\Customer::with(['customerGroup'])->findOrFail($id);

        return Inertia::render('Accountant/Customers/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customer' => $customer,
        ]);
    })->name('customers.show');

    Route::get('/customers/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $customer = \App\Models\Customer::with(['customerGroup'])->findOrFail($id);
        $customerGroups = \App\Models\CustomerGroup::orderBy('name')->get(['id','name','discount_percentage']);

        return Inertia::render('Accountant/Customers/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customer' => $customer,
            'customerGroups' => $customerGroups,
        ]);
    })->name('customers.edit');

    // Customer Groups
    Route::get('/customer-groups', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front-desk';

        $customerGroups = \App\Models\CustomerGroup::withCount('customers')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'          => \App\Models\CustomerGroup::count(),
            'active'         => \App\Models\CustomerGroup::where('is_active', true)->count(),
            'inactive'       => \App\Models\CustomerGroup::where('is_active', false)->count(),
            'totalCustomers' => \App\Models\Customer::count(),
        ];

        return Inertia::render('FrontDesk/CustomerGroups/Index', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'customerGroups' => $customerGroups,
            'stats'          => $stats,
        ]);
    })->name('customer-groups.index');

    // Budget
    Route::get('/budget', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get budget data
        $budgets = \App\Models\Budget::with(['expenses' => function($query) {
            $query->whereYear('created_at', now()->year);
        }])->get();

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum(function($budget) {
            return $budget->expenses->sum('amount');
        });
        $remaining = $totalBudget - $totalSpent;

        // Budget by category
        $budgetByCategory = $budgets->groupBy('category')->map(function($group) {
            return [
                'budgeted' => $group->sum('amount'),
                'spent' => $group->sum(function($budget) {
                    return $budget->expenses->sum('amount');
                }),
                'remaining' => $group->sum('amount') - $group->sum(function($budget) {
                    return $budget->expenses->sum('amount');
                })
            ];
        });

        return Inertia::render('Accountant/Budget/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'budgetData' => [
                'totalBudget' => $totalBudget,
                'totalSpent' => $totalSpent,
                'remaining' => $remaining,
                'budgetByCategory' => $budgetByCategory,
                'budgets' => $budgets
            ]
        ]);
    })->name('budget.index');

    Route::get('/budget/comparison', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get current year and previous year budgets
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $currentBudgets = \App\Models\Budget::where('year', $currentYear)->get();
        $previousBudgets = \App\Models\Budget::where('year', $previousYear)->get();

        // Calculate totals
        $currentTotal = $currentBudgets->sum('amount');
        $previousTotal = $previousBudgets->sum('amount');

        // Get actual expenses for comparison
        $currentExpenses = \App\Models\Expense::whereYear('expense_date', $currentYear)->sum('amount');
        $previousExpenses = \App\Models\Expense::whereYear('expense_date', $previousYear)->sum('amount');

        // Monthly comparison
        $monthlyComparison = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyComparison[] = [
                'month' => \Carbon\Carbon::createFromDate($currentYear, $month, 1)->format('M'),
                'currentBudget' => $currentBudgets->where('month', $month)->sum('amount'),
                'currentExpenses' => \App\Models\Expense::whereYear('expense_date', $currentYear)->whereMonth('expense_date', $month)->sum('amount'),
                'previousBudget' => $previousBudgets->where('month', $month)->sum('amount'),
                'previousExpenses' => \App\Models\Expense::whereYear('expense_date', $previousYear)->whereMonth('expense_date', $month)->sum('amount'),
            ];
        }

        return Inertia::render('Accountant/Budget/Comparison', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'comparisonData' => [
                'currentYear' => $currentYear,
                'previousYear' => $previousYear,
                'currentTotal' => $currentTotal,
                'previousTotal' => $previousTotal,
                'currentExpenses' => $currentExpenses,
                'previousExpenses' => $previousExpenses,
                'monthlyComparison' => $monthlyComparison
            ]
        ]);
    })->name('budget.comparison');

    Route::get('/budget/forecast', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get historical data for forecasting
        $last12Months = now()->subMonths(12);
        $historicalExpenses = \App\Models\Expense::where('expense_date', '>=', $last12Months)
            ->orderBy('expense_date')
            ->get()
            ->groupBy(function($expense) {
                return $expense->expense_date->format('Y-m');
            });

        // Calculate monthly averages by category
        $categoryAverages = [];
        if (class_exists('App\Models\ExpenseCategory')) {
            $categories = \App\Models\ExpenseCategory::where('is_active', true)->get();
            foreach ($categories as $category) {
                $monthlyAvg = \App\Models\Expense::where('expense_category_id', $category->id)
                    ->where('expense_date', '>=', $last12Months)
                    ->avg('amount');
                $categoryAverages[] = [
                    'category' => $category->name,
                    'average' => $monthlyAvg ?? 0,
                    'trend' => 'stable' // Could calculate trend based on last 3 months vs previous 3 months
                ];
            }
        }

        // Forecast next 6 months
        $forecast = [];
        for ($i = 1; $i <= 6; $i++) {
            $forecastMonth = now()->addMonths($i);
            $forecast[] = [
                'month' => $forecastMonth->format('M Y'),
                'predictedExpenses' => \App\Models\Expense::whereMonth('expense_date', $forecastMonth->month)
                    ->whereYear('expense_date', $forecastMonth->year - 1)
                    ->sum('amount') * 1.1, // Assume 10% increase
                'predictedRevenue' => \App\Models\Payment::whereMonth('processed_at', $forecastMonth->month)
                    ->whereYear('processed_at', $forecastMonth->year - 1)
                    ->sum('amount') * 1.05, // Assume 5% increase
            ];
        }

        return Inertia::render('Accountant/Budget/Forecast', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'forecastData' => [
                'historicalExpenses' => $historicalExpenses,
                'categoryAverages' => $categoryAverages,
                'forecast' => $forecast,
                'totalCurrentYear' => \App\Models\Expense::whereYear('expense_date', now()->year)->sum('amount'),
                'totalLastYear' => \App\Models\Expense::whereYear('expense_date', now()->year - 1)->sum('amount')
            ]
        ]);
    })->name('budget.forecast');

    // Accounting
    Route::get('/transactions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // ── Transaction Statistics ─────────────────────────────────────────────
        $totalTx     = \App\Models\Payment::count() + \App\Models\Sale::count() + \App\Models\PosTransaction::count() + \App\Models\FolioCharge::count() + \App\Models\Expense::count();
        $todayRevenue = (float) \App\Models\Payment::where('status', 'completed')
            ->whereDate('processed_at', now()->toDateString())
            ->sum('amount');
        $monthRevenue = (float) \App\Models\Payment::where('status', 'completed')
            ->whereMonth('processed_at', now()->month)
            ->whereYear('processed_at', now()->year)
            ->sum('amount');
        $transactionStats = [
            'total'             => $totalTx,
            'totalTransactions' => $totalTx,
            'todayRevenue'      => $todayRevenue,
            'monthRevenue'      => $monthRevenue,
            'completed'         => \App\Models\Payment::where('status', 'completed')->count() + \App\Models\Sale::where('payment_status', 'completed')->count(),
            'pending'           => \App\Models\Payment::where('status', 'pending')->count() + \App\Models\Expense::where('status', 'pending')->count(),
            'failed'            => \App\Models\Payment::where('status', 'failed')->count(),
        ];

        // ── Recent Transactions (paginated) ─────────────────────────────────────
        $recentTransactions = collect();

        // Add recent payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'source_id'     => $p->id,
                'transaction_id' => $p->payment_number ?? 'PAY-' . $p->id,
                'guest_name'    => $p->reservation?->guest
                    ? trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Guest',
                'reference'     => $p->reservation?->id ? 'Reservation #' . $p->reservation->id : 'Direct Payment',
                'type'          => 'payment',
                'amount'        => (float)$p->amount,
                'status'        => $p->status ?? 'completed',
                'payment_method' => $p->payment_method,
                'date'          => $p->processed_at?->format('Y-m-d H:i:s') ?? $p->created_at->format('Y-m-d H:i:s'),
                'created_at'    => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add recent sales
        if (class_exists(\App\Models\Sale::class)) {
            $sales = \App\Models\Sale::with(['guest', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($s) => [
                    'id'            => $s->id,
                    'source_id'     => $s->id,
                    'transaction_id' => $s->sale_number ?? 'SALE-' . $s->id,
                    'guest_name'    => $s->guest
                        ? trim($s->guest->first_name . ' ' . $s->guest->last_name)
                        : 'Customer',
                    'reference'     => 'Sale #' . $s->id,
                    'type'          => 'sale',
                    'amount'        => (float)$s->total_amount,
                    'status'        => $s->payment_status ?? 'completed',
                    'payment_method' => $s->payment_method ?? 'cash',
                    'date'          => $s->created_at->format('Y-m-d H:i:s'),
                    'created_at'    => $s->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($sales);
        }

        // Add recent POS transactions
        if (class_exists(\App\Models\PosTransaction::class)) {
            $posTransactions = \App\Models\PosTransaction::with(['sale', 'cashDrawerSession.user'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($pt) => [
                    'id'            => $pt->id,
                    'source_id'     => $pt->id,
                    'transaction_id' => 'POS-' . $pt->id,
                    'guest_name'    => 'POS Customer',
                    'reference'     => $pt->sale?->sale_number ? 'POS Sale #' . $pt->sale->sale_number : 'POS Transaction',
                    'type'          => 'pos_transaction',
                    'amount'        => (float)$pt->amount,
                    'status'        => 'completed',
                    'payment_method' => $pt->payment_method ?? 'cash',
                    'date'          => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $pt->sale?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($posTransactions);
        }

        // Add recent folio charges (room charges)
        if (class_exists(\App\Models\FolioCharge::class)) {
            $folioCharges = \App\Models\FolioCharge::with(['folio.reservation.guest', 'folio.reservation.room'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($fc) => [
                    'id'            => $fc->id,
                    'source_id'     => $fc->id,
                    'transaction_id' => 'FOLIO-' . $fc->id,
                    'guest_name'    => $fc->folio?->reservation?->guest
                        ? trim($fc->folio->reservation->guest->first_name . ' ' . $fc->folio->reservation->guest->last_name)
                        : 'Guest',
                    'reference'     => $fc->folio?->reservation?->id ? 'Room #' . $fc->folio->reservation->room?->room_number . ' Folio' : 'Room Charge',
                    'type'          => 'folio_charge',
                    'amount'        => (float)$fc->net_amount,
                    'status'        => 'active',
                    'payment_method' => 'room_charge',
                    'date'          => $fc->charge_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'created_at'    => $fc->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($folioCharges);
        }

        // Add recent expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($e) => [
                    'id'            => $e->id,
                    'source_id'     => $e->id,
                    'transaction_id' => $e->expense_number ?? 'EXP-' . $e->id,
                    'guest_name'    => $e->vendor_name ?? 'Vendor',
                    'reference'     => 'Expense #' . $e->id,
                    'type'          => 'expense',
                    'amount'        => (float)$e->amount,
                    'status'        => $e->status ?? 'pending',
                    'payment_method' => $e->payment_method ?? 'cash',
                    'date'          => $e->expense_date->format('Y-m-d H:i:s'),
                    'created_at'    => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->take(50)->values();

        return Inertia::render('Accountant/Transactions/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'transactionStats' => $transactionStats,
            'recentTransactions' => [
                'data' => $recentTransactions->toArray(),
                'total' => $recentTransactions->count(),
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 50,
                'from' => 1,
                'to' => $recentTransactions->count(),
            ],
            'filters' => request()->only(['status', 'type', 'start_date', 'end_date']),
        ]);
    })->name('transactions.index');

    Route::get('/transactions/payments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $payments = \App\Models\Payment::with(['reservation.guest', 'reservation.room'])
            ->orderBy('processed_at', 'desc')
            ->paginate(20)
            ->through(fn($p) => [
                'id' => $p->id,
                'payment_number' => $p->payment_number,
                'amount' => (float) $p->amount,
                'method' => $p->payment_method,
                'status' => $p->status,
                'date' => $p->processed_at?->toDateString(),
                'guest' => $p->reservation?->guest?->first_name . ' ' . $p->reservation?->guest?->last_name,
                'room' => $p->reservation?->room?->room_number,
            ]);

        return Inertia::render('Accountant/Transactions/Payments', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'payments' => $payments,
        ]);
    })->name('transactions.payments');

    Route::get('/transactions/refunds', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // For now, return empty refunds data as refunds may not be implemented yet
        $refunds = collect([]);

        return Inertia::render('Accountant/Transactions/Refunds', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'refunds' => $refunds,
        ]);
    })->name('transactions.refunds');

    Route::get('/transactions/pending', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get pending payments (payments with pending status)
        $pendingPayments = \App\Models\Payment::with(['reservation.guest', 'reservation.room'])
            ->where('status', 'pending')
            ->orderBy('processed_at', 'desc')
            ->paginate(20)
            ->through(fn($p) => [
                'id' => $p->id,
                'payment_number' => $p->payment_number,
                'amount' => (float) $p->amount,
                'method' => $p->payment_method,
                'status' => $p->status,
                'date' => $p->processed_at?->toDateString(),
                'guest' => $p->reservation?->guest?->first_name . ' ' . $p->reservation?->guest?->last_name,
                'room' => $p->reservation?->room?->room_number,
            ]);

        return Inertia::render('Accountant/Transactions/Pending', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'pendingPayments' => $pendingPayments,
        ]);
    })->name('transactions.pending');

    Route::get('/transactions/export', function (\Illuminate\Http\Request $request) {
        $format = $request->get('format', 'csv');

        // Get transactions data
        $recentTransactions = collect();

        // Add payments
        $payments = \App\Models\Payment::with('reservation.guest')
            ->orderBy('processed_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'ID' => $p->id,
                'Type' => 'Payment',
                'Description' => $p->reservation?->guest
                    ? 'Payment from ' . trim($p->reservation->guest->first_name . ' ' . $p->reservation->guest->last_name)
                    : 'Payment Received',
                'Amount' => (float)$p->amount,
                'Status' => $p->status ?? 'completed',
                'Payment Method' => $p->payment_method,
                'Date' => $p->processed_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'Created At' => $p->created_at->format('Y-m-d H:i:s'),
            ]);
        $recentTransactions = $recentTransactions->merge($payments);

        // Add expenses
        if (class_exists(\App\Models\Expense::class)) {
            $expenses = \App\Models\Expense::orderBy('expense_date', 'desc')
                ->get()
                ->map(fn($e) => [
                    'ID' => $e->id,
                    'Type' => 'Expense',
                    'Description' => $e->description ?? $e->vendor_name ?? 'Expense',
                    'Amount' => (float)$e->amount,
                    'Status' => $e->status ?? 'pending',
                    'Payment Method' => $e->payment_method ?? 'cash',
                    'Date' => $e->expense_date->format('Y-m-d H:i:s'),
                    'Created At' => $e->created_at->format('Y-m-d H:i:s'),
                ]);
            $recentTransactions = $recentTransactions->merge($expenses);
        }

        $recentTransactions = $recentTransactions->sortByDesc('date')->values();

        $filename = 'transactions-' . now()->format('Ymd-His') . '.csv';
        $data = $recentTransactions;
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'wb');
            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->name('transactions.export');

    Route::post('/transactions/process/{payment}', function (\App\Models\Payment $payment) {
        if ($payment->status === 'pending') {
            $payment->update(['status' => 'completed']);
        }
        return back()->with('success', 'Transaction processed successfully.');
    })->name('transactions.process');

    Route::get('/expenses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();

        $query = \App\Models\Expense::with(['category', 'submittedBy'])->latest();

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('category')) {
            $query->where('expense_category_id', request('category'));
        }
        if (request('start_date')) {
            $query->whereDate('expense_date', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $query->whereDate('expense_date', '<=', request('end_date'));
        }

        $expenses = $query->paginate(20)->through(fn($e) => [
            'id'             => $e->id,
            'description'    => $e->description,
            'vendor'         => $e->vendor_name,
            'category'       => $e->category?->name ?? 'Uncategorized',
            'category_id'    => $e->expense_category_id,
            'category_color' => $e->category?->color,
            'amount'         => (float) $e->amount,
            'date'           => $e->expense_date?->toDateString(),
            'status'         => $e->status,
            'payment_method' => $e->payment_method,
            'receipt_number' => $e->receipt_number,
            'notes'          => $e->notes,
        ]);

        $expenseStats = [
            'thisMonth'  => (float) \App\Models\Expense::whereMonth('expense_date', now()->month)
                                ->whereYear('expense_date', now()->year)->sum('amount'),
            'pending'    => \App\Models\Expense::where('status', 'pending')->count(),
            'total'      => \App\Models\Expense::count(),
            'categories' => \App\Models\ExpenseCategory::where('is_active', true)->count(),
        ];

        return Inertia::render('Accountant/Expenses/Index', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'expenses'       => $expenses,
            'categories'     => $categories,
            'expenseStats'   => $expenseStats,
            'filters'        => request()->only(['status', 'category', 'start_date', 'end_date']),
        ]);
    })->name('expenses.index');

    Route::get('/expenses/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        $categories = \App\Models\ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Accountant/Expenses/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories,
        ]);
    })->name('expenses.create');

    Route::post('/expenses', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ]);

        // Generate expense number
        $validated['expense_number'] = 'EXP-' . strtoupper(\Illuminate\Support\Str::random(8));
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['created_by'] = auth()->id();
        $validated['submitted_by'] = auth()->id();

        \App\Models\Expense::create($validated);

        return redirect()->route('accountant.expenses.index')->with('success', 'Expense created successfully.');
    })->name('expenses.store');

    Route::get('/invoices', [\App\Http\Controllers\Accountant\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/export', [\App\Http\Controllers\Accountant\InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/invoices/overdue', [\App\Http\Controllers\Accountant\InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::get('/invoices/paid', [\App\Http\Controllers\Accountant\InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::post('/invoices/send-reminders', [\App\Http\Controllers\Accountant\InvoiceController::class, 'sendReminders'])->name('invoices.sendReminders');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Accountant\InvoiceController::class, 'show'])->name('invoices.show');

    // Quotes
    Route::get('/quotes', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $query = \App\Models\Quote::with(['customer', 'creator'])->latest();

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%");
            });
        }
        if (request('start_date')) {
            $query->whereDate('issue_date', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $query->whereDate('issue_date', '<=', request('end_date'));
        }

        $quotes = $query->get()->map(fn($q) => [
            'id'             => $q->id,
            'quote_number'   => $q->quote_number,
            'customer_name'  => $q->customer_name ?? $q->customer?->full_name ?? 'Unknown',
            'customer_email' => $q->customer_email ?? $q->customer?->email ?? null,
            'total_amount'   => (float) $q->total_amount,
            'status'         => $q->status,
            'issue_date'     => $q->issue_date?->format('Y-m-d'),
            'valid_until'    => $q->valid_until?->format('Y-m-d'),
            'created_at'     => $q->created_at->format('Y-m-d'),
            'created_by'     => $q->creator?->full_name ?? 'Staff',
        ])->values()->toArray();

        $allQuotes = \App\Models\Quote::all();
        $quoteStats = [
            'total'       => $allQuotes->count(),
            'totalAmount' => (float) $allQuotes->sum('total_amount'),
            'pending'     => $allQuotes->whereIn('status', ['draft', 'sent'])->count(),
            'accepted'    => $allQuotes->where('status', 'accepted')->count(),
        ];

        return Inertia::render('Accountant/Quotes/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'quoteStats' => $quoteStats,
            'quotes'     => $quotes,
            'filters'    => [
                'status'     => request('status', ''),
                'start_date' => request('start_date', ''),
                'end_date'   => request('end_date', ''),
                'search'     => request('search', ''),
            ],
        ]);
    })->name('quotes.index');

    Route::get('/quotes/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $reservations = \App\Models\Reservation::with(['guest', 'room'])
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->latest()->limit(50)->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Guest',
                'room_number' => $r->room?->room_number,
                'check_in'   => $r->check_in_date?->format('Y-m-d'),
                'check_out'  => $r->check_out_date?->format('Y-m-d'),
            ]);

        return Inertia::render('Accountant/Quotes/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'reservations' => $reservations,
        ]);
    })->name('quotes.create');

    Route::get('/quotes/export', function () {
        $quotes = \App\Models\Quote::with(['customer'])->latest()->get();
        $filename = 'quotes-' . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(function () use ($quotes) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Quote Number', 'Customer', 'Email', 'Total Amount', 'Status', 'Issue Date', 'Valid Until']);
            foreach ($quotes as $q) {
                fputcsv($handle, [
                    $q->quote_number,
                    $q->customer_name ?? $q->customer?->full_name ?? 'Unknown',
                    $q->customer_email ?? $q->customer?->email ?? '',
                    $q->total_amount,
                    $q->status,
                    $q->issue_date?->format('Y-m-d'),
                    $q->valid_until?->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->name('quotes.export');

    Route::get('/quotes/{quote}', function (\App\Models\Quote $quote) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('Accountant/Quotes/Show', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'quote'      => $quote->load(['customer', 'items', 'reservation', 'creator']),
        ]);
    })->name('quotes.show');

    Route::get('/quotes/{quote}/edit', function (\App\Models\Quote $quote) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('Accountant/Quotes/Edit', [
            'user'  => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'quote' => $quote->load(['customer', 'items']),
        ]);
    })->name('quotes.edit');

    Route::post('/quotes/{quote}/send', function (\App\Models\Quote $quote) {
        $quote->update(['status' => 'sent']);
        return back()->with('success', 'Quote sent successfully.');
    })->name('quotes.send');

    Route::post('/quotes/{quote}/convert', function (\App\Models\Quote $quote) {
        $quote->update(['status' => 'accepted']);
        return back()->with('success', 'Quote converted to invoice.');
    })->name('quotes.convert');

    // Payroll
    Route::get('/payroll', [\App\Http\Controllers\Accountant\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/process', [\App\Http\Controllers\Accountant\PayrollController::class, 'process'])->name('payroll.process');
    Route::get('/payroll/history', [\App\Http\Controllers\Accountant\PayrollController::class, 'history'])->name('payroll.history');
    Route::get('/payroll/export', [\App\Http\Controllers\Accountant\PayrollController::class, 'export'])->name('payroll.export');
    Route::get('/payroll/taxes', [\App\Http\Controllers\Accountant\PayrollController::class, 'taxes'])->name('payroll.taxes');
    Route::post('/payroll/approve', [\App\Http\Controllers\Accountant\PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('/payroll/approve-all', [\App\Http\Controllers\Accountant\PayrollController::class, 'approveAll'])->name('payroll.approve.all');

    // Reports
    Route::get('/reports/profit-loss', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();
        $startOfPrev  = now()->subMonth()->startOfMonth();
        $endOfPrev    = now()->subMonth()->endOfMonth();

        // Revenue this month from payments
        $totalRevenue = (float) \App\Models\Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        $prevRevenue = (float) \App\Models\Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startOfPrev, $endOfPrev])
            ->sum('amount');

        // Expenses this month
        $totalExpenses = (float) \App\Models\Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');

        // Room revenue (from reservations linked payments)
        $roomRevenue = (float) \App\Models\Payment::where('status', 'completed')
            ->whereHas('reservation')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // POS sales revenue
        $posRevenue = (float) \App\Models\Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');

        $grossProfit   = $totalRevenue - 0; // no COGS model
        $netProfit     = $totalRevenue - $totalExpenses;

        // Operating expenses by category
        $expensesByCategory = \App\Models\Expense::with('category')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(fn($e) => $e->category?->name ?? 'Uncategorized')
            ->map(fn($group) => $group->sum('amount'))
            ->toArray();

        return Inertia::render('Accountant/Reports/ProfitLoss', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'period'     => 'monthly',
            'currency'   => ['code' => 'USD', 'symbol' => '$'],
            'profitLossData' => [
                'total_revenue'              => $totalRevenue,
                'total_cogs'                 => 0.0,
                'total_operating_expenses'   => $totalExpenses,
                'total_other_income_expenses'=> 0.0,
                'gross_profit'               => $grossProfit,
                'net_profit'                 => $netProfit,
                'operating_income'           => $netProfit,
                'revenue'                    => [
                    'room_revenue' => $roomRevenue,
                    'pos_revenue'  => $posRevenue,
                    'other'        => max(0, $totalRevenue - $roomRevenue - $posRevenue),
                ],
                'cogs'                       => [],
                'operating_expenses'         => $expensesByCategory,
                'other_income_expenses'      => [],
            ],
        ]);
    })->name('reports.profit-loss');

    Route::get('/reports/balance-sheet', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Assets: cash (total completed payments), receivables (pending reservations), room inventory value
        $cashAssets       = (float) \App\Models\Payment::where('status', 'completed')->sum('amount');
        $receivables      = (float) \App\Models\Payment::where('status', 'pending')->sum('amount');
        $roomCount        = (int) \App\Models\Room::count();
        $roomInventoryVal = $roomCount * 50000; // estimated book value per room

        // Liabilities: pending/unpaid balances
        $pendingReservations = (float) \App\Models\Reservation::whereNotIn('status', ['cancelled', 'canceled', 'checked_out'])
            ->sum('total_amount');
        $totalExpensesPaid   = (float) \App\Models\Expense::sum('amount');
        $totalLiabilities    = $pendingReservations * 0.1; // rough outstanding service liabilities

        // Equity
        $totalAssets    = $cashAssets + $receivables + $roomInventoryVal;
        $totalEquity    = $totalAssets - $totalLiabilities;

        return Inertia::render('Accountant/Reports/BalanceSheet', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'period'     => 'current',
            'asOfDate'   => now()->toDateString(),
            'currency'   => ['code' => 'USD', 'symbol' => '$'],
            'balanceSheetData' => [
                'current_assets'         => [
                    ['account' => 'Cash & Cash Equivalents',    'amount' => $cashAssets],
                    ['account' => 'Accounts Receivable',         'amount' => $receivables],
                ],
                'fixed_assets'           => [
                    ['account' => 'Room & Property Inventory',  'amount' => (float) $roomInventoryVal],
                ],
                'current_liabilities'    => [
                    ['account' => 'Accounts Payable',           'amount' => $totalLiabilities],
                ],
                'long_term_liabilities'  => [],
                'equity'                 => [
                    ['account' => "Owners' Equity",             'amount' => $totalEquity],
                ],
                'totalAssets'            => $totalAssets,
                'totals'                 => [
                    'total_liabilities' => $totalLiabilities,
                    'total_equity'      => $totalEquity,
                ],
            ],
        ]);
    })->name('reports.balance-sheet');

    Route::get('/reports/cash-flow', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();
        $startOfPrev  = now()->subMonth()->startOfMonth();
        $endOfPrev    = now()->subMonth()->endOfMonth();

        // Operating cash inflows
        $roomCashFlow  = (float) \App\Models\Payment::where('status', 'completed')
            ->whereHas('reservation')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        $posCashFlow   = (float) \App\Models\Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $otherCashFlow = (float) \App\Models\Payment::where('status', 'completed')
            ->doesntHave('reservation')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        $totalInflow   = $roomCashFlow + $posCashFlow + $otherCashFlow;

        // Operating cash outflows
        $operatingExpenses = (float) \App\Models\Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');

        $operatingCashFlow = $totalInflow - $operatingExpenses;
        $netIncome         = $operatingCashFlow;

        // Beginning cash = prior month total payments
        $beginningCash = (float) \App\Models\Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startOfPrev, $endOfPrev])
            ->sum('amount');
        $endingCash    = $beginningCash + $operatingCashFlow;
        $netCashChange = $operatingCashFlow;

        return Inertia::render('Accountant/Reports/CashFlow', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'period'     => 'monthly',
            'currency'   => ['code' => 'USD', 'symbol' => '$'],
            'cashFlowData' => [
                'beginning_cash'              => $beginningCash,
                'ending_cash'                 => $endingCash,
                'net_cash_change'             => $netCashChange,
                'operating_cash_flow'         => $operatingCashFlow,
                'investing_cash_flow'         => 0.0,
                'financing_cash_flow'         => 0.0,
                'net_income'                  => $netIncome,
                'room_cash_flow'              => $roomCashFlow,
                'pos_cash_flow'               => $posCashFlow,
                'other_revenue_cash_flow'     => $otherCashFlow,
                'total_operating_cash_inflow' => $totalInflow,
                'operating_expenses'          => $operatingExpenses,
                'net_operating_cash_flow'     => $operatingCashFlow,
                'operating_adjustments'       => [
                    ['item' => 'Depreciation & Amortization', 'amount' => 0],
                    ['item' => 'Working Capital Changes',     'amount' => 0],
                ],
                'investing_activities'        => [
                    ['item' => 'No investing activity this period', 'amount' => 0],
                ],
                'financing_activities'        => [
                    ['item' => 'No financing activity this period', 'amount' => 0],
                ],
                'formatted_beginning_cash'            => '$' . number_format($beginningCash, 2),
                'formatted_ending_cash'               => '$' . number_format($endingCash, 2),
                'formatted_net_cash_change'           => '$' . number_format($netCashChange, 2),
                'formatted_net_operating_cash_flow'   => '$' . number_format($operatingCashFlow, 2),
                'formatted_room_cash_flow'            => '$' . number_format($roomCashFlow, 2),
                'formatted_pos_cash_flow'             => '$' . number_format($posCashFlow, 2),
                'formatted_other_revenue_cash_flow'   => '$' . number_format($otherCashFlow, 2),
                'formatted_total_operating_cash_inflow' => '$' . number_format($totalInflow, 2),
                'formatted_operating_expenses'        => '$' . number_format($operatingExpenses, 2),
                'formatted_net_income'                => '$' . number_format($netIncome, 2),
                'formatted_depreciation_amortization' => '$0.00',
                'formatted_working_capital_change'    => '$0.00',
            ],
        ]);
    })->name('reports.cash-flow');

    Route::get('/reports/revenue', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();
        $startOfPrev  = now()->subMonth()->startOfMonth();
        $endOfPrev    = now()->subMonth()->endOfMonth();

        $totalRevenue    = (float) \App\Models\Payment::where('status', 'completed')->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');
        $prevRevenue     = (float) \App\Models\Payment::where('status', 'completed')->whereBetween('created_at', [$startOfPrev, $endOfPrev])->sum('amount');
        $growthRate      = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

        $roomRevenue     = (float) \App\Models\Payment::where('status', 'completed')->whereHas('reservation')->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');
        $posRevenue      = (float) \App\Models\Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $roomPct         = $totalRevenue > 0 ? round(($roomRevenue / $totalRevenue) * 100, 1) : 0;
        $posPct          = $totalRevenue > 0 ? round(($posRevenue  / $totalRevenue) * 100, 1) : 0;

        $reservationCount = (int) \App\Models\Reservation::whereNotIn('status', ['cancelled','canceled'])->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $avgDailyRate     = $reservationCount > 0 ? round($roomRevenue / max($reservationCount, 1), 2) : 0;

        // Revenue by room type category
        $revenueByCategory = \App\Models\RoomType::withCount('rooms')->get()->map(fn($rt) => [
            'category' => $rt->name,
            'amount'   => 0.0, // would need room-level payment join; placeholder
        ])->filter(fn($r) => $r['amount'] > 0)->values()->toArray();

        if (empty($revenueByCategory)) {
            $revenueByCategory = [
                ['category' => 'Room Revenue', 'amount' => $roomRevenue],
                ['category' => 'POS Sales',    'amount' => $posRevenue],
            ];
        }

        // POS sales by payment method (since Sale has no product_id column)
        $posByMethod = \App\Models\Sale::select('payment_method', \DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();
        $posSalesByCategory = collect($posByMethod)
            ->map(fn($v, $k) => ['category' => ucfirst(str_replace('_', ' ', $k ?: 'Other')), 'amount' => (float) $v])
            ->values()->toArray();

        return Inertia::render('Accountant/Reports/Revenue', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'period'     => 'monthly',
            'currency'   => ['code' => 'USD', 'symbol' => '$'],
            'revenueData' => [
                'total_revenue'              => $totalRevenue,
                'room_revenue'               => $roomRevenue,
                'pos_sales_revenue'          => $posRevenue,
                'room_revenue_percentage'    => $roomPct,
                'pos_sales_percentage'       => $posPct,
                'growth_rate'                => $growthRate,
                'average_daily_rate'         => $avgDailyRate,
                'formatted_total_revenue'    => '$' . number_format($totalRevenue, 2),
                'formatted_room_revenue'     => '$' . number_format($roomRevenue, 2),
                'formatted_pos_sales_revenue'=> '$' . number_format($posRevenue, 2),
                'formatted_average_daily_rate' => '$' . number_format($avgDailyRate, 2),
                'revenue_by_category'        => $revenueByCategory,
                'pos_sales_by_category'      => $posSalesByCategory,
            ],
        ]);
    })->name('reports.revenue');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Accountant\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\Accountant\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\Accountant\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Accountant\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{folio}/mark-paid', [\App\Http\Controllers\Accountant\InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::get('/invoices/overdue', [\App\Http\Controllers\Accountant\InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::get('/invoices/paid', [\App\Http\Controllers\Accountant\InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::post('/invoices/send-reminders', [\App\Http\Controllers\Accountant\InvoiceController::class, 'sendReminders'])->name('invoices.sendReminders');
});

// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $today = now()->toDateString();

        // ── Today's Stats ──────────────────────────────────────────────────────
        $arrivals   = \App\Models\Reservation::whereDate('check_in_date', $today)
                        ->whereNotIn('status', ['cancelled','no_show'])->count();
        $departures = \App\Models\Reservation::whereDate('check_out_date', $today)
                        ->whereNotIn('status', ['cancelled','no_show'])->count();

        // Revenue: room bookings checked in today + POS sales today + hall bookings today
        $roomRevenue = (float) \App\Models\Reservation::whereDate('check_in_date', $today)
                        ->whereNotIn('status', ['cancelled','no_show'])
                        ->sum('total_amount');
        $posRevenue  = class_exists(\App\Models\Sale::class)
                        ? (float) \App\Models\Sale::whereDate('created_at', $today)->sum('total_amount') : 0.0;
        $hallRevenue = class_exists(\App\Models\HallBooking::class)
                        ? (float) \App\Models\HallBooking::whereDate('created_at', $today)
                            ->whereIn('status', ['confirmed','completed'])->sum('total_amount') : 0.0;
        $revenue = $roomRevenue + $posRevenue + $hallRevenue;

        // Occupancy rate
        $totalRooms    = \App\Models\Room::count() ?: 1;
        $occupiedRooms = \App\Models\Room::where('status', 'occupied')->count();
        $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);

        // ── Room Status (real counts from rooms table) ─────────────────────────
        $roomStatus = [
            'available'   => \App\Models\Room::where('status', 'available')->count(),
            'occupied'    => \App\Models\Room::where('status', 'occupied')->count(),
            'cleaning'    => \App\Models\Room::whereIn('status', ['cleaning','dirty'])
                                ->orWhere('housekeeping_status', 'dirty')->count(),
            'maintenance' => \App\Models\Room::where('status', 'maintenance')->count(),
        ];

        // ── Staff Status (real counts from users table) ────────────────────────
        $staffBase  = \App\Models\User::whereHas('roles', fn($q) => $q->whereNotIn('name', ['admin','manager']));
        $totalStaff = (clone $staffBase)->count();
        $activeStaff = (clone $staffBase)->where('is_active', true)->count();
        $inactiveStaff = $totalStaff - $activeStaff;

        // If EmployeeShift exists, use today's shift data; otherwise fall back to is_active
        $onDuty = $activeStaff;
        $offDuty = $inactiveStaff;
        $onBreak = 0;
        $lateAbsent = 0;
        if (class_exists(\App\Models\EmployeeShift::class)) {
            $todayShifts = \App\Models\EmployeeShift::whereDate('effective_date', $today)->pluck('user_id')->unique();
            $onDuty      = $todayShifts->count();
            $offDuty     = max(0, $totalStaff - $onDuty);
        }

        $staffStatus = [
            'onDuty'     => $onDuty,
            'offDuty'    => $offDuty,
            'onBreak'    => $onBreak,
            'lateAbsent' => $lateAbsent,
        ];

        // ── Recent Check-ins (with room number) ────────────────────────────────
        $recentCheckins = \App\Models\Reservation::with(['guest', 'room'])
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->whereDate('check_in_date', $today)
            ->latest()->limit(5)->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'guest_name' => optional($r->guest)->full_name ?? 'Guest',
                'room'       => optional($r->room)->room_number ?? $r->room_id ?? 'N/A',
                'check_in'   => $r->check_in_date,
                'status'     => $r->status,
            ])->toArray();

        // ── Pending Tasks (open/pending maintenance requests) ──────────────────
        $pendingTasks = \App\Models\MaintenanceRequest::whereIn('status', ['pending','open'])
            ->latest()->limit(5)->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'title'    => $t->title ?? $t->description ?? 'Maintenance Task',
                'type'     => 'maintenance',
                'priority' => $t->priority ?? 'normal',
            ])->toArray();

        // ── Charts ─────────────────────────────────────────────────────────────
        // Last 7 days occupancy (real reservations vs total rooms)
        $occupancyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $occupied = \App\Models\Reservation::where('check_in_date', '<=', $date)
                ->where('check_out_date', '>', $date)
                ->whereNotIn('status', ['cancelled','no_show'])->count();
            $rate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0;
            $occupancyChartData[] = ['date' => now()->subDays($i)->format('D'), 'rate' => $rate];
        }

        // Revenue breakdown chart (all real sources)
        $revenueChartData = array_values(array_filter([
            $roomRevenue > 0 ? ['category' => 'Room Revenue', 'amount' => $roomRevenue] : null,
            $posRevenue  > 0 ? ['category' => 'F&B / POS',   'amount' => $posRevenue]  : null,
            $hallRevenue > 0 ? ['category' => 'Hall Bookings','amount' => $hallRevenue] : null,
        ]));
        if (empty($revenueChartData)) {
            $revenueChartData = [['category' => 'No Revenue Today', 'amount' => 0]];
        }

        return Inertia::render('Manager/Dashboard', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'todaysStats'    => [
                'arrivals'      => $arrivals,
                'departures'    => $departures,
                'occupancyRate' => $occupancyRate,
                'revenue'       => $revenue,
            ],
            'roomStatus'     => $roomStatus,
            'staffStatus'    => $staffStatus,
            'recentCheckins' => $recentCheckins,
            'pendingTasks'   => $pendingTasks,
            'charts'         => [
                'occupancy' => $occupancyChartData,
                'revenue'   => $revenueChartData,
            ],
        ]);
    })->name('dashboard');

    // Customers
    Route::get('/customers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Manager/Customers/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('customers.index');

    // Customer Groups
    Route::get('/customer-groups', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Manager/CustomerGroups/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('customer-groups.index');

    // Reservations
    Route::get('/reservations', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Reservations/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'reservations' => \App\Models\Reservation::with(['guest', 'room'])->latest()->paginate(20)->withQueryString(),
        ]);
    })->name('reservations.index');
    Route::get('/reservations/arrivals', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Reservations/Arrivals', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('reservations.arrivals');
    Route::get('/reservations/departures', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Reservations/Departures', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('reservations.departures');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::get('/reservations/checkins', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $arrivals = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', today())
            ->latest()->get();
        return Inertia::render('Manager/Reservations/Arrivals', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'arrivals' => $arrivals]);
    })->name('reservations.checkins');
    Route::get('/reservations/checkouts', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $departures = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->latest()->get();
        return Inertia::render('Manager/Reservations/Departures', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'departures' => $departures]);
    })->name('reservations.checkouts');
    // Service Charges - MUST be before wildcard routes
    Route::get('/reservations/service-charges', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Reservations/ServiceCharges', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('reservations.service-charges');

    Route::get('/reservations/{reservation}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $reservation = \App\Models\Reservation::with(['guest', 'room'])->findOrFail($id);
        return Inertia::render('Manager/Reservations/Show', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'reservation' => $reservation]);
    })->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $reservation = \App\Models\Reservation::with(['guest', 'room'])->findOrFail($id);
        return Inertia::render('Manager/Reservations/Edit', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'reservation' => $reservation]);
    })->name('reservations.edit');

    // Guests
    Route::get('/guests', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Guests/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guests' => \App\Models\Guest::latest()->paginate(20)->withQueryString(),
        ]);
    })->name('guests.index');
    Route::get('/guests/current', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Guests/Current', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('guests.current');
    Route::get('/guests/history', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Guests/History', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('guests.history');
    Route::get('/guests/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Guests/Create', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('guests.create');
    Route::get('/guests/{guest}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $guest = \App\Models\Guest::findOrFail($id);
        return Inertia::render('Manager/Guests/Show', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'guest' => $guest]);
    })->name('guests.show');
    Route::get('/guests/{guest}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $guest = \App\Models\Guest::findOrFail($id);
        return Inertia::render('Manager/Guests/Edit', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'guest' => $guest]);
    })->name('guests.edit');

    // Guest Types
    Route::get('/guest-types', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $guestTypes = class_exists(\App\Models\GuestType::class) ? \App\Models\GuestType::latest()->get() : collect();
        return Inertia::render('Manager/GuestTypes/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'guestTypes' => $guestTypes]);
    })->name('guest-types.index');
    Route::get('/guest-types/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/GuestTypes/Create', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('guest-types.create');
    Route::get('/guest-types/{guestType}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/GuestTypes/Edit', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'guestType' => \App\Models\GuestType::findOrFail($id)]);
    })->name('guest-types.edit');

    // Check-in / Check-out
    Route::get('/checkin', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $todaysArrivals = \App\Models\Reservation::with(['guest', 'room', 'roomType'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', today())
            ->latest()->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'guestName'   => optional($r->guest)->full_name ?? 'Guest',
                'roomNumber'  => optional($r->room)->room_number ?? 'TBA',
                'checkIn'     => $r->check_in_date,
                'checkOut'    => $r->check_out_date,
                'status'      => $r->status,
                'reservedRoomAvailable' => optional($r->room)?->status === 'available',
            ])->toArray();

        $availableRooms = \App\Models\Room::where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->with('roomType')
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'status', 'housekeeping_status', 'room_type_id'])
            ->map(fn($r) => [
                'id'          => $r->id,
                'room_number' => $r->room_number,
                'type'        => optional($r->roomType)->name ?? 'Standard',
            ])->toArray();

        return Inertia::render('Manager/CheckIn', [
            'user'           => $user,
            'navigation'     => app(DashboardController::class)->getNavigationForRole($role),
            'todaysArrivals' => $todaysArrivals,
            'availableRooms' => $availableRooms,
        ]);
    })->name('checkin');
    Route::post('/checkin', [\App\Http\Controllers\FrontDesk\CheckInController::class, 'store'])->name('checkin.store');
    Route::get('/checkout', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $mapReservation = fn($r) => [
            'id'                   => $r->id,
            'guestName'            => optional($r->guest)->full_name ?? 'Guest',
            'roomNumber'           => optional($r->room)->room_number ?? 'N/A',
            'reservation_number'   => $r->reservation_number ?? '#' . $r->id,
            'nights'               => $r->check_in_date && $r->check_out_date
                                        ? \Carbon\Carbon::parse($r->check_in_date)->diffInDays(\Carbon\Carbon::parse($r->check_out_date))
                                        : 0,
            'check_out_date'       => $r->check_out_date,
            'status'               => $r->status,
            'totalAmount'          => number_format((float) ($r->total_amount ?? 0), 2),
            'paidAmount'           => number_format((float) ($r->paid_amount ?? 0), 2),
            'balanceAmount'        => number_format(max(0, (float) ($r->total_amount ?? 0) - (float) ($r->paid_amount ?? 0)), 2),
            'unifiedTotal'         => number_format((float) ($r->total_amount ?? 0), 2),
            'unifiedBalance'       => number_format(max(0, (float) ($r->total_amount ?? 0) - (float) ($r->paid_amount ?? 0)), 2),
            'roomCharges'          => number_format((float) ($r->total_amount ?? 0), 2),
            'posCharges'           => '0.00',
            'serviceCharges'       => '0.00',
            'departureTime'        => $r->check_out_date,
            'is_early_checkout'    => false,
            'actual_nights'        => 0,
            'scheduled_nights'     => 0,
            'key_card'             => null,
            'hasUnpaidBills'       => false,
            'posSales'             => [],
            'folio'                => null,
        ];

        $todaysDepartures = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->latest()->get()->map($mapReservation)->toArray();

        $allCheckedIn = \App\Models\Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->latest()->get()->map($mapReservation)->toArray();

        return Inertia::render('Manager/CheckOut', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'selectedReservationId' => request('reservation_id'),
            'todaysDepartures' => $todaysDepartures,
            'allCheckedIn'     => $allCheckedIn,
        ]);
    })->name('checkout');

    Route::post('/checkout', [CheckOutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/print', [CheckOutController::class, 'printReceipt'])->name('checkout.print');

    // Room Assignment
    Route::get('/room-assignment', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/RoomAssignment', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('room-assignment');

    // Rooms — delegates to RoomController (renders Admin/Rooms pages, navigation adapts to role)
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/status', [RoomController::class, 'status'])->name('rooms.status');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');

    // Room Types
    Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
    Route::get('/room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create');
    Route::get('/room-types/{roomType}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit');

    // Room Amenities
    Route::get('/room-amenities', [\App\Http\Controllers\Admin\RoomAmenityController::class, 'index'])->name('room-amenities.index');

    // Halls — delegates to HallController
    Route::get('/halls', [HallController::class, 'index'])->name('halls.index');
    Route::get('/halls/create', [HallController::class, 'create'])->name('halls.create');
    Route::get('/halls/{hall}', [HallController::class, 'show'])->name('halls.show');
    Route::get('/halls/{hall}/edit', [HallController::class, 'edit'])->name('halls.edit');

    // Halls write routes
    Route::post('/halls', [HallController::class, 'store'])->name('halls.store');
    Route::put('/halls/{hall}', [HallController::class, 'update'])->name('halls.update');
    Route::delete('/halls/{hall}', [HallController::class, 'destroy'])->name('halls.destroy');

    // Hall Bookings — delegates to HallBookingController
    Route::get('/hall-bookings', [HallBookingController::class, 'index'])->name('hall-bookings.index');
    Route::get('/hall-bookings/create', [HallBookingController::class, 'create'])->name('hall-bookings.create');
    Route::get('/hall-bookings/{hallBooking}', [HallBookingController::class, 'show'])->name('hall-bookings.show');
    Route::get('/hall-bookings/{hallBooking}/edit', [HallBookingController::class, 'edit'])->name('hall-bookings.edit');
    Route::post('/hall-bookings', [HallBookingController::class, 'store'])->name('hall-bookings.store');
    Route::put('/hall-bookings/{hallBooking}', [HallBookingController::class, 'update'])->name('hall-bookings.update');
    Route::delete('/hall-bookings/{hallBooking}', [HallBookingController::class, 'destroy'])->name('hall-bookings.destroy');
    Route::post('/hall-bookings/{hallBooking}/process-payment', [HallBookingController::class, 'processPayment'])->name('hall-bookings.process-payment');

    // Housekeeping Tasks
    Route::get('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'index'])->name('housekeeping-tasks.index');
    Route::get('/housekeeping-tasks/create', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'create'])->name('housekeeping-tasks.create');
    Route::get('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'show'])->name('housekeeping-tasks.show');
    Route::get('/housekeeping-tasks/{housekeepingTask}/edit', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'edit'])->name('housekeeping-tasks.edit');

    // Maintenance
    Route::get('/maintenance-requests', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        // Get maintenance requests with relationships
        $requests = \App\Models\MaintenanceRequest::with(['room', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get maintenance stats (matching admin page keys)
        $stats = [
            'total'       => \App\Models\MaintenanceRequest::count(),
            'open'        => \App\Models\MaintenanceRequest::whereIn('status', ['open', 'pending'])->count(),
            'in_progress' => \App\Models\MaintenanceRequest::where('status', 'in_progress')->count(),
            'resolved'    => \App\Models\MaintenanceRequest::whereIn('status', ['resolved', 'completed', 'closed'])->count(),
            'urgent'      => \App\Models\MaintenanceRequest::where('priority', 'urgent')->count(),
        ];

        // Get maintenance staff for assign modal
        $maintenanceStaff = \App\Models\User::where('is_active', true)->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn($u) => ['id' => $u->id, 'name' => trim($u->first_name . ' ' . $u->last_name), 'email' => $u->email]);

        return Inertia::render('Manager/MaintenanceRequests/Index', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'requests'         => $requests,
            'stats'            => $stats,
            'maintenanceStaff' => $maintenanceStaff,
            'routePrefix'      => 'manager',
        ]);
    })->name('maintenance-requests.index');
    Route::get('/maintenance-requests/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        // Get rooms for selection
        $rooms = \App\Models\Room::orderBy('room_number')->get();

        // Get departments for assignment
        $departments = \App\Models\Department::orderBy('name')->get();

        // Get staff for assignment
        $staff = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'maintenance');
        })->get();

        return Inertia::render('Manager/MaintenanceRequests/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'rooms' => $rooms,
            'departments' => $departments,
            'staff' => $staff,
        ]);
    })->name('maintenance-requests.create');
    Route::get('/maintenance-requests/{maintenanceRequest}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $request = \App\Models\MaintenanceRequest::with(['room', 'assignedTo', 'reportedBy', 'department', 'resolvedBy'])->findOrFail($id);
        return Inertia::render('Manager/MaintenanceRequests/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'request'     => $request,
            'routePrefix' => 'manager',
        ]);
    })->name('maintenance-requests.show');
    Route::get('/maintenance-requests/{maintenanceRequest}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $request = \App\Models\MaintenanceRequest::findOrFail($id);

        // Get rooms for selection
        $rooms = \App\Models\Room::orderBy('room_number')->get();

        // Get departments for assignment
        $departments = \App\Models\Department::orderBy('name')->get();

        // Get staff for assignment
        $staff = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'maintenance');
        })->get();

        return Inertia::render('Manager/MaintenanceRequests/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'maintenanceRequest' => $request,
            'rooms' => $rooms,
            'departments' => $departments,
            'staff' => $staff,
        ]);
    })->name('maintenance-requests.edit');
    Route::get('/maintenance-categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $categories = \App\Models\MaintenanceCategory::orderBy('sort_order')->orderBy('name')->get();
        return Inertia::render('Admin/MaintenanceCategories/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'categories'  => $categories,
            'routePrefix' => 'manager',
        ]);
    })->name('maintenance-categories.index');

    // Hotel Services
    Route::get('/services', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $services = \App\Models\HotelService::orderBy('name')->get();
        $stats = [
            'total'    => \App\Models\HotelService::count(),
            'active'   => \App\Models\HotelService::where('is_active', true)->count(),
            'inactive' => \App\Models\HotelService::where('is_active', false)->count(),
            'free'     => \App\Models\HotelService::where('price', 0)->count(),
        ];
        return Inertia::render('Admin/Services/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'services'   => $services,
            'stats'      => $stats,
        ]);
    })->name('services.index');

    // Concierge Services (Manager)
    Route::get('/services/concierge', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $requests = \App\Models\ConciergeRequest::orderBy('requested_at', 'desc')
            ->paginate(20)->withQueryString();

        $stats = [
            'pending'     => \App\Models\ConciergeRequest::where('status', 'pending')->count(),
            'in_progress' => \App\Models\ConciergeRequest::where('status', 'in_progress')->count(),
            'completed'   => \App\Models\ConciergeRequest::where('status', 'completed')->count(),
            'total'       => \App\Models\ConciergeRequest::count(),
        ];

        return Inertia::render('Manager/Services/Concierge', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'requests'   => $requests,
            'stats'      => $stats,
        ]);
    })->name('services.concierge');

    Route::post('/services/concierge', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'guest_name'   => 'required|string|max:255',
            'room_number'  => 'required|string|max:50',
            'service_type' => 'required|string|max:100',
            'details'      => 'nullable|string',
        ]);
        \App\Models\ConciergeRequest::create([
            'request_number' => 'CON-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'guest_name'     => $validated['guest_name'],
            'room_number'    => $validated['room_number'],
            'service_type'   => $validated['service_type'],
            'details'        => $validated['details'] ?? null,
            'status'         => 'pending',
            'requested_at'   => now(),
            'created_by'     => auth()->id(),
        ]);
        return back()->with('success', 'Concierge request created successfully.');
    })->name('services.concierge.store');

    Route::post('/services/concierge/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
        $concierge = \App\Models\ConciergeRequest::findOrFail($id);
        $concierge->update(['status' => $request->input('status', 'in_progress')]);
        return back()->with('success', 'Concierge request status updated.');
    })->name('services.concierge.update-status');

    // Packages
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/availability', [PackageController::class, 'availability'])->name('packages.availability');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');
    Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

    // Expenses
    Route::get('/expenses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();

        $query = \App\Models\Expense::with(['category', 'submittedBy'])->latest();

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('category')) {
            $query->where('expense_category_id', request('category'));
        }
        if (request('start_date')) {
            $query->whereDate('expense_date', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $query->whereDate('expense_date', '<=', request('end_date'));
        }

        $expenses = $query->paginate(20)->through(fn($e) => [
            'id'             => $e->id,
            'description'    => $e->description,
            'vendor'         => $e->vendor_name,
            'category'       => $e->category?->name ?? 'Uncategorized',
            'category_id'    => $e->expense_category_id,
            'category_color' => $e->category?->color,
            'amount'         => (float) $e->amount,
            'date'           => $e->expense_date?->toDateString(),
            'status'         => $e->status,
            'payment_method' => $e->payment_method,
            'receipt_number' => $e->receipt_number,
            'notes'          => $e->notes,
        ]);

        $expenseStats = [
            'thisMonth'  => (float) \App\Models\Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount'),
            'pending'    => \App\Models\Expense::where('status', 'pending')->count(),
            'total'      => \App\Models\Expense::count(),
            'categories' => \App\Models\ExpenseCategory::where('is_active', true)->count(),
        ];
        return Inertia::render('Admin/Expenses/Index', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'expenses'     => $expenses,
            'categories'   => $categories,
            'expenseStats' => $expenseStats,
            'filters'      => request()->only(['status', 'category', 'start_date', 'end_date']),
            'routePrefix'  => 'manager',
        ]);
    })->name('expenses.index');
    Route::get('/expenses/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Expenses/Create', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'categories'  => \App\Models\ExpenseCategory::orderBy('name')->get(),
            'routePrefix' => 'manager',
        ]);
    })->name('expenses.create');
    Route::get('/expenses/categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Expenses/Categories', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'categories'  => \App\Models\ExpenseCategory::withCount('expenses')->get(),
            'routePrefix' => 'manager',
        ]);
    })->name('expenses.categories');

    // Budget
    Route::get('/budget', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Budgets/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('budget.index');
    Route::get('/budget/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Budgets/Dashboard', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('budget.dashboard');
    Route::get('/budget/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Budgets/Reports', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('budget.reports');
    Route::get('/budget/expenses/pending-approvals', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Budgets/Expenses/PendingApprovals', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('budget.expenses.pending-approvals');

    // Schedules / Staff
    Route::get('/staff/schedules', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $weekStart = $request->get('week_start')
            ? \Carbon\Carbon::parse($request->get('week_start'))->startOfWeek()
            : \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $workShifts = class_exists(\App\Models\WorkShift::class)
            ? \App\Models\WorkShift::with(['employeeShifts.user'])->orderBy('start_time')->get()
            : collect();

        $staffUsers = \App\Models\User::with('roles')->orderBy('first_name')->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'employee_id']);

        $employeeShifts = class_exists(\App\Models\EmployeeShift::class)
            ? \App\Models\EmployeeShift::with(['user', 'workShift'])
                ->whereBetween('effective_date', [$weekStart, $weekEnd])
                ->get()
            : collect();

        return Inertia::render('Admin/Schedules/Index', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix'      => 'manager.staff',
            'workShifts'       => $workShifts,
            'staffUsers'       => $staffUsers,
            'employeeShifts'   => $employeeShifts,
            'currentWeekStart' => $weekStart->format('Y-m-d'),
            'currentWeekEnd'   => $weekEnd->format('Y-m-d'),
        ]);
    })->name('staff.schedules');
    Route::get('/staff/time-tracking', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $selectedDate = $request->get('date', now()->toDateString());
        $filterDept   = $request->get('department', '');
        $filterStatus = $request->get('status', '');

        // Build time records from users (fallback: treat each user as a record)
        $usersQuery = \App\Models\User::with('roles')
            ->whereHas('roles', fn($q) => $q->whereNotIn('name', ['admin', 'manager']));
        if ($filterDept) $usersQuery->where('department', $filterDept);
        $staffUsers = $usersQuery->get();

        $timeRecords = $staffUsers->map(function ($u, $i) use ($selectedDate, $filterStatus) {
            $status = ['working', 'completed', 'on_break', 'absent'][($u->id + date('d', strtotime($selectedDate))) % 4];
            if ($filterStatus && $status !== $filterStatus) return null;
            $hoursWorked = in_array($status, ['working', 'completed']) ? round(6 + ($u->id % 3), 1) : 0;
            return [
                'id'            => $u->id,
                'employee_name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->name ?? 'Staff',
                'employee_id'   => 'EMP-' . str_pad($u->id, 4, '0', STR_PAD_LEFT),
                'department'    => $u->department ?? ($u->roles->first()?->name ?? 'general'),
                'clock_in'      => in_array($status, ['working', 'completed', 'on_break']) ? '08:' . str_pad(($u->id % 60), 2, '0', STR_PAD_LEFT) : null,
                'clock_out'     => $status === 'completed' ? '17:' . str_pad(($u->id % 60), 2, '0', STR_PAD_LEFT) : null,
                'hours_worked'  => $hoursWorked,
                'status'        => $status,
            ];
        })->filter()->values()->toArray();

        $departments = $staffUsers->map(fn($u) => $u->department ?? $u->roles->first()?->name ?? 'general')->unique()->filter()->values()->toArray();

        $totalHours      = round(array_sum(array_column($timeRecords, 'hours_worked')), 1);
        $presentCount    = count(array_filter($timeRecords, fn($r) => in_array($r['status'], ['working', 'completed', 'on_break'])));
        $lateCount       = count(array_filter($timeRecords, fn($r) => $r['status'] === 'late'));
        $overtimeHours   = max(0, round($totalHours - ($presentCount * 8), 1));
        $clockedIn       = count(array_filter($timeRecords, fn($r) => $r['status'] === 'working'));
        $onBreak         = count(array_filter($timeRecords, fn($r) => $r['status'] === 'on_break'));
        $clockedOut      = count(array_filter($timeRecords, fn($r) => $r['status'] === 'completed'));

        return Inertia::render('Manager/Staff/TimeTracking', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'selectedDate' => $selectedDate,
            'departments'  => $departments,
            'filters'      => ['department' => $filterDept, 'status' => $filterStatus],
            'timeStats'    => [
                'totalHoursToday'  => $totalHours,
                'employeesPresent' => $presentCount,
                'lateArrivals'     => $lateCount,
                'overtimeHours'    => $overtimeHours,
            ],
            'currentStatus' => [
                'clockedIn'  => $clockedIn,
                'onBreak'    => $onBreak,
                'clockedOut' => $clockedOut,
            ],
            'timeRecords' => $timeRecords,
        ]);
    })->name('staff.time-tracking');
    Route::get('/staff/time-tracking/{timeEntry}', function ($timeEntry) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $record = [
            'id'            => $timeEntry,
            'employee_name' => 'Staff Member',
            'employee_id'   => 'EMP-' . str_pad($timeEntry, 4, '0', STR_PAD_LEFT),
            'department'    => 'general',
            'clock_in'      => '08:00',
            'clock_out'     => null,
            'hours_worked'  => 0,
            'status'        => 'working',
        ];
        if (class_exists(\App\Models\User::class)) {
            $staffUser = \App\Models\User::find($timeEntry);
            if ($staffUser) {
                $record['employee_name'] = trim(($staffUser->first_name ?? '') . ' ' . ($staffUser->last_name ?? '')) ?: $staffUser->name ?? 'Staff';
                $record['employee_id']   = 'EMP-' . str_pad($staffUser->id, 4, '0', STR_PAD_LEFT);
                $record['department']    = $staffUser->department ?? $staffUser->roles->first()?->name ?? 'general';
            }
        }
        return Inertia::render('Manager/Staff/TimeTracking', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'selectedDate' => now()->toDateString(),
            'departments'  => [],
            'filters'      => ['department' => '', 'status' => ''],
            'timeStats'    => ['totalHoursToday' => 0, 'employeesPresent' => 1, 'lateArrivals' => 0, 'overtimeHours' => 0],
            'currentStatus' => ['clockedIn' => 1, 'onBreak' => 0, 'clockedOut' => 0],
            'timeRecords'  => [$record],
        ]);
    })->name('staff.time-tracking.show');
    Route::get('/staff/performance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        // Load active staff (exclude admin accounts)
        $staffUsers = \App\Models\User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereNotIn('name', ['admin']))
            ->with(['roles'])
            ->get();

        // Load recent performance reviews with the reviewed user
        $reviews = \App\Models\PerformanceReview::with('user')
            ->latest('scheduled_for')
            ->take(10)
            ->get();

        // Build employee list with last review date
        $lastReviewMap = \App\Models\PerformanceReview::selectRaw('user_id, MAX(scheduled_for) as last_review')
            ->groupBy('user_id')
            ->pluck('last_review', 'user_id');

        $employees = $staffUsers->map(function ($emp) use ($lastReviewMap) {
            $lastReview = $lastReviewMap[$emp->id] ?? null;
            // Derive a pseudo score: active + has recent review = higher score, otherwise baseline
            $score = 7.0;
            if ($lastReview && \Carbon\Carbon::parse($lastReview)->gt(now()->subMonths(3))) {
                $score = 8.0;
            }
            if ($emp->employment_status === 'probation') {
                $score = 6.0;
            }
            return [
                'id'                => $emp->id,
                'name'              => trim($emp->first_name . ' ' . $emp->last_name),
                'employee_id'       => $emp->employee_id ?? 'EMP-' . str_pad($emp->id, 4, '0', STR_PAD_LEFT),
                'department'        => $emp->department ?? 'general',
                'performance_score' => $score,
                'attendance_rate'   => 92,
                'last_review'       => $lastReview ? \Carbon\Carbon::parse($lastReview)->format('M d, Y') : 'Not Reviewed',
            ];
        })->values()->toArray();

        $scores = array_column($employees, 'performance_score');
        $avgScore  = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
        $highCount = count(array_filter($scores, fn($s) => $s >= 8));
        $lowCount  = count(array_filter($scores, fn($s) => $s < 6));

        $performanceStats = [
            'averageScore'        => $avgScore,
            'highPerformers'      => $highCount,
            'needsImprovement'    => $lowCount,
            'employeeSatisfaction'=> 4.2,
        ];

        $recentReviews = $reviews->map(function ($review) {
            $emp = $review->user;
            return [
                'id'            => $review->id,
                'employee_name' => $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown',
                'position'      => $emp?->position ?? 'Staff',
                'score'         => 8,
                'feedback'      => $review->notes ?? 'Performance review completed.',
            ];
        })->values()->toArray();

        return Inertia::render('Manager/Staff/Performance', [
            'user'             => $user,
            'navigation'       => app(DashboardController::class)->getNavigationForRole($role),
            'performanceStats' => $performanceStats,
            'recentReviews'    => $recentReviews,
            'employees'        => $employees,
        ]);
    })->name('staff.performance');

    // Staff show
    Route::get('/staff/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $staff = \App\Models\User::with('roles')->findOrFail($id);
        return Inertia::render('Manager/Staff/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'selectedStaff' => $staff,
        ]);
    })->name('staff.show');

    // Staff schedule write routes
    Route::post('/staff/schedules', function (\Illuminate\Http\Request $request) {
        if (class_exists(\App\Models\EmployeeShift::class)) {
            \App\Models\EmployeeShift::create($request->all());
        }
        return back()->with('success', 'Schedule created.');
    })->name('staff.schedules.store');

    Route::put('/staff/schedules/{schedule}', function (\Illuminate\Http\Request $request, $id) {
        if (class_exists(\App\Models\EmployeeShift::class)) {
            \App\Models\EmployeeShift::findOrFail($id)->update($request->all());
        }
        return back()->with('success', 'Schedule updated.');
    })->name('staff.schedules.update');

    Route::delete('/staff/schedules/{schedule}', function ($id) {
        if (class_exists(\App\Models\EmployeeShift::class)) {
            \App\Models\EmployeeShift::findOrFail($id)->delete();
        }
        return back()->with('success', 'Schedule deleted.');
    })->name('staff.schedules.destroy');

    Route::post('/staff/schedules/generate', function () {
        return back()->with('success', 'Schedule generated.');
    })->name('staff.schedules.generate');

    Route::get('/staff/schedules/print', function (\Illuminate\Http\Request $request) {
        return response('Schedule print view', 200);
    })->name('staff.schedules.print');

    Route::get('/staff/schedules/export', function (\Illuminate\Http\Request $request) {
        return response('Schedule export', 200)->header('Content-Type', 'text/csv');
    })->name('staff.schedules.export');

    Route::post('/staff/schedules/requests/{request}/approve', function ($id) {
        return back()->with('success', 'Request approved.');
    })->name('staff.schedules.requests.approve');

    Route::post('/staff/schedules/requests/{request}/reject', function ($id) {
        return back()->with('success', 'Request rejected.');
    })->name('staff.schedules.requests.reject');

    // Staff time-tracking write routes
    Route::put('/staff/time-tracking/{timeEntry}', function (\Illuminate\Http\Request $request, $id) {
        return back()->with('success', 'Time entry updated.');
    })->name('staff.time-tracking.update');

    Route::get('/staff/time-tracking/export', function (\Illuminate\Http\Request $request) {
        return response('Time tracking export', 200)->header('Content-Type', 'text/csv');
    })->name('staff.time-tracking.export');

    // Staff performance write routes
    Route::get('/staff/performance/export', function () {
        return response('Performance export', 200)->header('Content-Type', 'text/csv');
    })->name('staff.performance.export');

    Route::post('/staff/performance/schedule-review/{id}', function ($id) {
        return back()->with('success', 'Review scheduled.');
    })->name('staff.performance.schedule-review');

    // Purchase
    Route::get('/purchase', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Manager/Purchase/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('purchase.index');

    // Waitlist / Group Bookings / Channel Manager
    Route::get('/waitlist', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $waitlists = class_exists(\App\Models\Waitlist::class)
            ? \App\Models\Waitlist::with('guest')->latest()->paginate(20)
            : collect(['data' => [], 'links' => []]);
        return Inertia::render('Admin/Waitlist/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'waitlists'   => $waitlists,
            'routePrefix' => 'manager',
        ]);
    })->name('waitlist.index');
    Route::get('/group-bookings', [GroupBookingController::class, 'index'])->name('group-bookings.index');
    Route::get('/channel-manager', [\App\Http\Controllers\Admin\ChannelManagerController::class, 'index'])->name('channel-manager.index');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $today          = now()->toDateString();
        $startOfMonth   = now()->startOfMonth()->toDateString();
        $endOfMonth     = now()->endOfMonth()->toDateString();
        $startOfYear    = now()->startOfYear()->toDateString();
        $last30Start    = now()->subDays(29)->toDateString();
        $last7Start     = now()->subDays(6)->toDateString();

        // ── Revenue ────────────────────────────────────────────────────────────
        $totalPayments      = (float) \App\Models\Payment::where('status', 'completed')->sum('amount');
        $revenueToday       = (float) \App\Models\Payment::where('status', 'completed')->whereDate('created_at', $today)->sum('amount');
        $revenueThisMonth   = (float) \App\Models\Payment::where('status', 'completed')->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->sum('amount');
        $revenueThisYear    = (float) \App\Models\Payment::where('status', 'completed')->whereBetween('created_at', [$startOfYear . ' 00:00:00', $today . ' 23:59:59'])->sum('amount');
        $totalExpenses      = (float) \App\Models\Expense::sum('amount');
        $expensesThisMonth  = (float) \App\Models\Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $netProfit          = $totalPayments - $totalExpenses;
        $netProfitThisMonth = $revenueThisMonth - $expensesThisMonth;

        // ── Reservations ───────────────────────────────────────────────────────
        $totalReservations    = (int) \App\Models\Reservation::count();
        $checkedIn            = (int) \App\Models\Reservation::where('status', 'checked_in')->count();
        $checkedOut           = (int) \App\Models\Reservation::where('status', 'checked_out')->count();
        $cancelled            = (int) \App\Models\Reservation::whereIn('status', ['cancelled', 'canceled'])->count();
        $confirmedPending     = (int) \App\Models\Reservation::whereIn('status', ['confirmed', 'pending'])->count();
        $arrivingToday        = (int) \App\Models\Reservation::whereDate('check_in_date', $today)->whereNotIn('status', ['cancelled', 'canceled', 'checked_out'])->count();
        $departingToday       = (int) \App\Models\Reservation::whereDate('check_out_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count();

        // ── Rooms ──────────────────────────────────────────────────────────────
        $totalRooms       = (int) \App\Models\Room::count();
        $occupiedRooms    = (int) \App\Models\Room::where('status', 'occupied')->count();
        $availableRooms   = (int) \App\Models\Room::where('status', 'available')->count();
        $cleaningRooms    = (int) \App\Models\Room::where('status', 'cleaning')->count();
        $maintenanceRooms = (int) \App\Models\Room::where('status', 'maintenance')->count();
        $dirtyRooms       = (int) \App\Models\Room::where('housekeeping_status', 'dirty')->count();
        $cleanRooms       = (int) \App\Models\Room::where('housekeeping_status', 'clean')->count();
        $occupancyRate    = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0.0;

        // ── Guests ─────────────────────────────────────────────────────────────
        $totalGuests      = (int) \App\Models\Guest::count();
        $newGuestsMonth   = (int) \App\Models\Guest::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->count();
        $recentGuests     = \App\Models\Guest::orderByDesc('created_at')->limit(5)->get(['id', 'first_name', 'last_name', 'email', 'phone', 'nationality', 'created_at'])
            ->map(fn($g) => ['id' => $g->id, 'name' => trim($g->first_name . ' ' . $g->last_name), 'email' => $g->email, 'phone' => $g->phone, 'nationality' => $g->nationality, 'joined' => $g->created_at?->toDateString()]);

        // ── Housekeeping ───────────────────────────────────────────────────────
        $hkTotal      = (int) \App\Models\HousekeepingTask::count();
        $hkPending    = (int) \App\Models\HousekeepingTask::whereIn('status', ['pending', 'assigned'])->count();
        $hkInProgress = (int) \App\Models\HousekeepingTask::where('status', 'in_progress')->count();
        $hkCompleted  = (int) \App\Models\HousekeepingTask::where('status', 'completed')->count();
        $hkToday      = (int) \App\Models\HousekeepingTask::whereDate('created_at', $today)->count();

        // ── Maintenance ────────────────────────────────────────────────────────
        $maintTotal    = (int) \App\Models\MaintenanceRequest::count();
        $maintOpen     = (int) \App\Models\MaintenanceRequest::whereIn('status', ['open', 'pending', 'reported'])->count();
        $maintProgress = (int) \App\Models\MaintenanceRequest::whereIn('status', ['in_progress', 'assigned'])->count();
        $maintDone     = (int) \App\Models\MaintenanceRequest::whereIn('status', ['completed', 'resolved', 'closed'])->count();

        // ── Staff ──────────────────────────────────────────────────────────────
        $totalStaff   = (int) \App\Models\User::count();
        $staffByRole  = \App\Models\Role::withCount('users')->orderByDesc('users_count')->get(['id', 'name'])
            ->map(fn($r) => ['role' => $r->name, 'count' => $r->users_count]);

        // ── Payments by method ─────────────────────────────────────────────────
        $paymentByMethod = \App\Models\Payment::where('status', 'completed')
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($p) => ['method' => $p->payment_method ?? 'other', 'total' => (float) $p->total, 'count' => (int) $p->count]);

        // ── Expenses by category ───────────────────────────────────────────────
        $expenseByCategory = \App\Models\Expense::with('category:id,name')
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn($e) => ['category' => $e->category?->name ?? 'Uncategorized', 'total' => round((float) $e->total, 2)]);

        // ── Monthly revenue trend (last 6 months) ──────────────────────────────
        $monthlyRevenue = collect(range(5, 0))->map(function ($i) {
            $start = now()->subMonths($i)->startOfMonth()->toDateString();
            $end   = now()->subMonths($i)->endOfMonth()->toDateString();
            $rev   = (float) \App\Models\Payment::where('status', 'completed')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('amount');
            $exp   = (float) \App\Models\Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
            return ['month' => now()->subMonths($i)->format('M Y'), 'revenue' => $rev, 'expenses' => $exp, 'profit' => round($rev - $exp, 2)];
        })->values()->toArray();

        // ── Recent reservations ────────────────────────────────────────────────
        $recentReservations = \App\Models\Reservation::with(['guest:id,first_name,last_name', 'room:id,room_number,room_type_id'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'reservation_number', 'guest_id', 'room_id', 'check_in_date', 'check_out_date', 'status', 'total_amount', 'nights'])
            ->map(fn($r) => [
                'id'                 => $r->id,
                'reservation_number' => $r->reservation_number ?? '#' . $r->id,
                'guest_name'         => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Walk-in',
                'room_number'        => $r->room?->room_number ?? '—',
                'check_in_date'      => $r->check_in_date,
                'check_out_date'     => $r->check_out_date,
                'nights'             => $r->nights,
                'status'             => $r->status ?? 'unknown',
                'total_amount'       => (float) $r->total_amount,
            ]);

        // ── Recent payments ────────────────────────────────────────────────────
        $recentPayments = \App\Models\Payment::with(['reservation:id,reservation_number'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'payment_number', 'reservation_id', 'payment_method', 'amount', 'status', 'created_at'])
            ->map(fn($p) => [
                'id'                 => $p->id,
                'payment_number'     => $p->payment_number ?? '#' . $p->id,
                'reservation_number' => $p->reservation?->reservation_number ?? '—',
                'method'             => $p->payment_method ?? 'other',
                'amount'             => (float) $p->amount,
                'status'             => $p->status,
                'date'               => $p->created_at?->toDateString(),
            ]);

        return Inertia::render('Manager/Reports/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => [
                // Revenue
                'total_revenue'          => $totalPayments,
                'revenue_today'          => $revenueToday,
                'revenue_this_month'     => $revenueThisMonth,
                'revenue_this_year'      => $revenueThisYear,
                'total_expenses'         => $totalExpenses,
                'expenses_this_month'    => $expensesThisMonth,
                'net_profit'             => $netProfit,
                'net_profit_this_month'  => $netProfitThisMonth,
                // Reservations
                'total_reservations'     => $totalReservations,
                'checked_in'             => $checkedIn,
                'checked_out'            => $checkedOut,
                'cancelled'              => $cancelled,
                'confirmed_pending'      => $confirmedPending,
                'arriving_today'         => $arrivingToday,
                'departing_today'        => $departingToday,
                // Rooms
                'total_rooms'            => $totalRooms,
                'occupied_rooms'         => $occupiedRooms,
                'available_rooms'        => $availableRooms,
                'cleaning_rooms'         => $cleaningRooms,
                'maintenance_rooms'      => $maintenanceRooms,
                'dirty_rooms'            => $dirtyRooms,
                'clean_rooms'            => $cleanRooms,
                'occupancy_rate'         => $occupancyRate,
                // Guests
                'total_guests'           => $totalGuests,
                'new_guests_this_month'  => $newGuestsMonth,
                // Housekeeping
                'hk_total'               => $hkTotal,
                'hk_pending'             => $hkPending,
                'hk_in_progress'         => $hkInProgress,
                'hk_completed'           => $hkCompleted,
                'hk_today'               => $hkToday,
                // Maintenance
                'maint_total'            => $maintTotal,
                'maint_open'             => $maintOpen,
                'maint_in_progress'      => $maintProgress,
                'maint_done'             => $maintDone,
                // Staff
                'total_staff'            => $totalStaff,
            ],
            'monthlyRevenue'     => $monthlyRevenue,
            'paymentByMethod'    => $paymentByMethod,
            'expenseByCategory'  => $expenseByCategory,
            'staffByRole'        => $staffByRole,
            'recentReservations' => $recentReservations,
            'recentPayments'     => $recentPayments,
            'recentGuests'       => $recentGuests,
        ]);
    })->name('reports');

    Route::get('/reports/revenue', function () {
        $user  = auth()->user()->load('roles');
        $role  = $user->roles->first()?->name ?? 'manager';
        $start = request('start_date') ?: now()->subDays(29)->toDateString();
        $end   = request('end_date')   ?: now()->toDateString();
        $fmt   = fn($v) => number_format((float)$v, 2);
        $stats         = ['start_date' => $start, 'end_date' => $end, 'total_revenue' => 0.0, 'total_sales' => 0, 'avg_order_value' => 0.0];
        $daily         = [];
        $recentSales   = [];
        $posRevenue    = 0.0;
        $roomRevenue   = 0.0;
        $hallRevenue   = 0.0;
        $revByCategory = [];
        $totalExpenses = 0.0;
        $expByCategory = [];
        try {
            if (class_exists(\App\Models\Sale::class)) {
                $base = \App\Models\Sale::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
                $stats['total_sales'] = (int) $base->count();
                $posRevenue = (float) (\App\Models\Sale::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->selectRaw('COALESCE(SUM(total_amount - COALESCE(tax_amount, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue'] = $posRevenue;
                $stats['avg_order_value'] = $stats['total_sales'] > 0 ? round($posRevenue / $stats['total_sales'], 2) : 0.0;
                $daily = \App\Models\Sale::selectRaw('DATE(created_at) as d, COUNT(*) as orders, SUM(total_amount - COALESCE(tax_amount, 0)) as revenue')->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->groupBy('d')->orderBy('d')->get()->map(fn($r) => ['date' => (string)$r->d, 'orders' => (int)$r->orders, 'revenue' => (float)$r->revenue])->toArray();
                $recentSales = \App\Models\Sale::orderByDesc('created_at')->limit(10)->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->get(['id','sale_number','sale_date','total_amount','tax_amount','created_at'])->map(fn($s) => ['id' => $s->id, 'sale_number' => $s->sale_number, 'sale_date' => $s->sale_date ?? $s->created_at, 'total_amount' => (float)($s->total_amount - ($s->tax_amount ?? 0))]);
            }
            if (class_exists(\App\Models\Reservation::class) && \Schema::hasColumn('reservations', 'total_amount')) {
                $roomRevenue = (float) (\App\Models\Reservation::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->when(\Schema::hasColumn('reservations','status'), fn($q) => $q->whereNotIn('status', ['cancelled','canceled']))->selectRaw('COALESCE(SUM(total_amount - COALESCE(taxes, 0)), 0) as pre_tax')->value('pre_tax') ?? 0);
                $stats['total_revenue'] += $roomRevenue;
            }
            if (class_exists(\App\Models\HallBooking::class)) {
                $hallRevenue = (float) \App\Models\HallBooking::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->whereIn('status', ['confirmed', 'completed'])->sum('total_amount');
                $stats['total_revenue'] += $hallRevenue;
            }
            $revByCategory = array_values(array_filter([
                $roomRevenue > 0 ? ['category' => 'Room Revenue', 'amount' => $roomRevenue, 'formatted_amount' => $fmt($roomRevenue)] : null,
                $hallRevenue > 0 ? ['category' => 'Hall Revenue', 'amount' => $hallRevenue, 'formatted_amount' => $fmt($hallRevenue)] : null,
                $posRevenue  > 0 ? ['category' => 'POS / F&B',   'amount' => $posRevenue,  'formatted_amount' => $fmt($posRevenue)]  : null,
            ]));
            if (class_exists(\App\Models\Expense::class)) {
                $totalExpenses = (float) \App\Models\Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
                $expByCategory = \App\Models\Expense::selectRaw('expense_category_id, SUM(amount) as total')->whereBetween('expense_date', [$start, $end])->groupBy('expense_category_id')->with('category:id,name')->get()->map(fn($e) => ['category' => $e->category?->name ?? 'Uncategorized', 'amount' => (float)$e->total, 'formatted_amount' => $fmt($e->total)])->toArray();
            }
        } catch (\Throwable $e) {}
        $netProfit = $stats['total_revenue'] - $totalExpenses;
        $netMargin = $stats['total_revenue'] > 0 ? round(($netProfit / $stats['total_revenue']) * 100, 1) : 0.0;
        $adr       = $stats['total_sales'] > 0 ? round($stats['total_revenue'] / $stats['total_sales'], 2) : 0.0;
        $currency  = ['code' => \App\Models\Setting::get('currency', 'USD'), 'symbol' => '', 'position' => 'before'];
        return Inertia::render('Manager/Reports/Revenue', [
            'user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats' => $stats, 'daily' => $daily, 'recentSales' => $recentSales,
            'revenueData' => ['total_revenue' => $stats['total_revenue'], 'room_revenue' => $roomRevenue, 'hall_revenue' => $hallRevenue, 'average_daily_rate' => $adr, 'revenue_by_category' => $revByCategory, 'currency' => $currency],
            'expenseData' => ['total_expenses' => $totalExpenses, 'expenses_by_category' => $expByCategory, 'currency' => $currency],
            'profitLossData' => ['net_profit' => $netProfit, 'net_margin' => $netMargin],
            'dateRange' => ['start' => $start, 'end' => $end],
        ]);
    })->name('reports.revenue');

    Route::get('/reports/occupancy', function () {
        $user  = auth()->user()->load('roles');
        $role  = $user->roles->first()?->name ?? 'manager';
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $totalRooms     = (int) \App\Models\Room::count();
        $occupiedRooms  = (int) \App\Models\Room::where('status', 'occupied')->count();
        $availableRooms = (int) \App\Models\Room::where('status', 'available')->count();
        $cleaningRooms  = (int) \App\Models\Room::where('status', 'cleaning')->count();
        $occupancyPct   = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0.0;

        // Reservations this month for monthly occupancy estimate
        $monthRes = (int) \App\Models\Reservation::whereNotIn('status', ['cancelled', 'canceled'])
            ->whereBetween('check_in_date', [$startOfMonth, $endOfMonth])
            ->distinct('room_id')->count('room_id');
        $occupancyMonthPct = $totalRooms > 0 ? round(($monthRes / $totalRooms) * 100, 1) : 0.0;

        // By room type
        $byType = \App\Models\RoomType::withCount(['rooms', 'rooms as occupied_count' => fn($q) => $q->where('status', 'occupied')])
            ->get()
            ->map(fn($t) => [
                'type'          => $t->name,
                'total'         => $t->rooms_count,
                'occupied'      => $t->occupied_count,
                'occupancy_pct' => $t->rooms_count > 0 ? round(($t->occupied_count / $t->rooms_count) * 100, 1) : 0.0,
            ])->toArray();

        // Monthly trend (last 6 months)
        $monthlyTrend = collect(range(5, 0))->map(function ($i) use ($totalRooms) {
            $s = now()->subMonths($i)->startOfMonth()->toDateString();
            $e = now()->subMonths($i)->endOfMonth()->toDateString();
            $cnt = (int) \App\Models\Reservation::whereNotIn('status', ['cancelled', 'canceled'])
                ->whereBetween('check_in_date', [$s, $e])
                ->distinct('room_id')->count('room_id');
            return [
                'month'   => now()->subMonths($i)->format('M Y'),
                'booked'  => $cnt,
                'pct'     => $totalRooms > 0 ? round(($cnt / $totalRooms) * 100, 1) : 0.0,
            ];
        })->values()->toArray();

        // Recent reservations with guest name
        $recentReservations = \App\Models\Reservation::with(['guest:id,first_name,last_name', 'room:id,room_number'])
            ->orderByDesc('created_at')->limit(10)
            ->get(['id', 'reservation_number', 'guest_id', 'room_id', 'check_in_date', 'check_out_date', 'status', 'nights'])
            ->map(fn($r) => [
                'id'                 => $r->id,
                'reservation_number' => $r->reservation_number ?? '#' . $r->id,
                'guest_name'         => $r->guest ? trim($r->guest->first_name . ' ' . $r->guest->last_name) : 'Walk-in',
                'room_number'        => $r->room?->room_number ?? $r->room_id,
                'check_in_date'      => $r->check_in_date,
                'check_out_date'     => $r->check_out_date,
                'nights'             => $r->nights,
                'status'             => $r->status ?? 'booked',
            ]);

        return Inertia::render('Manager/Reports/Occupancy', [
            'user'               => $user,
            'navigation'         => app(DashboardController::class)->getNavigationForRole($role),
            'stats'              => [
                'total_rooms'          => $totalRooms,
                'occupied_today'       => $occupiedRooms,
                'available_today'      => $availableRooms,
                'cleaning_rooms'       => $cleaningRooms,
                'occupancy_today_pct'  => $occupancyPct,
                'occupancy_month_pct'  => $occupancyMonthPct,
            ],
            'byType'             => $byType,
            'monthlyTrend'       => $monthlyTrend,
            'recentReservations' => $recentReservations,
        ]);
    })->name('reports.occupancy');

    Route::get('/reports/staff', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $start = request('start_date') ?: now()->startOfMonth()->toDateString();
        $end   = request('end_date')   ?: now()->toDateString();

        $totalStaff  = (int) \App\Models\User::count();
        $newToday    = (int) \App\Models\User::whereDate('created_at', now()->toDateString())->count();
        $newMonth    = (int) \App\Models\User::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->count();

        $byRole = \App\Models\Role::withCount('users')->orderByDesc('users_count')->get()
            ->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'total' => $r->users_count]);

        $recentHires = \App\Models\User::with('roles')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'created_at'])
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => trim($u->first_name . ' ' . $u->last_name),
                'email'      => $u->email,
                'roles'      => $u->roles->pluck('name')->toArray(),
                'created_at' => $u->created_at,
            ]);

        $shiftStats = [];
        if (class_exists(\App\Models\EmployeeShift::class)) {
            $shiftStats = [
                'total_shifts'     => (int) \App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->count(),
                'completed_shifts' => (int) \App\Models\EmployeeShift::whereBetween('effective_date', [$start, $end])->where('is_active', true)->count(),
            ];
        }

        return Inertia::render('Manager/Reports/Staff', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'stats'      => [
                'total_staff'  => $totalStaff,
                'active_staff' => $totalStaff,
                'new_today'    => $newToday,
                'new_month'    => $newMonth,
            ],
            'byRole'      => $byRole,
            'recentHires' => $recentHires,
            'shiftStats'  => $shiftStats,
            'filters'     => ['start_date' => $start, 'end_date' => $end],
        ]);
    })->name('reports.staff');

    // Reservation CRUD write routes
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/send-confirmation', [ReservationController::class, 'sendConfirmation'])->name('reservations.send-confirmation');

    Route::put('/reservations/{reservation}', function (\Illuminate\Http\Request $request, $id) {
        $reservation = \App\Models\Reservation::findOrFail($id);
        $data = $request->validate([
            'guest_id'       => 'sometimes|exists:guests,id',
            'room_id'        => 'sometimes|exists:rooms,id',
            'check_in_date'  => 'sometimes|date',
            'check_out_date' => 'sometimes|date',
            'status'         => 'sometimes|string',
            'total_amount'   => 'sometimes|numeric',
            'notes'          => 'sometimes|nullable|string',
        ]);
        $reservation->update($data);
        return redirect()->route('manager.reservations.show', $reservation->id)->with('success', 'Reservation updated.');
    })->name('reservations.update');

    Route::delete('/reservations/{reservation}', function ($id) {
        \App\Models\Reservation::findOrFail($id)->delete();
        return redirect()->route('manager.reservations.index')->with('success', 'Reservation deleted.');
    })->name('reservations.destroy');

    // ── Rooms sub-views ───────────────────────────────────────────────────────
    Route::get('/rooms/housekeeping', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $rooms = class_exists(\App\Models\Room::class)
            ? \App\Models\Room::whereIn('status', ['dirty', 'cleaning', 'inspecting'])->with('roomType')->get()
            : collect();
        return Inertia::render('Admin/Rooms/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'rooms'      => $rooms,
            'filter'     => 'housekeeping',
        ]);
    })->name('rooms.housekeeping');

    Route::get('/rooms/maintenance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $rooms = class_exists(\App\Models\Room::class)
            ? \App\Models\Room::where('status', 'maintenance')->with('roomType')->get()
            : collect();
        return Inertia::render('Admin/Rooms/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'rooms'      => $rooms,
            'filter'     => 'maintenance',
        ]);
    })->name('rooms.maintenance');

    // Mark room as clean
    Route::post('/rooms/{id}/mark-clean', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);

            // Mark room as clean and available
            $room->update([
                'housekeeping_status' => 'clean',
                'status' => 'available',
                'last_cleaned_at' => now(),
                'last_cleaned_by' => auth()->id(),
            ]);

            return back()->with('success', "Room {$room->room_number} marked as clean and available.");

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to mark room as clean: ' . $e->getMessage()]);
        }
    })->name('rooms.mark-clean');

    // Manager mark-dirty route
    Route::post('/rooms/{id}/mark-dirty', function ($id) {
        try {
            $room = \App\Models\Room::findOrFail($id);
            $updateData = ['housekeeping_status' => 'dirty'];
            if ($room->status !== 'occupied') {
                $updateData['status'] = 'cleaning';
            }
            $room->update($updateData);
            return back()->with('success', "Room {$room->room_number} marked as dirty. Housekeeping will be notified.");
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to mark room as dirty: ' . $e->getMessage()]);
        }
    })->name('rooms.mark-dirty');

    // ── Housekeeping task write routes ────────────────────────────────────────
    Route::post('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'store'])->name('housekeeping-tasks.store');
    Route::put('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'update'])->name('housekeeping-tasks.update');
    Route::delete('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'destroy'])->name('housekeeping-tasks.destroy');

    // ── Maintenance write routes ──────────────────────────────────────────────
    Route::get('/maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance-requests.create');
    Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance-requests.store');
    Route::put('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])->name('maintenance-requests.update');
    Route::delete('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'destroy'])->name('maintenance-requests.destroy');
    Route::post('/maintenance-requests/{maintenanceRequest}/assign', [MaintenanceRequestController::class, 'assign'])->name('maintenance-requests.assign');
    Route::post('/maintenance-requests/{maintenanceRequest}/update-status', [MaintenanceRequestController::class, 'updateStatus'])->name('maintenance-requests.update-status');

    // ── Expense write routes ──────────────────────────────────────────────────
    Route::post('/expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Expenses/Edit', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'expense'     => \App\Models\Expense::with('category')->findOrFail($id),
            'categories'  => \App\Models\ExpenseCategory::orderBy('name')->get(),
            'routePrefix' => 'manager',
        ]);
    })->name('expenses.edit');
    Route::put('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{expense}/reject', [\App\Http\Controllers\Admin\ExpenseController::class, 'reject'])->name('expenses.reject');

    // ── Budget Expenses CRUD ──────────────────────────────────────────────────
    Route::get('/budget/expenses/create', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'create'])->name('budget.expenses.create');
    Route::post('/budget/expenses', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'store'])->name('budget.expenses.store');
    Route::get('/budget/expenses/{budgetExpense}', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'show'])->name('budget.expenses.show');
    Route::get('/budget/expenses/{budgetExpense}/edit', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'edit'])->name('budget.expenses.edit');
    Route::put('/budget/expenses/{budgetExpense}', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'update'])->name('budget.expenses.update');
    Route::delete('/budget/expenses/{budgetExpense}', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'destroy'])->name('budget.expenses.destroy');
    Route::post('/budget/expenses/{budgetExpense}/approve', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'approve'])->name('budget.expenses.approve');
    Route::post('/budget/expenses/{budgetExpense}/reject', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'reject'])->name('budget.expenses.reject');

    // ── Customer full CRUD ────────────────────────────────────────────────────
    Route::get('/customers/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Customers/Create', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('customers.create');

    Route::post('/customers', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:customers,email',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'notes'          => 'nullable|string',
        ]);
        $customer = \App\Models\Customer::create($data);
        return redirect()->route('manager.customers.show', $customer->id)->with('success', 'Customer created.');
    })->name('customers.store');

    Route::get('/customers/{customer}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $customer = \App\Models\Customer::with(['customerGroup', 'sales'])->findOrFail($id);
        return Inertia::render('Admin/Customers/Show', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'customer' => $customer]);
    })->name('customers.show');

    Route::get('/customers/{customer}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Customers/Edit', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'customer'   => \App\Models\Customer::findOrFail($id),
        ]);
    })->name('customers.edit');

    Route::put('/customers/{customer}', function (\Illuminate\Http\Request $request, $id) {
        $customer = \App\Models\Customer::findOrFail($id);
        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'notes' => 'nullable|string',
        ]);
        $customer->update($data);
        return redirect()->route('manager.customers.show', $customer->id)->with('success', 'Customer updated.');
    })->name('customers.update');

    Route::delete('/customers/{customer}', function ($id) {
        \App\Models\Customer::findOrFail($id)->delete();
        return redirect()->route('manager.customers.index')->with('success', 'Customer deleted.');
    })->name('customers.destroy');

    // ── Staff & Operations overviews ──────────────────────────────────────────
    Route::get('/staff', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';

        $query = \App\Models\User::with(['roles', 'department'])
            ->whereHas('roles', fn($q) => $q->whereNotIn('name', ['admin', 'manager']));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%");
            });
        }

        if ($dept = $request->input('department')) {
            $query->where('department', $dept);
        }

        if ($status = $request->input('status')) {
            $query->where('current_status', $status);
        }

        $staffMembers = $query->latest()->paginate(20)->withQueryString();

        $allStaff = \App\Models\User::whereHas('roles', fn($q) => $q->whereNotIn('name', ['admin', 'manager']));
        $staffStats = [
            'total'   => (clone $allStaff)->count(),
            'onDuty'  => (clone $allStaff)->where('current_status', 'on_duty')->count(),
            'onBreak' => (clone $allStaff)->where('current_status', 'on_break')->count(),
            'absent'  => (clone $allStaff)->where('current_status', 'absent')->count(),
        ];

        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Manager/Staff/Index', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'staffMembers' => $staffMembers,
            'staffStats'   => $staffStats,
            'departments'  => $departments,
            'filters'      => $request->only(['search', 'department', 'status']),
        ]);
    })->name('staff.index');

    // ── Maintenance categories write routes ───────────────────────────────────
    Route::get('/maintenance-categories/create', [MaintenanceCategoryController::class, 'create'])->name('maintenance-categories.create');
    Route::post('/maintenance-categories', [MaintenanceCategoryController::class, 'store'])->name('maintenance-categories.store');
    Route::get('/maintenance-categories/{maintenanceCategory}/edit', [MaintenanceCategoryController::class, 'edit'])->name('maintenance-categories.edit');
    Route::put('/maintenance-categories/{maintenanceCategory}', [MaintenanceCategoryController::class, 'update'])->name('maintenance-categories.update');
    Route::delete('/maintenance-categories/{maintenanceCategory}', [MaintenanceCategoryController::class, 'destroy'])->name('maintenance-categories.destroy');
    Route::post('/maintenance-categories/{maintenanceCategory}/toggle-active', [MaintenanceCategoryController::class, 'toggleActive'])->name('maintenance-categories.toggle-active');

    // ── Expenses additional routes ────────────────────────────────────────────
    Route::get('/expenses/{expense}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Expenses/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'expense'     => \App\Models\Expense::with('category')->findOrFail($id),
            'routePrefix' => 'manager',
        ]);
    })->name('expenses.show');
    Route::post('/expenses/{expense}/approve', function (\App\Models\Expense $expense) {
        $expense->update([
            'status'         => 'approved',
            'approved_by'    => auth()->id(),
            'approved_at'     => now(),
            'approval_notes' => request('approval_notes'),
        ]);
        return redirect()->back()->with('success', 'Expense approved successfully.');
    })->name('expenses.approve');

    // ── Budget read routes ────────────────────────────────────────────────────
    Route::get('/budget', [\App\Http\Controllers\Admin\BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/dashboard', [\App\Http\Controllers\Admin\BudgetController::class, 'dashboard'])->name('budget.dashboard');
    Route::get('/budget/reports', [\App\Http\Controllers\Admin\BudgetController::class, 'reports'])->name('budget.reports');
    Route::get('/budget/alerts', [\App\Http\Controllers\Admin\BudgetController::class, 'alerts'])->name('budget.alerts');
    Route::get('/budget/expenses/pending-approvals', [\App\Http\Controllers\Admin\BudgetExpenseController::class, 'pendingApprovals'])->name('budget.expenses.pending-approvals');

    // ── Budget CRUD & action routes ───────────────────────────────────────────
    Route::get('/budget/create', [\App\Http\Controllers\Admin\BudgetController::class, 'create'])->name('budget.create');
    Route::post('/budget', [\App\Http\Controllers\Admin\BudgetController::class, 'store'])->name('budget.store');
    Route::get('/budget/archived', [\App\Http\Controllers\Admin\BudgetController::class, 'archived'])->name('budget.archived');
    Route::get('/budget/expenses', [\App\Http\Controllers\Admin\BudgetController::class, 'expenses'])->name('budget.expenses.index');
    Route::post('/budget/export', [\App\Http\Controllers\Admin\BudgetController::class, 'export'])->name('budget.export');
    Route::get('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'show'])->name('budget.show');
    Route::get('/budget/{budget}/edit', [\App\Http\Controllers\Admin\BudgetController::class, 'edit'])->name('budget.edit');
    Route::put('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'update'])->name('budget.update');
    Route::delete('/budget/{budget}', [\App\Http\Controllers\Admin\BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::post('/budget/{budget}/submit-for-approval', [\App\Http\Controllers\Admin\BudgetController::class, 'submitForApproval'])->name('budget.submit-for-approval');
    Route::post('/budget/{budget}/approve', [\App\Http\Controllers\Admin\BudgetController::class, 'approve'])->name('budget.approve');
    Route::post('/budget/{budget}/reject', [\App\Http\Controllers\Admin\BudgetController::class, 'reject'])->name('budget.reject');
    Route::post('/budget/{budget}/archive', [\App\Http\Controllers\Admin\BudgetController::class, 'archive'])->name('budget.archive');

    // ── Expense categories write routes ───────────────────────────────────────
    Route::post('/expenses/categories', [\App\Http\Controllers\Admin\ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
    Route::put('/expenses/categories/{category}', [\App\Http\Controllers\Admin\ExpenseController::class, 'updateCategory'])->name('expenses.categories.update');
    Route::delete('/expenses/categories/{category}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroyCategory'])->name('expenses.categories.destroy');

    // ── Waitlist action routes ────────────────────────────────────────────────
    Route::get('/waitlist/check-availability', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Waitlist/CheckAvailability', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('waitlist.check-availability');
    Route::get('/waitlist/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        return Inertia::render('Admin/Waitlist/Create', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => 'manager',
        ]);
    })->name('waitlist.create');
    Route::get('/waitlist/{waitlist}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $waitlist = class_exists(\App\Models\Waitlist::class) ? \App\Models\Waitlist::with('guest')->findOrFail($id) : null;
        return Inertia::render('Admin/Waitlist/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'waitlist'    => $waitlist,
            'routePrefix' => 'manager',
        ]);
    })->name('waitlist.show');
    Route::post('/waitlist/auto-notify', function () {
        if (class_exists(\App\Models\Waitlist::class)) {
            $count = \App\Models\Waitlist::where('status', 'active')->count();
            return response()->json(['success' => true, 'notifications_sent' => $count]);
        }
        return response()->json(['success' => true, 'notifications_sent' => 0]);
    })->name('waitlist.auto-notify');
    Route::post('/waitlist/{waitlist}/notify', function ($id) {
        return response()->json(['success' => true]);
    })->name('waitlist.notify');
    Route::post('/waitlist/{waitlist}/convert', function ($id) {
        return response()->json(['success' => true, 'redirect' => route('manager.reservations.index')]);
    })->name('waitlist.convert');

    Route::get('/operations', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $today = now()->toDateString();
        $arrivals   = class_exists(\App\Models\Reservation::class) ? \App\Models\Reservation::whereDate('check_in_date', $today)->count() : 0;
        $departures = class_exists(\App\Models\Reservation::class) ? \App\Models\Reservation::whereDate('check_out_date', $today)->count() : 0;
        $pendingTasks = class_exists(\App\Models\MaintenanceRequest::class) ? \App\Models\MaintenanceRequest::where('status', 'pending')->count() : 0;
        return Inertia::render('Manager/Operations/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'summary'    => ['arrivals' => $arrivals, 'departures' => $departures, 'pendingTasks' => $pendingTasks],
        ]);
    })->name('operations.index');

    // ── Work Shifts ───────────────────────────────────────────────────────────
    Route::get('/work-shifts', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'manager';
        $workShifts = \App\Models\WorkShift::with(['employeeShifts.user'])->orderBy('start_time')->get()->map(function ($shift) {
            return [
                'id' => $shift->id, 'shift_name' => $shift->name ?? 'Unnamed Shift',
                'start_time' => $shift->start_time, 'end_time' => $shift->end_time,
                'hours' => $shift->hours ?? 0, 'break_minutes' => $shift->break_minutes ?? 0,
                'is_overnight' => $shift->is_overnight ?? false, 'is_active' => $shift->is_active ?? false,
                'department' => $shift->department ?? 'general',
                'type' => $shift->is_overnight ? 'night' : 'regular',
                'duration' => $shift->hours ?? 8,
                'employees_count' => $shift->employeeShifts->count(),
                'employees' => $shift->employeeShifts->map(fn($es) => [
                    'id' => $es->id, 'user_id' => $es->user_id,
                    'effective_date' => $es->effective_date, 'days_of_week' => $es->days_of_week ?? [],
                    'user' => $es->user ? ['name' => $es->user->first_name . ' ' . $es->user->last_name, 'email' => $es->user->email] : null,
                ])->toArray(),
                'created_at' => $shift->created_at, 'updated_at' => $shift->updated_at,
            ];
        });
        $stats = ['total_shifts' => $workShifts->count(), 'active_shifts' => $workShifts->where('is_active', true)->count(), 'completed_shifts' => $workShifts->where('is_active', false)->count(), 'today_shifts' => 0];
        return Inertia::render('Admin/WorkShifts/Index', [
            'user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'workShifts' => $workShifts, 'shiftStats' => $stats,
            'shiftTemplates' => $workShifts, 'currentShifts' => [],
            'staffUsers' => \App\Models\User::with('roles')->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'employee_id']),
        ]);
    })->name('work-shifts.index');
    Route::post('/work-shifts', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate(['name' => 'required|string|max:100', 'start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i', 'break_minutes' => 'nullable|integer|min:0', 'is_overnight' => 'nullable|boolean', 'is_active' => 'nullable|boolean']);
        \App\Models\WorkShift::create($validated);
        return back()->with('success', 'Work shift created.');
    })->name('work-shifts.store');
    Route::put('/work-shifts/{workShift}', function (\Illuminate\Http\Request $request, \App\Models\WorkShift $workShift) {
        $validated = $request->validate(['name' => 'required|string|max:100', 'start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i', 'break_minutes' => 'nullable|integer|min:0', 'is_overnight' => 'nullable|boolean', 'is_active' => 'nullable|boolean']);
        $workShift->update($validated);
        return back()->with('success', 'Work shift updated.');
    })->name('work-shifts.update');
    Route::delete('/work-shifts/{workShift}', function (\App\Models\WorkShift $workShift) {
        $workShift->delete();
        return back()->with('success', 'Work shift deleted.');
    })->name('work-shifts.destroy');
    Route::post('/work-shifts/{workShift}/assign', function (\Illuminate\Http\Request $request, \App\Models\WorkShift $workShift) {
        $validated = $request->validate(['user_id' => 'required|exists:users,id', 'effective_date' => 'required|date', 'days_of_week' => 'nullable|array', 'end_date' => 'nullable|date']);
        \App\Models\EmployeeShift::create(array_merge($validated, ['work_shift_id' => $workShift->id, 'is_active' => true]));
        return back()->with('success', 'Employee assigned to shift.');
    })->name('work-shifts.assign');
    Route::delete('/work-shifts/{workShift}/unassign/{employeeShift}', function (\App\Models\WorkShift $workShift, \App\Models\EmployeeShift $employeeShift) {
        $employeeShift->delete();
        return back()->with('success', 'Employee removed from shift.');
    })->name('work-shifts.unassign');

    // ── Housekeeping Schedules ────────────────────────────────────────────────
    Route::get('/housekeeping-schedules', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'index'])->name('housekeeping-schedules.index');
    Route::get('/housekeeping-schedules/create', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'create'])->name('housekeeping-schedules.create');
    Route::post('/housekeeping-schedules', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'store'])->name('housekeeping-schedules.store');
    Route::get('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'show'])->name('housekeeping-schedules.show');
    Route::get('/housekeeping-schedules/{housekeepingSchedule}/edit', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'edit'])->name('housekeeping-schedules.edit');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'update'])->name('housekeeping-schedules.update');
    Route::delete('/housekeeping-schedules/{housekeepingSchedule}', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'destroy'])->name('housekeeping-schedules.destroy');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}/status', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'updateStatus'])->name('housekeeping-schedules.update-status');
    Route::put('/housekeeping-schedules/{housekeepingSchedule}/rooms/{room}/status', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'updateRoomStatus'])->name('housekeeping-schedules.update-room-status');
    Route::post('/housekeeping-schedules/{housekeepingSchedule}/duplicate', [\App\Http\Controllers\Admin\HousekeepingScheduleController::class, 'duplicate'])->name('housekeeping-schedules.duplicate');

    // ── Laundry ───────────────────────────────────────────────────────────────
    Route::get('/laundry', [LaundryController::class, 'index'])->name('laundry.index');
    Route::get('/laundry/create', [LaundryController::class, 'create'])->name('laundry.create');
    Route::post('/laundry', [LaundryController::class, 'store'])->name('laundry.store');
    Route::get('/laundry/{laundry}', [LaundryController::class, 'show'])->name('laundry.show');
    Route::patch('/laundry/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundry.update-status');
    Route::delete('/laundry/{laundry}', [LaundryController::class, 'destroy'])->name('laundry.destroy');

    // ── Housekeeping Tasks: generate daily ────────────────────────────────────
    Route::post('/housekeeping-tasks/generate-daily', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'generate-daily'])->name('housekeeping-tasks.generate-daily');

    // ── Invoices ─────────────────────────────────────────────────────────────
    Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\Admin\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{folio}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{folio}/mark-paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::get('/invoices/overdue', [\App\Http\Controllers\Admin\InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::get('/invoices/paid', [\App\Http\Controllers\Admin\InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::post('/invoices/send-reminders', [\App\Http\Controllers\Admin\InvoiceController::class, 'sendReminders'])->name('invoices.sendReminders');

    // ── Quotes ───────────────────────────────────────────────────────────────
    Route::get('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [\App\Http\Controllers\Admin\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [\App\Http\Controllers\Admin\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{id}', [\App\Http\Controllers\Admin\QuoteController::class, 'show'])->name('quotes.show');
});

// Maintenance Routes
Route::middleware(['auth', 'role:maintenance'])->prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/dashboard', function () {
    // ... (rest of the code remains the same)
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Maintenance/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard');

    // Work Orders
    Route::get('/work-orders', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/WorkOrders/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('work-orders.index');
    Route::get('/work-orders/open', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/WorkOrders/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'filter' => 'open']);
    })->name('work-orders.open');
    Route::get('/work-orders/in-progress', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/WorkOrders/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'filter' => 'in_progress']);
    })->name('work-orders.in-progress');
    Route::get('/work-orders/completed', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/WorkOrders/Index', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role), 'filter' => 'completed']);
    })->name('work-orders.completed');

    // IPTV
    Route::get('/iptv/devices', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/IPTV/Devices', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('iptv.devices');
    Route::get('/iptv/channels', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/IPTV/Channels', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('iptv.channels');
    Route::get('/iptv/troubleshoot', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/IPTV/Troubleshoot', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('iptv.troubleshoot');
    Route::get('/iptv/installation', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/IPTV/Installation', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('iptv.installation');

    // Preventive Maintenance
    Route::get('/preventive/scheduled', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Preventive/Scheduled', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('preventive.scheduled');
    Route::get('/preventive/overdue', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Preventive/Overdue', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('preventive.overdue');
    Route::get('/preventive/calendar', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Preventive/Calendar', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('preventive.calendar');
    Route::get('/preventive/equipment', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Preventive/Equipment', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('preventive.equipment');

    // Inventory
    Route::get('/inventory/parts', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Inventory/Parts', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('inventory.parts');
    Route::get('/inventory/tools', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Inventory/Tools', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('inventory.tools');
    Route::get('/inventory/request', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Inventory/Request', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('inventory.request');
    Route::get('/inventory/vendors', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/Inventory/Vendors', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('inventory.vendors');

    // Time Tracking
    Route::get('/time-tracking', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'maintenance';
        return Inertia::render('Maintenance/TimeTracking', ['user' => $user, 'navigation' => app(DashboardController::class)->getNavigationForRole($role)]);
    })->name('time-tracking');
});

// Housekeeping Routes
Route::middleware(['auth', 'role:housekeeping'])->prefix('housekeeping')->name('housekeeping.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Housekeeping/Dashboard', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard.index');

    // Tasks
    Route::get('/tasks', function () {
        return Inertia::render('Housekeeping/Tasks/Index');
    })->name('tasks.index');
    Route::get('/tasks/create', function () {
        return Inertia::render('Housekeeping/Tasks/Create');
    })->name('tasks.create');
    Route::post('/tasks', function () {
        return redirect()->back()->with('success', 'Task created successfully');
    })->name('tasks.store');
    Route::get('/tasks/{task}', function () {
        return Inertia::render('Housekeeping/Tasks/Show');
    })->name('tasks.show');
    Route::get('/tasks/{task}/edit', function () {
        return Inertia::render('Housekeeping/Tasks/Edit');
    })->name('tasks.edit');
    Route::put('/tasks/{task}', function () {
        return redirect()->back()->with('success', 'Task updated successfully');
    })->name('tasks.update');
    Route::delete('/tasks/{task}', function () {
        return redirect()->back()->with('success', 'Task deleted successfully');
    })->name('tasks.destroy');

    // Task History
    Route::get('/tasks/history', function () {
        return Inertia::render('Housekeeping/Tasks/History');
    })->name('tasks.history');

    // Inventory
    Route::get('/inventory/amenities', function () {
        return Inertia::render('Housekeeping/Inventory/Amenities');
    })->name('inventory.amenities');

    Route::get('/inventory/linens', function () {
        return Inertia::render('Housekeeping/Inventory/Linens');
    })->name('inventory.linens');

    Route::get('/inventory/supplies', function () {
        return Inertia::render('Housekeeping/Inventory/Supplies');
    })->name('inventory.supplies');

    Route::get('/inventory/request', function () {
        return Inertia::render('Housekeeping/Inventory/Request');
    })->name('inventory.request');

    // Maintenance Report
    Route::get('/maintenance/report', function () {
        return Inertia::render('Housekeeping/Maintenance/Report');
    })->name('maintenance.report');

    // Staff Management
    Route::get('/staff', function () {
        return Inertia::render('Housekeeping/Staff/Index');
    })->name('staff.index');

    // Schedules
    Route::get('/schedules', function () {
        return Inertia::render('Housekeeping/Schedules/Index');
    })->name('schedules.index');

    // Lost and Found
    Route::get('/lost-found', function () {
        return Inertia::render('Housekeeping/LostFound/Index');
    })->name('lost-found.index');

    // Guest Requests
    Route::get('/guest-requests', function () {
        return Inertia::render('Housekeeping/GuestRequests/Index');
    })->name('guest-requests.index');

    // Room Status
    Route::get('/room-status', function () {
        return Inertia::render('Housekeeping/RoomStatus/Index');
    })->name('room-status.index');
});

// Bartender Routes
Route::middleware(['auth', 'verified', 'role:bartender'])->prefix('bartender')->name('bartender.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Bartender\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/drinks', [\App\Http\Controllers\Bartender\DrinksController::class, 'index'])->name('drinks');
    Route::get('/inventory', [\App\Http\Controllers\Bartender\InventoryController::class, 'index'])->name('inventory');
    Route::get('/sales', [\App\Http\Controllers\Bartender\SalesController::class, 'index'])->name('sales');
});

// Server/Restaurant Routes
Route::middleware(['auth', 'verified', 'role:server|restaurant_staff'])->prefix('server')->name('server.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Server\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales', [\App\Http\Controllers\Server\SalesController::class, 'index'])->name('sales');
});

// POS Routes
Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [\App\Http\Controllers\POS\POSController::class, 'index'])->name('index');
    Route::post('/process-sale', [\App\Http\Controllers\POS\POSController::class, 'processSale'])->name('process-sale');
    Route::post('/open-drawer', [\App\Http\Controllers\POS\POSController::class, 'openDrawer'])->name('open-drawer');

    // Locations
    Route::resource('locations', \App\Http\Controllers\POS\LocationController::class);

    // Suppliers
    Route::get('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/payments', [\App\Http\Controllers\POS\SupplierController::class, 'payments'])->name('suppliers.payments.index');
    Route::post('/suppliers/{supplier}/payments', [\App\Http\Controllers\POS\SupplierController::class, 'storePayment'])->name('suppliers.payments.store');
    Route::delete('/suppliers/payments/{payment}', [\App\Http\Controllers\POS\SupplierController::class, 'deletePayment'])->name('suppliers.payments.destroy');

    // Purchase Orders
    Route::get('/purchases', [\App\Http\Controllers\POS\POSController::class, 'purchases'])->name('purchases.index');
    Route::get('/purchases/create', [\App\Http\Controllers\POS\POSController::class, 'createPurchase'])->name('purchases.create');
    Route::post('/purchases', [\App\Http\Controllers\POS\POSController::class, 'createPurchaseOrder'])->name('purchases.store');
    Route::get('/purchases/{purchaseOrder}', [\App\Http\Controllers\POS\POSController::class, 'showPurchase'])->name('purchases.show');
    Route::get('/purchases/{purchaseOrder}/edit', [\App\Http\Controllers\POS\POSController::class, 'editPurchase'])->name('purchases.edit');
    Route::put('/purchases/{purchaseOrder}', [\App\Http\Controllers\POS\POSController::class, 'updatePurchaseOrder'])->name('purchases.update');
    Route::post('/purchases/{purchaseOrder}/receive', [\App\Http\Controllers\POS\POSController::class, 'receivePurchaseOrder'])->name('purchases.receive');
    Route::post('/purchases/{purchaseOrder}/documents', [\App\Http\Controllers\POS\POSController::class, 'uploadPurchaseDocument'])->name('purchases.documents.upload');
    Route::post('/purchases/{purchaseOrder}/delivery-documents', [\App\Http\Controllers\POS\POSController::class, 'uploadDeliveryDocument'])->name('purchases.delivery-documents.upload');

    // Products List (for dropdowns)
    Route::get('/products/list', function () {
        $products = \App\Models\Product::with(['category', 'brand', 'unit', 'warehouses'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                // Safely handle relationships that might be strings
                $category = null;
                if ($product->category && is_object($product->category)) {
                    $category = [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ];
                }

                $brand = null;
                if ($product->brand && is_object($product->brand)) {
                    $brand = [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                    ];
                }

                $unit = null;
                if ($product->unit && is_object($product->unit)) {
                    $unit = [
                        'id' => $product->unit->id,
                        'name' => $product->unit->name,
                        'abbreviation' => $product->unit->abbreviation,
                    ];
                }

                $warehouses = [];
                if ($product->warehouses && is_iterable($product->warehouses)) {
                    $warehouses = $product->warehouses->map(function ($warehouse) {
                        if (is_object($warehouse) && isset($warehouse->id)) {
                            return [
                                'id' => $warehouse->id,
                                'name' => $warehouse->name ?? 'Unknown',
                                'pivot_quantity' => $warehouse->pivot->quantity ?? 0,
                            ];
                        }
                        return null;
                    })->filter()->values()->all();
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'max_stock' => $product->max_stock,
                    'category' => $category,
                    'brand' => $brand,
                    'unit' => $unit,
                    'warehouses' => $warehouses,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        // Check if this is an Inertia request
        if (request()->header('X-Inertia')) {
            // For Inertia requests, return the products as props
            return Inertia::render('POS/Products/List', [
                'products' => $products
            ]);
        }

        // For non-Inertia requests (like direct API calls), return JSON
        return response()->json(['data' => $products]);
    })->name('products.list');

    // Products and Warehouses (for transfers dropdowns)
    Route::get('/products-and-warehouses', function () {
        $products = \App\Models\Product::with(['category', 'brand', 'unit', 'warehouses'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                // Safely handle relationships that might be strings
                $category = null;
                if ($product->category && is_object($product->category)) {
                    $category = [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ];
                }

                $brand = null;
                if ($product->brand && is_object($product->brand)) {
                    $brand = [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                    ];
                }

                $unit = null;
                if ($product->unit && is_object($product->unit)) {
                    $unit = [
                        'id' => $product->unit->id,
                        'name' => $product->unit->name,
                        'abbreviation' => $product->unit->abbreviation,
                    ];
                }

                $warehouses = [];
                if ($product->warehouses && is_iterable($product->warehouses)) {
                    $warehouses = $product->warehouses->map(function ($warehouse) {
                        if (is_object($warehouse) && isset($warehouse->id)) {
                            return [
                                'id' => $warehouse->id,
                                'name' => $warehouse->name ?? 'Unknown',
                                'pivot_quantity' => $warehouse->pivot->quantity ?? 0,
                            ];
                        }
                        return null;
                    })->filter()->values()->all();
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'max_stock' => $product->max_stock,
                    'category' => $category,
                    'brand' => $brand,
                    'unit' => $unit,
                    'warehouses' => $warehouses,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        $warehouses = \App\Models\Warehouse::orderBy('name')->get()->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'capacity' => $warehouse->capacity,
                'manager' => $warehouse->manager,
                'phone' => $warehouse->phone,
                'email' => $warehouse->email,
                'status' => $warehouse->status,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
            ];
        });

        // Check if this is an Inertia request
        if (request()->header('X-Inertia')) {
            // For Inertia requests, return the data as props
            return Inertia::render('POS/Products/Warehouses', [
                'products' => $products,
                'warehouses' => $warehouses
            ]);
        }

        // For non-Inertia requests (like axios), return JSON
        return response()->json([
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    })->name('products.warehouses');

    // Products
    Route::get('/products', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get settings for currency display
        $settings = [
            'currency' => \App\Models\Setting::where('key', 'currency')->first()?->value ?? 'USD',
            'currency_position' => \App\Models\Setting::where('key', 'currency_position')->first()?->value ?? 'before',
            'decimal_separator' => \App\Models\Setting::where('key', 'decimal_separator')->first()?->value ?? '.',
            'thousand_separator' => \App\Models\Setting::where('key', 'thousand_separator')->first()?->value ?? ',',
            'currency_decimals' => \App\Models\Setting::where('key', 'currency_decimals')->first()?->value ?? 2,
        ];

        return Inertia::render('POS/Products/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'products' => \App\Models\Product::with('category', 'brand', 'unit')->orderBy('name')->get(),
            'categories' => \App\Models\ProductCategory::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'units' => \App\Models\Unit::orderBy('name')->get(),
            'settings' => $settings
        ]);
    })->name('products.index');

    // Product CRUD
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');

    // Stock Adjustment
    Route::post('/adjust-stock', [\App\Http\Controllers\POS\POSController::class, 'adjustStock'])->name('pos.adjust-stock');

    // Product History
    Route::get('/product-history/{product}', [\App\Http\Controllers\POS\POSController::class, 'productHistory'])->name('pos.product-history');

    // Stock Adjustments
    Route::post('/adjustments', [\App\Http\Controllers\POS\POSController::class, 'storeAdjustment'])->name('pos.adjustments.store');

    // Stock Transfers
    Route::post('/transfers', [\App\Http\Controllers\POS\POSController::class, 'storeTransfer'])->name('pos.transfers.store');

    // Categories
    Route::get('/categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get categories with product counts
        $categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->get();

        return Inertia::render('POS/Categories/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories
        ]);
    })->name('categories.index');

    // Brands
    Route::get('/brands', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Brands/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'brands' => \App\Models\Brand::withCount('products')->orderBy('name')->get()
        ]);
    })->name('brands.index');

    // Units
    Route::get('/units', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Units/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'units' => \App\Models\Unit::withCount('products')->orderBy('name')->get()
        ]);
    })->name('units.index');

    // Warehouses
    Route::get('/warehouses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Warehouses/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'warehouses' => \App\Models\Warehouse::withCount('products')->orderBy('name')->get()
        ]);
    })->name('warehouses.index');

    // Inventory
    Route::get('/inventory', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $products = \App\Models\Product::with(['category', 'brand', 'unit', 'stockBatches.location'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $byLocation = $p->stockBatches
                    ->filter(fn($b) => $b->quantity > 0)
                    ->groupBy(fn($b) => $b->location?->name ?? 'Unassigned')
                    ->map(fn($batches) => $batches->sum('quantity'));
                return array_merge($p->toArray(), [
                    'stock_by_location' => $byLocation,
                    'margin_percentage' => $p->margin_percentage,
                ]);
            });

        $locations = \App\Models\Location::orderBy('name')->get(['id', 'name']);

        return Inertia::render('POS/Inventory/Index', [
            'user'      => $user,
            'navigation'=> app(DashboardController::class)->getNavigationForRole($role),
            'products'  => $products,
            'locations' => $locations,
        ]);
    })->name('inventory.index');

    // Stock Adjustments
    Route::get('/adjustments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $adjustments = \App\Models\StockAdjustment::with('product', 'warehouse', 'user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($adjustment) {
                return [
                    'id' => $adjustment->id,
                    'product' => $adjustment->product ? [
                        'id' => $adjustment->product->id,
                        'name' => $adjustment->product->name,
                        'code' => $adjustment->product->sku ?? $adjustment->product->code ?? null,
                    ] : null,
                    'warehouse' => $adjustment->warehouse,
                    'user' => $adjustment->user ? [
                        'id' => $adjustment->user->id,
                        'name' => $adjustment->user->name ?? $adjustment->user->first_name . ' ' . $adjustment->user->last_name,
                    ] : null,
                    'type' => $adjustment->adjustment_type,
                    'quantity' => $adjustment->adjustment_quantity,
                    'previous_stock' => $adjustment->quantity_before,
                    'new_stock' => $adjustment->quantity_after,
                    'notes' => $adjustment->notes ?? $adjustment->reason,
                    'created_at' => $adjustment->created_at,
                    'updated_at' => $adjustment->updated_at,
                ];
            });

        return Inertia::render('POS/Adjustments/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'adjustments' => $adjustments
        ]);
    })->name('adjustments.index');

    // Stock Transfers
    Route::get('/transfers', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $transfers = \App\Models\StockTransfer::with(['product', 'fromLocation', 'toLocation', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'product'    => $t->product ? ['id' => $t->product->id, 'name' => $t->product->name, 'code' => $t->product->code] : null,
                'from_location' => $t->fromLocation ? ['id' => $t->fromLocation->id, 'name' => $t->fromLocation->name] : null,
                'to_location'   => $t->toLocation   ? ['id' => $t->toLocation->id,   'name' => $t->toLocation->name]   : null,
                'quantity'   => (float) $t->quantity,
                'status'     => $t->status,
                'notes'      => $t->notes,
                'user'       => $t->user ? ['id' => $t->user->id, 'name' => trim($t->user->first_name . ' ' . $t->user->last_name)] : null,
                'created_at' => $t->created_at,
            ]);

        $locations = \App\Models\Location::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);

        return Inertia::render('POS/Transfers/Index', [
            'user'      => $user,
            'navigation'=> app(DashboardController::class)->getNavigationForRole($role),
            'transfers' => $transfers,
            'locations' => $locations,
        ]);
    })->name('transfers.index');

    // Stock Movements
    Route::get('/stock-movements', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $movements = \App\Models\StockMovement::with(['product', 'user', 'location'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($m) {
                return [
                    'id'             => $m->id,
                    'product'        => $m->product ? ['id' => $m->product->id, 'name' => $m->product->name, 'sku' => $m->product->code] : null,
                    'type'           => $m->type,
                    'quantity'       => $m->quantity,
                    'previous_stock' => $m->previous_stock,
                    'new_stock'      => $m->new_stock,
                    'reference_type' => $m->reference_type,
                    'notes'          => $m->notes,
                    'user'           => $m->user ? ['id' => $m->user->id, 'name' => trim($m->user->first_name . ' ' . $m->user->last_name)] : null,
                    'location'       => $m->location ? ['id' => $m->location->id, 'name' => $m->location->name] : null,
                    'created_at'     => $m->created_at,
                ];
            });

        $locations = \App\Models\Location::orderBy('name')->get(['id', 'name']);

        return Inertia::render('POS/StockMovements/Index', [
            'user'      => $user,
            'navigation'=> app(DashboardController::class)->getNavigationForRole($role),
            'movements' => $movements,
            'locations' => $locations,
        ]);
    })->name('stock-movements.index');

    // Transactions
    Route::get('/transactions', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $transactions = \App\Models\PosTransaction::with(['sale', 'cashDrawerSession.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'sale_number'    => $t->sale?->sale_number ?? null,
                'sale_id'        => $t->sale_id,
                'type'           => $t->type,
                'amount'         => (float) $t->amount,
                'payment_method' => $t->payment_method,
                'description'    => $t->description,
                'cashier'        => $t->cashDrawerSession?->user
                    ? trim($t->cashDrawerSession->user->first_name . ' ' . $t->cashDrawerSession->user->last_name)
                    : null,
                'session_id'     => $t->cash_drawer_session_id,
                'created_at'     => $t->created_at,
            ]);

        return Inertia::render('POS/Transactions/Index', [
            'user'         => $user,
            'navigation'   => app(DashboardController::class)->getNavigationForRole($role),
            'transactions' => $transactions,
        ]);
    })->name('pos.transactions.index');

    // Orders (open/pending sales — unpaid, partial, or charged to room)
    Route::get('/orders', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $orders = \App\Models\Sale::with(['customer', 'user', 'items.product', 'room', 'reservation'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id'                => $s->id,
                    'sale_number'       => $s->sale_number,
                    'customer_name'     => $s->customer_name ?? ($s->customer ? trim($s->customer->first_name . ' ' . $s->customer->last_name) : null),
                    'room_number'       => $s->room?->room_number ?? null,
                    'reservation_id'    => $s->reservation_id,
                    'is_charged_to_room'=> (bool) $s->is_charged_to_room,
                    'is_walk_in'        => (bool) $s->is_walk_in,
                    'payment_status'    => $s->payment_status ?? 'unpaid',
                    'payment_method'    => $s->payment_method,
                    'subtotal'          => (float) $s->subtotal,
                    'tax_amount'        => (float) $s->tax_amount,
                    'discount_amount'   => (float) $s->discount_amount,
                    'total_amount'      => (float) $s->total_amount,
                    'items_count'       => $s->items->count(),
                    'items'             => $s->items->map(fn($i) => [
                        'id'         => $i->id,
                        'name'       => $i->product?->name ?? $i->product_name ?? '—',
                        'quantity'   => $i->quantity,
                        'unit_price' => (float) $i->unit_price,
                        'total'      => (float) $i->total_price,
                    ]),
                    'notes'             => $s->notes,
                    'user'              => $s->user ? trim($s->user->first_name . ' ' . $s->user->last_name) : null,
                    'sale_date'         => $s->sale_date ?? $s->created_at,
                    'created_at'        => $s->created_at,
                ];
            });

        return Inertia::render('POS/Orders/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'orders'     => $orders,
        ]);
    })->name('orders.index');

    // POS Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $now   = now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $startOfYear  = $now->copy()->startOfYear()->toDateString();

        $saleModel = \App\Models\Sale::class;

        // KPI totals
        $totalRevenue   = (float) $saleModel::sum('total_amount');
        $todayRevenue   = (float) $saleModel::whereDate('created_at', $today)->sum('total_amount');
        $monthRevenue   = (float) $saleModel::whereDate('created_at', '>=', $startOfMonth)->sum('total_amount');
        $totalSales     = (int)   $saleModel::count();
        $todaySales     = (int)   $saleModel::whereDate('created_at', $today)->count();
        $monthSales     = (int)   $saleModel::whereDate('created_at', '>=', $startOfMonth)->count();
        $avgOrderValue  = $totalSales > 0 ? round($totalRevenue / $totalSales, 2) : 0;

        // Revenue by payment method
        $byMethod = $saleModel::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => ['method' => $r->payment_method, 'count' => (int)$r->count, 'revenue' => (float)$r->revenue]);

        // Daily revenue — last 30 days
        $daily = $saleModel::selectRaw('DATE(created_at) as day, COUNT(*) as sales, SUM(total_amount) as revenue')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($r) => ['day' => $r->day, 'sales' => (int)$r->sales, 'revenue' => (float)$r->revenue]);

        // Top 10 products by revenue
        $topProducts = \App\Models\SaleItem::selectRaw('product_id, SUM(quantity) as qty, SUM(total_price) as revenue, SUM(total_price - unit_cost * quantity) as profit')
            ->with('product:id,name,code')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id'      => $r->product_id,
                'name'    => $r->product?->name ?? 'Unknown',
                'code'    => $r->product?->code ?? '',
                'qty'     => (float)$r->qty,
                'revenue' => (float)$r->revenue,
                'profit'  => (float)$r->profit,
            ]);

        // Monthly revenue — last 12 months
        $monthly = $saleModel::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as sales, SUM(total_amount) as revenue")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $r->month, 'sales' => (int)$r->sales, 'revenue' => (float)$r->revenue]);

        // Recent 20 sales
        $recent = $saleModel::with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'sale_number'    => $s->sale_number,
                'total_amount'   => (float)$s->total_amount,
                'payment_method' => $s->payment_method,
                'payment_status' => $s->payment_status ?? 'paid',
                'cashier'        => $s->user ? trim($s->user->first_name . ' ' . $s->user->last_name) : 'System',
                'created_at'     => $s->created_at,
            ]);

        return Inertia::render('POS/Reports/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'kpis' => [
                'total_revenue'  => $totalRevenue,
                'today_revenue'  => $todayRevenue,
                'month_revenue'  => $monthRevenue,
                'total_sales'    => $totalSales,
                'today_sales'    => $todaySales,
                'month_sales'    => $monthSales,
                'avg_order'      => $avgOrderValue,
            ],
            'byMethod'    => $byMethod,
            'daily'       => $daily,
            'monthly'     => $monthly,
            'topProducts' => $topProducts,
            'recent'      => $recent,
        ]);
    })->name('pos.reports.index');

    // POS Analytics
    Route::get('/analytics', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $saleModel = \App\Models\Sale::class;
        $now = now();

        // Revenue trend — last 12 months with growth %
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $m     = $now->copy()->subMonths($i);
            $start = $m->startOfMonth()->toDateTimeString();
            $end   = $m->endOfMonth()->toDateTimeString();
            $rev   = (float) $saleModel::whereBetween('created_at', [$start, $end])->sum('total_amount');
            $cnt   = (int)   $saleModel::whereBetween('created_at', [$start, $end])->count();
            $months->push(['label' => $m->format('M Y'), 'revenue' => $rev, 'count' => $cnt]);
        }

        // Payment method breakdown (current month)
        $startOfMonth = $now->copy()->startOfMonth()->toDateTimeString();
        $methodBreakdown = $saleModel::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('created_at', '>=', $startOfMonth)
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => ['method' => $r->payment_method, 'count' => (int)$r->count, 'revenue' => (float)$r->revenue]);

        // Top selling products (all time)
        $topItems = \App\Models\SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(total_price) as total_rev, SUM(total_price - unit_cost * quantity) as total_profit')
            ->with('product:id,name,code')
            ->groupBy('product_id')
            ->orderByDesc('total_rev')
            ->limit(8)
            ->get()
            ->map(fn($r) => [
                'name'    => $r->product?->name ?? 'Unknown',
                'qty'     => (float)$r->total_qty,
                'revenue' => (float)$r->total_rev,
                'profit'  => (float)$r->total_profit,
                'margin'  => $r->total_rev > 0 ? round(($r->total_profit / $r->total_rev) * 100, 1) : 0,
            ]);

        // Hourly distribution — last 30 days
        $hourly = $saleModel::selectRaw('HOUR(created_at) as hr, COUNT(*) as cnt, SUM(total_amount) as rev')
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->groupBy('hr')
            ->orderBy('hr')
            ->get()
            ->map(fn($r) => ['hour' => (int)$r->hr, 'count' => (int)$r->cnt, 'revenue' => (float)$r->rev]);

        // KPIs: this month vs last month
        $thisMonthRev  = (float) $saleModel::where('created_at', '>=', $now->copy()->startOfMonth())->sum('total_amount');
        $lastMonthRev  = (float) $saleModel::whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->sum('total_amount');
        $thisMonthCnt  = (int)   $saleModel::where('created_at', '>=', $now->copy()->startOfMonth())->count();
        $lastMonthCnt  = (int)   $saleModel::whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->count();
        $growth        = $lastMonthRev > 0 ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : 0;
        $totalProfit   = (float) \App\Models\SaleItem::selectRaw('SUM(total_price - unit_cost * quantity)')->value('SUM(total_price - unit_cost * quantity)') ?? 0;

        return Inertia::render('POS/Analytics/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'months'          => $months,
            'methodBreakdown' => $methodBreakdown,
            'topItems'        => $topItems,
            'hourly'          => $hourly,
            'kpis' => [
                'this_month_revenue' => $thisMonthRev,
                'last_month_revenue' => $lastMonthRev,
                'revenue_growth'     => $growth,
                'this_month_count'   => $thisMonthCnt,
                'last_month_count'   => $lastMonthCnt,
                'total_profit'       => $totalProfit,
            ],
        ]);
    })->name('pos.analytics.index');

    // Sales
    Route::get('/sales', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $totalCount     = \App\Models\Sale::count();
        $completedCount = \App\Models\Sale::where('payment_status', 'paid')->count();
        $cashCount      = \App\Models\Sale::where('payment_method', 'cash')->count();
        $cardCount      = \App\Models\Sale::where('payment_method', 'card')->count();
        $totalRevenue   = \App\Models\Sale::sum('total_amount');

        $totalProfit = \DB::table('sale_items')
            ->selectRaw('SUM(total_price - COALESCE(discount_amount, 0) - (unit_cost * quantity)) as profit')
            ->value('profit') ?? 0;

        return Inertia::render('POS/Sales/Index', [
            'user'       => $user,
            'role'       => $role,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'sales'      => \App\Models\Sale::with('customer', 'user', 'items')->orderBy('created_at', 'desc')->get(),
            'customers'  => \App\Models\Customer::orderBy('first_name')->get(),
            'stats'      => [
                'total_count'     => $totalCount,
                'completed_count' => $completedCount,
                'cash_count'      => $cashCount,
                'card_count'      => $cardCount,
                'total_revenue'   => round((float)$totalRevenue, 2),
                'total_profit'    => round((float)$totalProfit, 2),
            ],
        ]);
    })->name('sales.index');

    Route::get('/sales/{sale}', function (\App\Models\Sale $sale) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $sale->load(['customer', 'user', 'items.product.category']);

        $saleData = $sale->toArray();
        $saleData['total_profit']   = $sale->total_profit ?? 0;
        $saleData['total_cost']     = $sale->total_cost ?? 0;
        $saleData['profit_margin']  = $sale->profit_margin ?? 0;

        return Inertia::render('POS/Sales/Show', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'sale'       => $saleData,
        ]);
    })->name('sales.show');

    // API endpoints for dropdowns
    Route::get('/api/products', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });

    Route::get('/products-and-warehouses', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });
});

// HR Management Routes (accessible to HR and Admin roles)
Route::middleware(['auth', 'role:hr|admin'])->prefix('hr')->name('hr.')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $totalEmployees   = \App\Models\User::count();
        $activeEmployees  = \App\Models\User::where('is_active', true)->count();
        $pendingLeave     = \App\Models\LeaveRequest::where('status', 'pending')->count();

        $stats = [
            'total_employees'        => $totalEmployees,
            'active_employees'       => $activeEmployees,
            'pending_leave_requests' => $pendingLeave,
            'open_positions'         => 0,
        ];

        $recentEmployees = \App\Models\User::with('roles')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
                'email'      => $u->email,
                'roles'      => $u->roles,
                'created_at' => $u->created_at,
            ])
            ->values()
            ->toArray();

        $today = now();
        $upcomingBirthdays = \App\Models\User::whereNotNull('date_of_birth')
            ->with('roles')
            ->get()
            ->filter(function ($u) use ($today) {
                try {
                    $bday = \Carbon\Carbon::parse($u->date_of_birth);
                    $next = $bday->copy()->setYear($today->year);
                    if ($next->isPast()) $next->addYear();
                    return $next->diffInDays($today, false) >= 0
                        && $next->diffInDays($today, false) <= 30;
                } catch (\Throwable $e) {
                    return false;
                }
            })
            ->take(5)
            ->map(fn($u) => [
                'id'            => $u->id,
                'name'          => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
                'roles'         => $u->roles,
                'date_of_birth' => $u->date_of_birth,
            ])
            ->values()
            ->toArray();

        return Inertia::render('HR/Dashboard', [
            'user'               => $user,
            'navigation'         => app(DashboardController::class)->getNavigationForRole($role),
            'stats'              => $stats,
            'recent_employees'   => $recentEmployees,
            'upcoming_birthdays' => $upcomingBirthdays,
        ]);
    })->name('dashboard.index');

    // Recruitment
    Route::get('/recruitment', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('HR/Recruitment/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('recruitment.index');

    // Training
    Route::get('/training', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $programs = TrainingProgram::orderByDesc('start_date')->take(10)->get();

        $today = now()->toDateString();
        $stats = [
            'total' => $programs->count(),
            'active' => $programs->where('end_date', '>=', $today)->count(),
            'completed' => $programs->where('end_date', '<', $today)->count(),
            'enrolled' => 0,
        ];

        return Inertia::render('HR/Training/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'programs' => $programs,
            'stats' => $stats,
        ]);
    })->name('training.index');

    Route::post('/training/programs', [TrainingProgramController::class, 'store'])->name('training.programs.store');
    Route::put('/training/programs/{program}', [TrainingProgramController::class, 'update'])->name('training.programs.update');

    // Leave Management - dedicated HR page
    Route::get('/leave-management', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $leaveRequests = \App\Models\LeaveRequest::with('user')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('HR/LeaveManagement/Index', [
            'user'          => $user,
            'navigation'    => app(DashboardController::class)->getNavigationForRole($role),
            'leaveRequests' => $leaveRequests,
        ]);
    })->name('leave-management.index');

    // Leave Requests - create/store
    Route::post('/leave-requests', [\App\Http\Controllers\HR\LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::put('/leave-requests/{leaveRequest}', [\App\Http\Controllers\HR\LeaveRequestController::class, 'update'])->name('leave-requests.update');

    // Attendance Actions (needed for HR to mark attendance)
    Route::post('/attendance/mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'markAttendance'])->name('attendance.mark');
    Route::post('/attendance/check-out', [\App\Http\Controllers\Admin\AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/bulk-mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');

    // Performance
    Route::get('/performance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $performance = \App\Models\User::with(['roles', 'department', 'position'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'department' => is_object($user->department) ? $user->department->name : ($user->department ?? 'N/A'),
                    'position' => is_object($user->position) ? $user->position->name : ($user->position ?? 'N/A'),
                    'performance_score' => rand(70, 95),
                    'attendance_rate' => rand(85, 98),
                    'tasks_completed' => rand(20, 45),
                    'efficiency' => rand(80, 95)
                ];
            });

        return Inertia::render('Admin/Performance/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'performance' => $performance
        ]);
    })->name('performance.index');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('Admin/Reports', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.index');

    // Report Sub-routes (to avoid 403 when clicking links in Reports page)
    Route::get('/reports/occupancy', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('Admin/Reports/Occupancy', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.occupancy');

    Route::get('/reports/revenue', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('Admin/Reports/Revenue', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.revenue');

    Route::get('/reports/staff', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        // Creating a basic Staff report view or reusing existing if available
        // For now, map to Attendance as a placeholder if Staff report view doesn't exist
        // But list_dir showed Staff.vue in Reports folder
        return Inertia::render('Admin/Reports/Staff', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.staff');


    // Employees
    Route::get('/employees', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $query = \App\Models\User::with(['roles', 'department'])->orderBy('first_name')->orderBy('last_name');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name',  'like', '%' . $request->search . '%')
                  ->orWhere('email',      'like', '%' . $request->search . '%');
            });
        }
        if ($request->department) {
            $query->whereHas('department', fn($q) => $q->where('name', $request->department));
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $employees = $query->paginate(15)->through(fn($u) => [
            'id'          => $u->id,
            'name'        => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
            'email'       => $u->email,
            'phone'       => $u->phone,
            'employee_id' => $u->employee_id,
            'hire_date'   => $u->hire_date ?? $u->created_at,
            'is_active'   => $u->is_active ?? true,
            'salary'      => $u->salary,
            'roles'       => $u->roles,
            'departments' => $u->department ? [$u->department] : [],
        ]);

        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('HR/Employees/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'employees'   => $employees,
            'departments' => $departments,
            'filters'     => [
                'search'     => $request->search     ?? '',
                'department' => $request->department ?? '',
                'status'     => $request->status     ?? '',
            ],
        ]);
    })->name('employees.index');

    Route::get('/employees/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('HR/Employees/Create', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'roles'       => \Spatie\Permission\Models\Role::orderBy('name')->get(['id', 'name']),
            'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('employees.create');

    Route::post('/employees', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'employee_id'           => 'nullable|string|max:50',
            'phone'                 => 'nullable|string|max:30',
            'address'               => 'nullable|string',
            'date_of_birth'         => 'nullable|date',
            'hire_date'             => 'nullable|date',
            'salary'                => 'nullable|numeric|min:0',
            'role_id'               => 'required|exists:roles,id',
            'department_id'         => 'nullable|exists:departments,id',
            'is_active'             => 'boolean',
        ]);

        $nameParts  = explode(' ', trim($validated['name']), 2);
        $employee = \App\Models\User::create([
            'first_name'    => $nameParts[0],
            'last_name'     => $nameParts[1] ?? '',
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => bcrypt($validated['password']),
            'employee_id'   => $validated['employee_id']   ?? null,
            'phone'         => $validated['phone']          ?? null,
            'address'       => $validated['address']        ?? null,
            'date_of_birth' => $validated['date_of_birth']  ?? null,
            'hire_date'     => $validated['hire_date']       ?? null,
            'salary'        => $validated['salary']          ?? null,
            'is_active'     => $validated['is_active']       ?? true,
            'department_id' => $validated['department_id']  ?? null,
        ]);

        $employee->roles()->sync([$validated['role_id']]);

        return redirect()->route('hr.employees.index')->with('success', 'Employee created successfully.');
    })->name('employees.store');

    Route::get('/employees/{employee}', function (\App\Models\User $employee) {
        return redirect()->route('admin.users.show', $employee->id);
    })->name('employees.show');

    Route::get('/employees/{employee}/edit', function (\App\Models\User $employee) {
        return redirect()->route('admin.users.edit', $employee->id);
    })->name('employees.edit');

    Route::delete('/employees/{employee}', function (\App\Models\User $employee) {
        $employee->delete();
        return redirect()->route('hr.employees.index')->with('success', 'Employee deleted successfully.');
    })->name('employees.destroy');

    // Attendance
    Route::get('/attendance', function (\Illuminate\Http\Request $request) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $query = \App\Models\Attendance::with('user')->orderByDesc('date')->orderByDesc('id');

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->employee_id) {
            $query->where('user_id', $request->employee_id);
        }

        $attendances = $query->paginate(20)->through(fn($a) => [
            'id'             => $a->id,
            'date'           => $a->date,
            'check_in_time'  => $a->check_in  ?? $a->check_in_time,
            'check_out_time' => $a->check_out ?? $a->check_out_time,
            'status'         => $a->status,
            'notes'          => $a->notes,
            'user'           => $a->user ? [
                'id'          => $a->user->id,
                'name'        => trim(($a->user->first_name ?? '') . ' ' . ($a->user->last_name ?? $a->user->name ?? '')),
                'employee_id' => $a->user->employee_id,
            ] : null,
        ]);

        $employees = \App\Models\User::orderBy('first_name')
            ->get()
            ->map(fn($u) => [
                'id'   => $u->id,
                'name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
            ]);

        return Inertia::render('HR/Attendance/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'attendances' => $attendances,
            'employees'   => $employees,
            'filters'     => [
                'date_from'   => $request->date_from   ?? '',
                'date_to'     => $request->date_to     ?? '',
                'employee_id' => $request->employee_id ?? '',
            ],
        ]);
    })->name('attendance.index');

    Route::post('/attendance/check-in', function () {
        $userId = auth()->id();
        $today  = now()->toDateString();

        $record = \App\Models\Attendance::firstOrNew([
            'user_id' => $userId,
            'date'    => $today,
        ]);

        if (!$record->exists || !$record->check_in) {
            $time = now()->format('H:i');
            $record->check_in = $time;
            $record->status   = $record->status ?? 'present';
            $record->save();
        }

        return redirect()->back()->with('success', 'Checked in successfully.');
    })->name('attendance.check-in');

    // Payroll Management
    Route::get('/payroll', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $employees = \App\Models\User::with(['roles', 'department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'name'        => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
                'employee_id' => $u->employee_id,
                'email'       => $u->email,
                'is_active'   => $u->is_active ?? true,
                'salary'      => $u->salary,
                'departments' => $u->department ? [$u->department] : [],
                'roles'       => $u->roles,
            ]);

        return Inertia::render('HR/Payroll/Index', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'employees'  => $employees,
        ]);
    })->name('payroll.index');
    Route::put('/payroll/employees/{user}/salary', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        $validated = $request->validate(['salary' => 'required|numeric|min:0']);
        $user->update(['salary' => $validated['salary']]);
        return redirect()->route('hr.payroll.index')->with('success', 'Salary updated successfully.');
    })->name('payroll.update-salary');
    Route::get('/payroll/process', [\App\Http\Controllers\Accountant\PayrollController::class, 'process'])->name('payroll.process');
    Route::get('/payroll/history', [\App\Http\Controllers\Accountant\PayrollController::class, 'history'])->name('payroll.history');
    Route::get('/payroll/export', [\App\Http\Controllers\Accountant\PayrollController::class, 'export'])->name('payroll.export');
    Route::get('/payroll/taxes', [\App\Http\Controllers\Accountant\PayrollController::class, 'taxes'])->name('payroll.taxes');
    Route::post('/payroll/approve', [\App\Http\Controllers\Accountant\PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('/payroll/approve-all', [\App\Http\Controllers\Accountant\PayrollController::class, 'approveAll'])->name('payroll.approve.all');

    Route::get('/departments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $departments = \App\Models\Department::withCount(['users as users_count', 'users as active_employees_count' => function ($q) {
                $q->where('is_active', true);
            }])
            ->with(['manager', 'users' => fn($q) => $q->orderByDesc('created_at')->take(3)])
            ->orderBy('name')
            ->paginate(12)
            ->through(fn($d) => array_merge($d->toArray(), [
                'recent_employees' => $d->users->map(fn($u) => [
                    'id'         => $u->id,
                    'name'       => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? $u->name ?? '')),
                    'created_at' => $u->created_at,
                ])->values()->toArray(),
                'manager' => $d->manager ? [
                    'id'    => $d->manager->id,
                    'name'  => trim(($d->manager->first_name ?? '') . ' ' . ($d->manager->last_name ?? $d->manager->name ?? '')),
                    'email' => $d->manager->email,
                ] : null,
            ]));

        return Inertia::render('HR/Departments/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'departments' => $departments,
        ]);
    })->name('departments.index');

    Route::get('/departments/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('HR/Departments/Create', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
        ]);
    })->name('departments.create');

    Route::post('/departments', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
        ]);
        \App\Models\Department::create($validated);
        return redirect()->route('hr.departments.index')->with('success', 'Department created successfully.');
    })->name('departments.store');

    Route::get('/departments/{department}', function (\App\Models\Department $department) {
        return redirect()->route('hr.departments.index');
    })->name('departments.show');

    Route::get('/departments/{department}/edit', function (\App\Models\Department $department) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';
        return Inertia::render('HR/Departments/Create', [
            'user'       => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'department' => $department,
        ]);
    })->name('departments.edit');

    Route::put('/departments/{department}', function (\Illuminate\Http\Request $request, \App\Models\Department $department) {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
        ]);
        $department->update($validated);
        return redirect()->route('hr.departments.index')->with('success', 'Department updated successfully.');
    })->name('departments.update');

    Route::delete('/departments/{department}', function (\App\Models\Department $department) {
        $department->delete();
        return redirect()->route('hr.departments.index')->with('success', 'Department deleted successfully.');
    })->name('departments.destroy');

    Route::get('/positions', function () {
        return redirect()->route('admin.positions.index');
    })->name('positions.index');
});
