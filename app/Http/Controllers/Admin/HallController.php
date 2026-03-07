<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Requests\Admin\HallRequest;
use App\Models\Hall;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HallController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $halls = Hall::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Halls/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'halls'       => $halls,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function create()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Halls/Create', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
        ]);
    }

    public function store(HallRequest $request)
    {
        $hall = Hall::create($request->validated());
        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.halls.index' : 'admin.halls.index';
        return redirect()->route($indexRoute)->with('success', 'Hall created successfully.');
    }

    public function show(Hall $hall)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Halls/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'hall'        => $hall,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function edit(Hall $hall)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/Halls/Edit', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'hall'        => $hall,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function update(HallRequest $request, Hall $hall)
    {
        $hall->update($request->validated());
        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.halls.index' : 'admin.halls.index';
        return redirect()->route($indexRoute)->with('success', 'Hall updated successfully.');
    }

    public function destroy(Hall $hall)
    {
        $hall->delete();
        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.halls.index' : 'admin.halls.index';
        return redirect()->route($indexRoute)->with('success', 'Hall deleted successfully.');
    }

    public function availability(Request $request)
    {
        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $availableHalls = Hall::active()
            ->whereDoesntHave('reservations', function ($query) use ($date, $startTime, $endTime) {
                $query->whereDate('start_date', $date)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereTime('start_time', '<', $endTime)
                          ->whereTime('end_time', '>', $startTime);
                    });
            })
            ->get();

        return response()->json($availableHalls);
    }
}
