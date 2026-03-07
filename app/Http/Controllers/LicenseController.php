<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;
use Inertia\Inertia;
use Carbon\Carbon;

class LicenseController extends Controller
{
    public function index()
    {
        $licenses = License::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/IPTV/Licenses/Index', [
            'user' => auth()->user()->load('roles'),
            'licenses' => $licenses
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/IPTV/Licenses/Create', [
            'user' => auth()->user()->load('roles')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'organization' => 'nullable|string|max:255',
            'license_type' => 'required|in:iptv,hotel_management,premium',
            'product_name' => 'required|string|max:255',
            'max_devices' => 'required|integer|min:1|max:1000',
            'max_rooms' => 'required|integer|min:1|max:10000',
            'max_channels' => 'required|integer|min:1|max:10000',
            'vod_enabled' => 'boolean',
            'premium_features' => 'boolean',
            'allowed_features' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
            'max_activations' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string'
        ]);

        $license = License::createLicense($request->all());

        return response()->json([
            'success' => true,
            'message' => 'License created successfully',
            'license' => $license
        ]);
    }

    public function show(License $license)
    {
        return Inertia::render('Admin/IPTV/Licenses/Show', [
            'user' => auth()->user()->load('roles'),
            'license' => $license
        ]);
    }

    public function edit(License $license)
    {
        return Inertia::render('Admin/IPTV/Licenses/Edit', [
            'user' => auth()->user()->load('roles'),
            'license' => $license
        ]);
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'organization' => 'nullable|string|max:255',
            'max_devices' => 'required|integer|min:1|max:1000',
            'max_rooms' => 'required|integer|min:1|max:10000',
            'max_channels' => 'required|integer|min:1|max:10000',
            'vod_enabled' => 'boolean',
            'premium_features' => 'boolean',
            'allowed_features' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'max_activations' => 'required|integer|min:1|max:10',
            'status' => 'required|in:active,expired,suspended,revoked',
            'notes' => 'nullable|string'
        ]);

        $license->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'License updated successfully'
        ]);
    }

    public function destroy(License $license)
    {
        $license->delete();

        return response()->json([
            'success' => true,
            'message' => 'License deleted successfully'
        ]);
    }

    public function validateLicense(Request $request)
    {
        $validated = $this->validate($request, [
            'license_key' => 'required|string',
            'device_id' => 'nullable|string',
            'hardware_fingerprint' => 'nullable|string'
        ]);

        $license = License::where('license_key', $validated['license_key'])->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'message' => 'License not found'
            ], 404);
        }

        if (!$license->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'License is not valid or has expired',
                'status' => $license->status
            ], 400);
        }

        $license->validate();

        return response()->json([
            'valid' => true,
            'license' => [
                'license_key' => $license->license_key,
                'license_type' => $license->license_type,
                'product_name' => $license->product_name,
                'max_devices' => $license->max_devices,
                'max_rooms' => $license->max_rooms,
                'max_channels' => $license->max_channels,
                'vod_enabled' => $license->vod_enabled,
                'premium_features' => $license->premium_features,
                'allowed_features' => $license->allowed_features,
                'expires_at' => $license->expires_at,
                'remaining_days' => $license->remaining_days
            ]
        ]);
    }

    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'activation_code' => 'required|string',
            'device_info' => 'nullable|array'
        ]);

        $license = License::where('license_key', $request->license_key)
                         ->where('activation_code', $request->activation_code)
                         ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key or activation code'
            ], 404);
        }

        if (!$license->canActivateDevice()) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum activations reached for this license'
            ], 400);
        }

        if ($license->activate($request->device_info)) {
            return response()->json([
                'success' => true,
                'message' => 'License activated successfully',
                'license' => [
                    'license_key' => $license->license_key,
                    'max_devices' => $license->max_devices,
                    'max_rooms' => $license->max_rooms,
                    'max_channels' => $license->max_channels,
                    'vod_enabled' => $license->vod_enabled,
                    'premium_features' => $license->premium_features,
                    'allowed_features' => $license->allowed_features
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to activate license'
        ], 500);
    }
}
