<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseValidationService;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
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

        // --- Strict online license check ---
        try {
            $licensed = $this->licenseService->isSystemLicensed();
        } catch (\RuntimeException $e) {
            Log::warning('License check threw RuntimeException; denying access.', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            $licensed = false;
        } catch (\Throwable $e) {
            // Any unexpected error during the check should not silently block users.
            // The grace-period logic inside isSystemLicensed() already handles network failures;
            // only a truly unrecoverable exception lands here.
            Log::error('Unexpected license middleware error.', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            $licensed = false;
        }

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
