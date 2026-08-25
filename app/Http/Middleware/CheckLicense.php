<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    public function handle(Request $request, Closure $next)
    {
        $hasValidLicense = License::where('status', License::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasValidLicense) {
            return $next($request);
        }

        $path = $request->path();

        $exemptPrefixes = [
            'license',       // /license/activate, /license/info (the activation gate itself)
            'logout',
            'password',
            'forgot-password',
            'reset-password',
            '_ignition',
            '_debugbar',
            'vendor',
            'storage',
            'api',
            'up',
        ];

        foreach ($exemptPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        if ($request->header('X-Inertia')) {
            return response()->json([
                'component' => 'Auth/LicenseActivate',
                'props'     => [],
                'url'       => route('license.activate'),
                'version'   => null,
            ], 409)->header('X-Inertia-Location', route('license.activate'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error'   => 'License required',
                'code'    => 'LICENSE_REQUIRED',
                'message' => 'This system requires a valid license. Please activate your license.',
                'redirect' => route('license.activate'),
            ], 403);
        }

        return redirect()->route('license.activate')
            ->with('error', 'This system requires a valid license to operate.');
    }
}
