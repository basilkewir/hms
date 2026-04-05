<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'hotel_name', 'value' => 'Grand Hotel', 'type' => 'string', 'group' => 'general', 'description' => 'Hotel name'],
            ['key' => 'hotel_address', 'value' => '123 Hotel Street, City, State 12345', 'type' => 'string', 'group' => 'general', 'description' => 'Hotel address'],
            ['key' => 'hotel_phone', 'value' => '+1 (555) 123-4567', 'type' => 'string', 'group' => 'general', 'description' => 'Hotel phone number'],
            ['key' => 'hotel_email', 'value' => 'info@grandhotel.com', 'type' => 'string', 'group' => 'general', 'description' => 'Hotel email address'],
            ['key' => 'timezone', 'value' => 'America/New_York', 'type' => 'string', 'group' => 'general', 'description' => 'Default timezone'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general', 'description' => 'Default currency'],
            ['key' => 'currency_position', 'value' => 'prefix', 'type' => 'string', 'group' => 'general', 'description' => 'Currency position (prefix or suffix)'],
            ['key' => 'supported_currencies', 'value' => json_encode([
                'USD' => 'US Dollar ($)',
                'EUR' => 'Euro (€)',
                'GBP' => 'British Pound (£)',
                'JPY' => 'Japanese Yen (¥)',
                'AUD' => 'Australian Dollar (A$)',
                'CAD' => 'Canadian Dollar (C$)',
                'CHF' => 'Swiss Franc (CHF)',
                'CNY' => 'Chinese Yuan (¥)',
                'SEK' => 'Swedish Krona (kr)',
                'NZD' => 'New Zealand Dollar (NZ$)',
                'MXN' => 'Mexican Peso ($)',
                'SGD' => 'Singapore Dollar (S$)',
                'HKD' => 'Hong Kong Dollar (HK$)',
                'NOK' => 'Norwegian Krone (kr)',
                'TRY' => 'Turkish Lira (₺)',
                'RUB' => 'Russian Ruble (₽)',
                'INR' => 'Indian Rupee (₹)',
                'BRL' => 'Brazilian Real (R$)',
                'ZAR' => 'South African Rand (R)',
                'KRW' => 'South Korean Won (₩)',
                'PLN' => 'Polish Złoty (zł)',
                'CZK' => 'Czech Koruna (Kč)',
                'HUF' => 'Hungarian Forint (Ft)',
                'ILS' => 'Israeli Shekel (₪)',
                'CLP' => 'Chilean Peso ($)',
                'PHP' => 'Philippine Peso (₱)',
                'AED' => 'UAE Dirham (د.إ)',
                'SAR' => 'Saudi Riyal (﷼)',
                'EGP' => 'Egyptian Pound (£)',
                'THB' => 'Thai Baht (฿)',
                'MYR' => 'Malaysian Ringgit (RM)',
                'IDR' => 'Indonesian Rupiah (Rp)',
                'VND' => 'Vietnamese Dong (₫)',
                'PKR' => 'Pakistani Rupee (₨)',
                'BGN' => 'Bulgarian Lev (лв)',
                'HRK' => 'Croatian Kuna (kn)',
                'RON' => 'Romanian Leu (lei)',
                'ISK' => 'Icelandic Króna (kr)',
                'DKK' => 'Danish Krone (kr)',
                'COP' => 'Colombian Peso ($)',
                'PEN' => 'Peruvian Sol (S/)',
                'UYU' => 'Uruguayan Peso ($U)',
                'ARS' => 'Argentine Peso ($)',
                'BOB' => 'Bolivian Boliviano (Bs)',
                'PYG' => 'Paraguayan Guaraní (₲)',
                'JOD' => 'Jordanian Dinar (د.ا)',
                'KWD' => 'Kuwaiti Dinar (د.ك)',
                'BHD' => 'Bahraini Dinar (.د.ب)',
                'OMR' => 'Omani Rial (﷼)',
                'QAR' => 'Qatari Riyal (﷼)',
                'LBP' => 'Lebanese Pound (£)',
                'SYP' => 'Syrian Pound (£)',
                'IQD' => 'Iraqi Dinar (د.ع)',
                'IRR' => 'Iranian Rial (﷼)',
                'AFN' => 'Afghan Afghani (؋)',
                'AMD' => 'Armenian Dram (֏)',
                'AZN' => 'Azerbaijani Manat (₼)',
                'GEL' => 'Georgian Lari (₾)',
                'KZT' => 'Kazakhstani Tenge (₸)',
                'KGS' => 'Kyrgyzstani Som (лв)',
                'TJS' => 'Tajikistani Somoni (ЅМ)',
                'TMT' => 'Turkmenistani Manat (T)',
                'UZS' => 'Uzbekistani Som (лв)',
                'BDT' => 'Bangladeshi Taka (৳)',
                'BTN' => 'Bhutanese Ngultrum (Nu.)',
                'LKR' => 'Sri Lankan Rupee (₨)',
                'MVR' => 'Maldivian Rufiyaa (.ރ)',
                'NPR' => 'Nepalese Rupee (₨)',
                'MMK' => 'Myanmar Kyat (K)',
                'LAK' => 'Lao Kip (₭)',
                'KHR' => 'Cambodian Riel (៛)',
                'BND' => 'Brunei Dollar (B$)',
                'FJD' => 'Fijian Dollar (FJ$)',
                'PGK' => 'Papua New Guinean Kina (K)',
                'SBD' => 'Solomon Islands Dollar (SI$)',
                'TOP' => 'Tongan Paʻanga (T$)',
                'VUV' => 'Vanuatu Vatu (VT)',
                'WST' => 'Samoan Tala (WS$)',
                'XCD' => 'East Caribbean Dollar (EC$)',
                'XOF' => 'West African CFA Franc (CFA)',
                'XAF' => 'Central African CFA Franc (FCFA)',
                'KMF' => 'Comorian Franc (CF)',
                'DJF' => 'Djiboutian Franc (Fdj)',
                'ERN' => 'Eritrean Nakfa (Nfk)',
                'ETB' => 'Ethiopian Birr (Br)',
                'KES' => 'Kenyan Shilling (KSh)',
                'MGA' => 'Malagasy Ariary (Ar)',
                'MWK' => 'Malawian Kwacha (MK)',
                'MUR' => 'Mauritian Rupee (₨)',
                'MZN' => 'Mozambican Metical (MT)',
                'RWF' => 'Rwandan Franc (R₣)',
                'SCR' => 'Seychellois Rupee (₨)',
                'SOS' => 'Somali Shilling (S)',
                'TZS' => 'Tanzanian Shilling (TSh)',
                'UGX' => 'Ugandan Shilling (USh)',
                'ZMW' => 'Zambian Kwacha (ZK)',
                'ZWL' => 'Zimbabwean Dollar (Z$)',
                'AOA' => 'Angolan Kwanza (Kz)',
                'BWP' => 'Botswana Pula (P)',
                'BIF' => 'Burundian Franc (FBu)',
                'CVE' => 'Cape Verdean Escudo ($)',
                'GHS' => 'Ghanaian Cedi (GH₵)',
                'GMD' => 'Gambian Dalasi (D)',
                'GNF' => 'Guinean Franc (FG)',
                'LRD' => 'Liberian Dollar (L$)',
                'LSL' => 'Lesotho Loti (L)',
                'MAD' => 'Moroccan Dirham (د.م.)',
                'MDL' => 'Moldovan Leu (L)',
                'MKD' => 'Macedonian Denar (ден)',
                'MNT' => 'Mongolian Tugrik (₮)',
                'NAD' => 'Namibian Dollar (N$)',
                'NGN' => 'Nigerian Naira (₦)',
                'RSD' => 'Serbian Dinar (Дин.)',
                'SLL' => 'Sierra Leonean Leone (Le)',
                'SZL' => 'Swazi Lilangeni (L)',
                'TND' => 'Tunisian Dinar (د.ت)',
                'UAH' => 'Ukrainian Hryvnia (₴)',
                'XPF' => 'CFP Franc (₣)',
                'YER' => 'Yemeni Rial (﷼)',
                'ALL' => 'Albanian Lek (L)',
                'BAM' => 'Bosnia-Herzegovina Convertible Mark (KM)',
                'MRU' => 'Mauritanian Ouguiya (UM)',
                'STN' => 'São Tomé and Príncipe Dobra (Db)'
            ]), 'type' => 'json', 'group' => 'general', 'description' => 'List of supported currencies'],

            // Security Settings
            ['key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Session timeout in minutes'],
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'group' => 'security', 'description' => 'Minimum password length'],
            ['key' => 'require_2fa', 'value' => '0', 'type' => 'boolean', 'group' => 'security', 'description' => 'Require two-factor authentication'],
            ['key' => 'force_password_change', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'description' => 'Force password change on first login'],
            ['key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'security', 'description' => 'Maximum login attempts before lockout'],

            // IPTV Settings — Xtream Codes server
            ['key' => 'xtream_url',              'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Xtream Codes server base URL (e.g. http://xtream.example.com:8080)'],
            ['key' => 'xtream_use_https',        'value' => '0',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Use HTTPS for Xtream Codes API'],
            // Default/global Xtream credentials — per-device overrides stored in iptv_devices.pushed_settings
            ['key' => 'xtream_username',         'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Default Xtream Codes username (per-device can override)'],
            ['key' => 'xtream_password',         'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Default Xtream Codes password (per-device can override)'],

            // IPTV Settings — Weather widget
            ['key' => 'weather_api_key',         'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'OpenWeatherMap API key for TV weather widget'],
            ['key' => 'weather_city',            'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Default city for weather widget (e.g. Dubai)'],
            ['key' => 'weather_units',           'value' => 'metric',  'type' => 'string',  'group' => 'iptv', 'description' => 'Temperature units: metric (°C) or imperial (°F)'],
            ['key' => 'weather_enabled',         'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Show weather widget on TV home screen'],

            // IPTV Settings — Hotel branding on TV
            ['key' => 'hotel_welcome_message',   'value' => 'Welcome to our Hotel', 'type' => 'string',  'group' => 'iptv', 'description' => 'Welcome message shown on TV home screen'],
            ['key' => 'hotel_primary_color',     'value' => '#FFD700', 'type' => 'string',  'group' => 'iptv', 'description' => 'Primary accent color on TV UI (hex)'],

            // IPTV Settings — TV UI & behaviour
            ['key' => 'iptv_ui_theme',           'value' => 'dark',    'type' => 'string',  'group' => 'iptv', 'description' => 'TV app UI theme: dark or light'],
            ['key' => 'iptv_show_epg',           'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Show Electronic Programme Guide on TV'],
            ['key' => 'iptv_auto_launch_seconds','value' => '15',      'type' => 'integer', 'group' => 'iptv', 'description' => 'Seconds before auto-launching IPTV player on boot'],
            ['key' => 'iptv_show_clock',         'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Show clock on TV home screen'],
            ['key' => 'iptv_show_room_number',   'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Show room number on TV home screen'],
            ['key' => 'iptv_enable_vod',         'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Enable Video On Demand tab in TV app'],
            ['key' => 'iptv_enable_series',      'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Enable Series tab in TV app'],
            ['key' => 'iptv_enable_radio',       'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Enable Radio tab in TV app'],
            ['key' => 'iptv_parental_pin',       'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Global parental control PIN (4 digits) – empty = disabled'],
            ['key' => 'admin_pin',               'value' => '1234',    'type' => 'string',  'group' => 'iptv', 'description' => 'Admin PIN to access TV settings panel'],

            // IPTV Settings — Legacy (kept for compatibility)
            ['key' => 'iptv_server_url',         'value' => '',        'type' => 'string',  'group' => 'iptv', 'description' => 'Legacy IPTV server URL (use xtream_url instead)'],
            ['key' => 'default_channel_package', 'value' => 'premium', 'type' => 'string',  'group' => 'iptv', 'description' => 'Default channel package'],
            ['key' => 'enable_vod',              'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Enable video on demand'],
            ['key' => 'enable_parental_controls','value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Enable parental controls'],
            ['key' => 'auto_provision_rooms',    'value' => '1',       'type' => 'boolean', 'group' => 'iptv', 'description' => 'Auto-provision IPTV for new rooms'],

            // Backup Settings
            ['key' => 'backup_frequency', 'value' => 'daily', 'type' => 'string', 'group' => 'backup', 'description' => 'Backup frequency'],
            ['key' => 'backup_retention_days', 'value' => '30', 'type' => 'integer', 'group' => 'backup', 'description' => 'Backup retention in days'],
            ['key' => 'backup_location', 'value' => 'local', 'type' => 'string', 'group' => 'backup', 'description' => 'Backup storage location'],
            ['key' => 'enable_auto_backup', 'value' => '1', 'type' => 'boolean', 'group' => 'backup', 'description' => 'Enable automatic backups'],

            // Guest & Discount Settings
            ['key' => 'auto_apply_guest_type_discount', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Automatically apply guest type discount when creating reservations'],
            ['key' => 'auto_apply_vip_discount', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'description' => 'Automatically apply VIP discount for VIP guests'],
            ['key' => 'vip_discount_percentage', 'value' => '10', 'type' => 'string', 'group' => 'general', 'description' => 'Default VIP discount percentage'],
            ['key' => 'discount_combination_mode', 'value' => 'add', 'type' => 'string', 'group' => 'general', 'description' => 'How to combine discounts: "add" (sum all) or "override" (manual overrides automatic)'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
