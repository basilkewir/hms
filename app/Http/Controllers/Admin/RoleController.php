<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('users')->get();

        // Format roles data for frontend
        $formattedRoles = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'user_count' => $role->users->count(),
                'status' => $role->is_active ? 'active' : 'inactive',
                'key_permissions' => $role->permissions,
                'permissions' => $role->permissions,
                'is_active' => $role->is_active,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ];
        });

        return response()->json($formattedRoles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'required|string',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->is_active ?? true,
        ]);

        // Sync permissions
        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Role created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $role = Role::with(['users', 'permissions'])->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'user_count' => $role->users->count(),
                'status' => $role->is_active ? 'active' : 'inactive',
                'key_permissions' => $role->permissions,
                'permissions' => $role->permissions,
                'is_active' => $role->is_active,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
                'users' => $role->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'description' => 'required|string',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $role->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->is_active ?? true,
        ]);

        // Sync permissions
        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Role updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Prevent deletion of system roles
        $systemRoles = ['admin', 'manager', 'front_desk', 'housekeeping', 'maintenance', 'accountant', 'staff'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system role'
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned users. Please reassign users first.'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all permissions grouped by category
     */
    public function getPermissions()
    {
        $permissions = Permission::getPermissionsByCategory();
        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Get available permissions for a role
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $allPermissions = Permission::getPermissionsByCategory();
        $rolePermissions = $role->permissions()->pluck('name')->toArray();

        // Mark which permissions the role has
        foreach ($allPermissions as $category => $permissions) {
            foreach ($permissions as $name => $permission) {
                $allPermissions[$category][$name]['has_permission'] = in_array($name, $rolePermissions);
            }
        }

        return response()->json([
            'success' => true,
            'permissions' => $allPermissions,
            'role_permissions' => $rolePermissions
        ]);
    }

    /**
     * Get roles by position
     */
    public function getByPosition($positionId)
    {
        $roles = Role::where('position_id', $positionId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, $roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $role->syncPermissions($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
            'permissions' => $role->permissions
        ]);
    }
}
