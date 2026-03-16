<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // General settings (raw key/value pairs from DB)
        $generalSettings = Setting::where('group', 'general')->get();
        $settings = [];
        foreach ($generalSettings as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        // Ensure currency keys always exist and are in sync with admin settings
        // Admin Settings writes 'currency' and 'currency_position' keys via Setting::set()
        $settings['currency'] = Setting::get('currency', $settings['currency'] ?? 'USD');
        $settings['currency_position'] = Setting::get('currency_position', $settings['currency_position'] ?? 'prefix');

        // Structured hotel settings for frontend (all from real DB values, aligned with admin keys)
        $hotelSettings = [
            'currency' => [
                'code' => $settings['currency'],
                'symbol' => null, // frontend derives symbol from code to keep single source of truth
                'position' => $settings['currency_position'],
                'thousand_separator' => Setting::get('thousand_separator'),
                'decimal_separator' => Setting::get('decimal_separator'),
                'decimals' => (int) Setting::get('currency_decimals', 2),
            ],
            'tax' => [
                'tax_rate' => (float) Setting::get('tax_rate', 0),
                'service_charge_rate' => (float) Setting::get('service_charge_rate', 0),
                'city_tax_rate' => (float) Setting::get('city_tax_rate', 0),
            ],
            'loyalty' => [
                'enabled' => (bool) Setting::get('loyalty_enabled', false),
                'points_per_currency' => (float) Setting::get('loyalty_points_per_currency', 0),
                'currency_per_point' => (float) Setting::get('loyalty_currency_per_point', 0),
                'tier_system_enabled' => (bool) Setting::get('loyalty_tier_system_enabled', false),
            ],
        ];

        // Roles & permissions via Spatie (only real data from DB)
        $roles = [];
        $permissions = [];

        if ($user) {
            $roles = $user->getRoleNames()->values()->all();
            $permissions = $user->getAllPermissions()
                ->pluck('name')
                ->values()
                ->all();
        }

        // Notifications placeholder (wired for later real implementation)
        $notifications = [
            'unread_count' => 0,
        ];

        return [
            ...parent::share($request),
            'settings' => $settings,
            'hotelSettings' => $hotelSettings,
            'branding' => [
                'hotel_name' => Setting::get('hotel_name'),
                'hotel_logo' => Setting::get('hotel_logo'),
                'hotel_website' => Setting::get('hotel_website', ''),
                'contact' => [
                    'email' => Setting::get('hotel_email'),
                    'phone' => Setting::get('hotel_phone'),
                    'address' => Setting::get('hotel_address'),
                    'social' => [
                        'facebook' => Setting::get('facebook_url'),
                        'instagram' => Setting::get('instagram_url'),
                        'twitter' => Setting::get('twitter_url'),
                    ],
                ],
                'booking_engine' => [
                    'url' => Setting::get('booking_engine_url'),
                ],
            ],
            'auth' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'notifications' => $notifications,
        ];
    }
}
