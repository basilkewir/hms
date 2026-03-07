<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get all permissions for the user through their roles
            $userPermissions = [];
            foreach ($user->roles as $role) {
                $rolePermissions = $role->permissions()->pluck('name')->toArray();
                $userPermissions = array_merge($userPermissions, $rolePermissions);
            }
            $userPermissions = array_unique($userPermissions);
            
            // Share permissions with all views
            view()->share('userPermissions', $userPermissions);
            
            // Also share with Inertia
            inertia()->share('userPermissions', $userPermissions);
        }

        return $next($request);
    }
}
