<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks front-desk-only users to the Lite Guest Display page.
 *
 * Users who also hold admin or manager are unaffected.
 * Shell essentials (login, profile, notifications, assets) stay available.
 */
class RestrictFrontDeskToLite
{
    /** Path prefixes (no leading slash) always allowed for locked front-desk users. */
    private const ALLOWED_PREFIXES = [
        'lite',
        'login',
        'logout',
        'user',
        'notifications',
        'forgot-password',
        'reset-password',
        'password',
        'two-factor',
        'email',
        'confirm-password',
        'register',
        'license',
        'up',
        'build',
        'images',
        'css',
        'js',
        'vendor',
        'storage',
        'favicon',
        'robots.txt',
        '_ignition',
        '_debugbar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? Auth::user();

        if (!$user || !$this->isLockedFrontDesk($user)) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        if ($path === '' || $path === 'dashboard') {
            return redirect()->route('lite.dashboard');
        }

        if ($this->isAllowed($path, $request)) {
            return $next($request);
        }

        return $this->deny($request);
    }

    private function isLockedFrontDesk($user): bool
    {
        $roleNames = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()
            : collect($user->roles ?? [])->pluck('name');

        if ($roleNames->intersect(['front_desk', 'frontdesk'])->isEmpty()) {
            return false;
        }

        // Dual-role users keep full access.
        return $roleNames->intersect(['admin', 'manager'])->isEmpty();
    }

    private function isAllowed(string $path, Request $request): bool
    {
        if (is_file(public_path($path))) {
            return true;
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    private function deny(Request $request): Response
    {
        $home = route('lite.dashboard');

        if ($request->header('X-Inertia')) {
            return response()->json([
                'component' => 'Errors/403',
                'props'     => [
                    'message' => 'You do not have permission to access this page.',
                    'status'  => 403,
                ],
                'url'     => $home,
                'version' => null,
            ], 409)->header('X-Inertia-Location', $home);
        }

        if ($request->expectsJson()) {
            abort(403, 'You do not have permission to access this page.');
        }

        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Front desk users can only access the Guest Display page.'
        );
    }
}
