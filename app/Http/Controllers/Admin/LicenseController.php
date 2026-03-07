<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\License;
use App\Services\LicenseValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class LicenseController extends Controller
{
    public function __construct(private LicenseValidationService $licenseService) {}

    /**
     * Standalone license settings page (linked from sidebar).
     */
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $licenseStatus = $this->licenseService->getLicenseStatus();

        return Inertia::render('Admin/Settings/License', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'isActivated' => $licenseStatus['licensed'],
            'licenseData' => $licenseStatus['licensed'] ? $this->formatLicenseData($licenseStatus['status']) : null,
        ]);
    }

    /**
     * JSON endpoint used by Settings/Index.vue license tab.
     */
    public function info()
    {
        $licenseStatus = $this->licenseService->getLicenseStatus();

        if (!$licenseStatus['licensed']) {
            return response()->json(['licensed' => false]);
        }

        return response()->json(array_merge(
            ['licensed' => true],
            $this->formatLicenseData($licenseStatus['status'])
        ));
    }

    /**
     * Activate / re-activate a license key.
     * Accepts both JSON (from Settings tab fetch) and form POST.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|min:10',
            'hotel_name'  => 'nullable|string|max:255',
        ]);

        $result = $this->licenseService->validateLicense(
            $request->license_key,
            $request->hotel_name ?: config('app.name')
        );

        Cache::forget('license_valid');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['valid'],
                'message' => $result['message'],
            ], $result['valid'] ? 200 : 422);
        }

        if (!$result['valid']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'License activated successfully.');
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(Request $request)
    {
        $result = $this->licenseService->refreshToken();

        Cache::forget('license_valid');

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'License token refreshed successfully.');
    }

    /**
     * Remove the active license from this server.
     */
    public function deactivate(Request $request)
    {
        $this->licenseService->removeLicense();
        Cache::forget('license_valid');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'License removed.']);
        }

        return back()->with('success', 'License removed. Please re-activate to use the system.');
    }

    /**
     * Normalise license_data array for the frontend.
     */
    private function formatLicenseData(array $data): array
    {
        return [
            'license_key'      => $data['license_key']       ?? null,
            'hotel_name'       => $data['hotel_name']         ?? null,
            'license_type'     => $data['license_type']       ?? null,
            'status'           => $data['status']             ?? 'ACTIVE',
            'expires_at'       => $data['expires_at']         ?? null,
            'features'         => $data['features']           ?? [],
            'device_allocation'=> $data['device_allocation']  ?? [],
            'total_used'       => $data['total_used']         ?? 0,
            'total_limit'      => $data['total_limit']        ?? 0,
            'offline_mode'     => $data['offline_mode']       ?? false,
            'validated_at'     => $data['validated_at']       ?? null,
        ];
    }
}
