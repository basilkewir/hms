<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\Hall;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PackageRequest;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $packages = Package::with(['halls', 'reservations'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $allPackages = Package::query()->get(['id', 'is_active', 'is_available']);
        $stats = [
            'total' => $allPackages->count(),
            'active' => $allPackages->where('is_active', true)->count(),
            'available' => $allPackages->where('is_available', true)->count(),
        ];

        return Inertia::render('Admin/Packages/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'packages' => $packages,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $halls = Hall::query()->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Packages/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'halls' => $halls,
        ]);
    }

    /**
     * Store a newly created package.
     */
    public function store(PackageRequest $request)
    {
        $package = Package::create($request->validated());

        if ($request->has('hall_ids')) {
            $package->halls()->sync($request->hall_ids);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->load(['halls', 'reservations', 'groupBookings']);

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        return Inertia::render('Admin/Packages/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'package' => $package,
        ]);
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        $package->load('halls');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';
        $halls = Hall::query()->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Packages/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'package' => $package,
            'halls' => $halls,
        ]);
    }

    /**
     * Update the specified package.
     */
    public function update(PackageRequest $request, Package $package)
    {
        $package->update($request->validated());

        if ($request->has('hall_ids')) {
            $package->halls()->sync($request->hall_ids);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified package.
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Check availability for a package.
     */
    public function availability(Request $request, Package $package)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $bookings = $package->reservations()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('check_in_date', [$startDate, $endDate])
                          ->orWhereBetween('check_out_date', [$startDate, $endDate]);
                    });
                }
            })
            ->count();

        $maxBookings = (int) ($package->max_bookings ?? 0);
        $isAvailable = $maxBookings > 0 ? ($bookings < $maxBookings) : true;

        return response()->json([
            'available' => $isAvailable,
            'bookings' => $bookings,
            'max_bookings' => $package->max_bookings,
            'remaining' => $maxBookings > 0 ? ($maxBookings - $bookings) : null
        ]);
    }
}
