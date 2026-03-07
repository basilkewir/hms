<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search', '');
        $department = $request->input('department', '');
        $role = $request->input('role', '');
        $status = $request->input('status', '');

        // Build query
        $query = User::with(['roles', 'department', 'position'])
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            })
            ->when($department, function ($q) use ($department) {
                // Check if department is a name or ID
                if (is_numeric($department)) {
                    $q->where('department_id', $department);
                } else {
                    $q->whereHas('department', function ($query) use ($department) {
                        $query->where('name', $department);
                    })->orWhere('department', $department); // Fallback for string field
                }
            })
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('employment_status', $status);
            });

        // Get paginated users
        $users = $query->orderBy('first_name')->orderBy('last_name')->paginate(10);

        // Get user statistics
        $userStats = [
            'total' => User::count(),
            'active' => User::where('employment_status', 'active')->count(),
            'pending' => User::where('employment_status', 'pending')->count(),
            'inactive' => User::where('employment_status', 'inactive')->count(),
        ];

        // Calculate staff status for today (on duty, on break, absent)
        $today = Carbon::today();
        $onDuty = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->where('status', '!=', 'rejected')
            ->distinct('user_id')
            ->count('user_id');

        $onBreak = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->whereNotNull('break_start_time')
            ->whereNull('break_end_time')
            ->where('status', '!=', 'rejected')
            ->distinct('user_id')
            ->count('user_id');

        $dayOfWeek = $today->dayOfWeek;
        $scheduledStaff = EmployeeShift::where('is_active', true)
            ->where('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->whereJsonContains('days_of_week', $dayOfWeek)
            ->pluck('user_id')
            ->unique();

        $clockedInStaff = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->where('status', '!=', 'rejected')
            ->pluck('user_id')
            ->unique();

        $absent = $scheduledStaff->diff($clockedInStaff)->count();

        $staffStats = [
            'total' => User::where('is_active', true)->count(),
            'onDuty' => $onDuty,
            'onBreak' => $onBreak,
            'absent' => $absent,
        ];

        // Get departments for filter
        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Determine which view to render based on route
        $user = auth()->user();
        $isManager = $user->hasRole('manager');
        $viewPath = $isManager ? 'Manager/Staff/Index' : 'Admin/Users/Index';

        // Map users data for manager view
        $mappedUsers = $users->through(function($user) use ($today) {
            // Determine current status
            $timeEntry = TimeEntry::where('user_id', $user->id)
                ->whereDate('work_date', $today)
                ->where('status', '!=', 'rejected')
                ->first();

            $currentStatus = 'off_duty';
            if ($timeEntry) {
                if ($timeEntry->clock_in_time && !$timeEntry->clock_out_time) {
                    if ($timeEntry->break_start_time && !$timeEntry->break_end_time) {
                        $currentStatus = 'on_break';
                    } else {
                        $currentStatus = 'on_duty';
                    }
                }
            } else {
                // Check if scheduled but not clocked in
                $dayOfWeek = $today->dayOfWeek;
                $isScheduled = EmployeeShift::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->where('effective_date', '<=', $today)
                    ->where(function ($query) use ($today) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $today);
                    })
                    ->whereJsonContains('days_of_week', $dayOfWeek)
                    ->exists();
                
                if ($isScheduled) {
                    $currentStatus = 'absent';
                }
            }

            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => $user->department?->name ?? $user->department ?? 'N/A',
                'position' => $user->position?->name ?? $user->position_name ?? 'N/A',
                'current_status' => $currentStatus,
                'roles' => $user->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
            ];
        });

        return Inertia::render($viewPath, [
            'user' => $user->load('roles'),
            'staffMembers' => $isManager ? $mappedUsers : $users,
            'users' => $isManager ? null : $users,
            'staffStats' => $isManager ? $staffStats : null,
            'userStats' => $isManager ? null : $userStats,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'role' => $role,
                'status' => $status
            ]
        ]);
    }

    public function create()
    {
        // Get system settings
        $settings = Setting::where('group', 'general')->pluck('value', 'key')->toArray();
        $settings = array_merge([
            'currency' => 'XAF',
            'currency_symbol' => 'FCFA',
            'currency_code' => 'XAF',
            'hotel_name' => 'Grand Hotel Yaoundé',
            'timezone' => 'Africa/Douala'
        ], $settings);

        // Get all departments (for dropdown)
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Users/Create', [
            'user' => auth()->user()->load('roles'),
            'settings' => $settings,
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'employee_id' => 'nullable|string|max:50|unique:users',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hire_date' => 'required|date',
            'pay_type' => 'required|string|in:hourly,monthly,salary',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'role_id' => 'required|exists:roles,id',
            'employment_status' => 'required|string|in:active,inactive,probation,terminated',
            'password' => 'required|string|min:8|confirmed',
            'force_password_change' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get department and position for string fields (backward compatibility)
            $department = Department::findOrFail($request->department_id);
            $position = Position::findOrFail($request->position_id);
            $role = Role::findOrFail($request->role_id);

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'employee_id' => $request->employee_id,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'department' => $department->name, // Keep for backward compatibility
                'position' => $position->name, // Keep for backward compatibility
                'hire_date' => $request->hire_date,
                'pay_type' => $request->pay_type,
                'hourly_rate' => $request->hourly_rate,
                'monthly_rate' => $request->monthly_rate,
                'salary' => $request->salary,
                'employment_status' => $request->employment_status,
                'password' => Hash::make($request->password),
                'is_active' => $request->is_active ?? true,
            ]);

            // Assign role (Spatie uses role name or model)
            $user->assignRole($role->name);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $user = User::with(['roles', 'timeEntries'])->findOrFail($id);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'currentUser' => auth()->user()->load('roles')
        ]);
    }

    public function edit($id)
    {
        $user = User::with(['roles', 'department', 'position'])->findOrFail($id);
        
        // Get all departments (for dropdown)
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Get positions for user's department if set
        $positions = [];
        if ($user->department_id) {
            $positions = Position::where('department_id', $user->department_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        // Get roles for user's position if set
        $roles = [];
        if ($user->position_id) {
            $roles = Role::where('position_id', $user->position_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        // Get system settings
        $settings = Setting::where('group', 'general')->pluck('value', 'key')->toArray();
        $settings = array_merge([
            'currency' => 'XAF',
            'currency_symbol' => 'FCFA',
            'currency_code' => 'XAF',
            'hotel_name' => 'Grand Hotel Yaoundé',
            'timezone' => 'Africa/Douala'
        ], $settings);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles,
            'settings' => $settings,
            'currentUser' => auth()->user()->load('roles')
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $id,
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hire_date' => 'required|date',
            'pay_type' => 'required|string|in:hourly,monthly,salary',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'salary' => 'nullable|numeric|min:0',
            'role_id' => 'required|exists:roles,id',
            'employment_status' => 'required|string|in:active,inactive,probation,terminated',
            'force_password_change' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get department and position for string fields (backward compatibility)
            $department = Department::findOrFail($request->department_id);
            $position = Position::findOrFail($request->position_id);
            $role = Role::findOrFail($request->role_id);

            // Update user
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'employee_id' => $request->employee_id,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'department' => $department->name, // Keep for backward compatibility
                'position' => $position->name, // Keep for backward compatibility
                'hire_date' => $request->hire_date,
                'pay_type' => $request->pay_type,
                'hourly_rate' => $request->hourly_rate,
                'monthly_rate' => $request->monthly_rate,
                'salary' => $request->salary,
                'employment_status' => $request->employment_status,
                'is_active' => $request->is_active ?? true,
            ]);

            // Update password if provided
            if ($request->password) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Update role (Spatie syncRoles expects array of role names)
            $user->syncRoles([$role->name]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $users = User::with('roles')->get();
        
        $filename = "users_export_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Employee ID',
                'Department',
                'Position',
                'Status',
                'Role',
                'Hire Date',
                'Created At'
            ]);
            
            // CSV Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->first_name ?? '',
                    $user->last_name ?? '',
                    $user->email,
                    $user->phone ?? '',
                    $user->employee_id ?? '',
                    $user->department ? str_replace('_', ' ', ucfirst($user->department)) : '',
                    $user->position ?? '',
                    $user->status ?? '',
                    $user->roles->first()->name ?? '',
                    $user->hire_date ?? '',
                    $user->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
