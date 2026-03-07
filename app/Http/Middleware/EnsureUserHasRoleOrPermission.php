<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null, string $permission = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Admin always has access
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check role if provided
        if ($role && $user->hasRole($role)) {
            return $next($request);
        }

        // Check permission if provided
        if ($permission && $user->can($permission)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
