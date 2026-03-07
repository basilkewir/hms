<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\LicenseValidationService;
use App\Services\ThemeService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseValidationService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        // Convert to a more usable format for the frontend
        $formattedSettings = [];
        foreach ($settings as $group => $groupSettings) {
            $formattedSettings[$group] = [];
            foreach ($groupSettings as $setting) {
                $formattedSettings[$group][$setting->key] = $setting->value;
            }
        }

        // Get real license data from the license service
        $licenseStatus = $this->licenseService->getLicenseStatus();
        $licenseData = $licenseStatus['licensed'] ? $licenseStatus['status'] : null;

        $user = Auth::check() ? Auth::user()->load(['roles']) : null;

        // Use Admin/Settings for both admin and manager (DashboardLayout handles role-based navigation)
        return Inertia::render('Admin/Settings', [
            'user' => $user,
            'settings' => $formattedSettings,
            'allSettings' => $settings,
            'licenseData' => $licenseData
        ]);
    }

    public function update(Request $request)
    {
        try {
            // Handle file upload for logo
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoData = base64_encode(file_get_contents($logo->getRealPath()));
                $logoType = $logo->getClientMimeType();
                $logoBase64 = 'data:' . $logoType . ';base64,' . $logoData;

                // Save logo to settings
                Setting::set('hotel_logo', $logoBase64, 'string', 'general');
            }

            // Handle regular settings
            $settingsData = $request->input('settings');

            if (!$settingsData || !is_array($settingsData)) {
                // If only logo was uploaded, return success
                if ($request->hasFile('logo')) {
                    return response()->json([
                        'message' => 'Logo uploaded successfully',
                        'debug' => [
                            'logo_uploaded' => true
                        ]
                    ]);
                }

                return response()->json(['error' => 'Settings are required'], 422);
            }

        $themeSettingsSaved = [];
        $totalSettingsSaved = 0;

        foreach ($settingsData as $key => $value) {
            if (is_array($value) && array_key_exists('value', $value)) {
                Setting::set(
                    $key,
                    $value['value'],
                    $value['type'] ?? 'string',
                    $value['group'] ?? 'general',
                    $value['description'] ?? null
                );
            } else {
                // Determine the group based on the key prefix
                $group = 'general'; // default group

                if (Str::startsWith($key, 'integration.')) {
                    $group = 'integration';
                } elseif (Str::startsWith($key, 'theme_')) {
                    $group = 'theme';
                    $themeSettingsSaved[$key] = $value;
                } elseif (Str::startsWith($key, 'security.')) {
                    $group = 'security';
                } elseif (Str::startsWith($key, 'backup.')) {
                    $group = 'backup';
                } elseif (Str::startsWith($key, 'iptv.')) {
                    $group = 'iptv';
                } elseif (Str::startsWith($key, 'pos_')) {
                    $group = 'pos';
                } elseif (Str::startsWith($key, 'frontdesk_')) {
                    $group = 'frontdesk';
                }

                // Convert checkbox values (strings 'true'/'false' or '0'/'1') to proper booleans
                if (in_array($value, ['true', 'false'])) {
                    $value = $value === 'true';
                } elseif (in_array($value, ['0', '1'])) {
                    $value = (bool) $value;
                }

                Setting::set($key, $value, 'string', $group);
                $totalSettingsSaved++;
            }
        }

        // Log theme settings for debugging
        if (!empty($themeSettingsSaved)) {
            Log::info('Theme settings saved to database:', $themeSettingsSaved);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'debug' => [
                'total_settings_saved' => $totalSettingsSaved,
                'theme_settings_saved' => count($themeSettingsSaved),
                'theme_settings' => $themeSettingsSaved
            ]
        ]);
        } catch (\Exception $e) {
            Log::error('Error saving settings: ' . $e->getMessage());
            return response()->json(['error' => 'Error saving settings: ' . $e->getMessage()], 500);
        }
    }

    public function getSettings($group = null)
    {
        $query = Setting::query();

        if ($group) {
            $query->where('group', $group);
        }

        return response()->json($query->get());
    }

    /**
     * Get general settings including currency
     */
    public function getGeneralSettings()
    {
        $settings = Setting::where('group', 'general')->get();

        $formattedSettings = [];
        foreach ($settings as $setting) {
            $formattedSettings[$setting->key] = $setting->value;
        }

        return response()->json($formattedSettings);
    }

    /**
     * Get theme settings
     */
    public function getThemeSettings()
    {
        $settings = Setting::where('group', 'theme')->get();

        $formattedSettings = [];
        foreach ($settings as $setting) {
            $formattedSettings[$setting->key] = $setting->value;
        }

        // Merge with defaults if any settings are missing
        $defaults = [
            'theme_mode' => 'dark',
            'theme_primary_color' => '#facc15',
            'theme_secondary_color' => '#3b82f6',
            'theme_success_color' => '#22c55e',
            'theme_warning_color' => '#f59e0b',
            'theme_danger_color' => '#ef4444',
            'theme_background_color' => '#0b0b0b',
            'theme_sidebar_color' => '#0f172a',
            'theme_card_color' => '#111827',
            'theme_text_primary' => '#f3f4f6',
            'theme_text_secondary' => '#9ca3af',
            'theme_text_tertiary' => '#6b7280',
            'theme_border_color' => '#374151',
            'theme_radius' => '0.5rem',
            'theme_shadow' => '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            'theme_transition' => 'all 0.3s ease-in-out'
        ];

        $themeSettings = array_merge($defaults, $formattedSettings);

        return response()->json($themeSettings);
    }

    /**
     * Update print settings for POS and Front Desk
     */
    public function updatePrintSettings(Request $request)
    {
        $settings = [
            'pos_print_paper_width',
            'pos_print_font_size',
            'pos_print_show_logo',
            'frontdesk_print_paper_width',
            'frontdesk_print_font_size',
            'frontdesk_print_show_logo',
        ];

        foreach ($settings as $key) {
            if ($request->has($key)) {
                $value = is_bool($request->input($key))
                    ? ($request->input($key) ? '1' : '0')
                    : $request->input($key);
                Setting::set($key, $value, 'string', 'print');
            }
        }

        return redirect()->route('admin.settings.print')->with('success', 'Print settings updated successfully');
    }
}
