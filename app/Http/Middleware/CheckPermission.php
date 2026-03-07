<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // Get user's permissions through their roles
        $userPermissions = [];
        foreach ($user->roles as $role) {
            $rolePermissions = $role->permissions()->pluck('name')->toArray();
            $userPermissions = array_merge($userPermissions, $rolePermissions);
        }
        $userPermissions = array_unique($userPermissions);

        // Check if user has the required permission
        if (!in_array($permission, $userPermissions)) {
            // Return 403 Forbidden response
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
