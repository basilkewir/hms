<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseValidationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    /** Cache TTL for license validity (seconds). 12 hours. */
    private const CACHE_TTL = 43200;

    public function __construct(private LicenseValidationService $licenseService) {}

    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        // --- Exempt path prefixes (no license check needed) ---
        // NOTE: 'login' is intentionally NOT exempt — the license must be valid
        // before anyone can even see the login screen.
        $exemptPrefixes = [
            'license',       // /license/activate, /license/info (the activation gate itself)
            'logout',
            'password',
            'forgot-password',
            'reset-password',
            '_ignition',     // debug/dev tool
            '_debugbar',
            'vendor',
            'storage',
            'api',           // API routes handled separately
            'up',            // health-check
        ];

        foreach ($exemptPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        // --- Offline-first license check ---
        $licensed = Cache::remember('license_valid', self::CACHE_TTL, function () {
            return $this->licenseService->isSystemLicensed();
        });

        if ($licensed) {
            return $next($request);
        }

        // --- Not licensed: block access ---

        // Inertia requests get a proper Inertia redirect (no full page reload)
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
                'error'    => 'unlicensed',
                'message'  => 'This system requires a valid license. Please activate your license.',
                'redirect' => route('license.activate'),
            ], 403);
        }

        return redirect()->route('license.activate')
            ->with('error', 'This system requires a valid license to operate.');
    }
}
