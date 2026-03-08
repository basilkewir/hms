<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;

class ShareSettings
{
    public function handle(Request $request, Closure $next)
    {
        // Get all settings
        $allSettings = Setting::all();

        $settings = [];
        foreach ($allSettings as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        // Use the 'currency' field as the primary currency code (XAF)
        // Fall back to currency_code if not available
        $currencyCode = $settings['currency'] ?? $settings['currency_code'] ?? 'USD';

        // Get appropriate symbol for the currency
        $currencySymbol = $this->getCurrencySymbol($currencyCode);

        // Structure hotel settings for frontend
        $hotelSettings = [
            'name' => $settings['hotel_name'] ?? 'Hotel',
            'address' => $settings['hotel_address'] ?? '',
            'currency' => [
                'code' => $currencyCode,
                'symbol' => $currencySymbol,
                'position' => $settings['currency_position'] ?? 'prefix',
                'decimals' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ]
        ];

        // Share both flat settings and structured hotel settings
        Inertia::share('settings', $settings);
        Inertia::share('hotelSettings', $hotelSettings);

        return $next($request);
    }

    /**
     * Get currency symbol for given currency code
     */
    private function getCurrencySymbol($currencyCode)
    {
        $symbols = [
            'XAF' => 'FCFA',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'SEK' => 'kr',
            'NZD' => 'NZ$',
            'MXN' => 'Mex$',
            'SGD' => 'S$',
            'HKD' => 'HK$',
            'NOK' => 'kr',
            'TRY' => '₺',
            'RUB' => '₽',
            'INR' => '₹',
            'BRL' => 'R$',
            'ZAR' => 'R',
            'KRW' => '₩',
            'PLN' => 'zł',
            'CZK' => 'Kč',
            'HUF' => 'Ft',
            'ILS' => '₪',
            'CLP' => 'CLP$',
            'PHP' => '₱',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'EGP' => '£',
            'THB' => '฿',
            'MYR' => 'RM',
            'IDR' => 'Rp',
            'VND' => '₫',
            'PKR' => '₨',
            'BGN' => 'лв',
            'HRK' => 'kn',
            'RON' => 'lei',
            'ISK' => 'kr',
            'DKK' => 'kr',
            'COP' => 'COL$',
            'PEN' => 'S/',
            'UYU' => 'UYU$',
            'ARS' => 'AR$',
            'BOB' => 'Bs',
            'PYG' => '₲',
            'JOD' => 'د.ا',
            'KWD' => 'د.ك',
            'BHD' => '.د.ب',
            'OMR' => '﷼',
            'QAR' => '﷼',
            'LBP' => '£',
            'SYP' => '£',
            'IQD' => 'د.ع',
            'IRR' => '﷼',
            'AFN' => '؋',
            'AMD' => '֏',
            'AZN' => '₼',
            'GEL' => '₾',
            'KZT' => '₸',
            'KGS' => 'лв',
            'TJS' => 'ЅМ',
            'TMT' => 'T',
            'UZS' => 'лв',
            'BDT' => '৳',
            'BTN' => 'Nu.',
            'LKR' => '₨',
            'MVR' => '.ރ',
            'NPR' => '₨',
            'MMK' => 'K',
            'LAK' => '₭',
            'KHR' => '៛',
            'BND' => 'B$',
            'FJD' => 'F$',
            'SBD' => 'SI$',
            'VUV' => 'VT',
            'WST' => 'WS$',
            'TOP' => 'T$',
            'SCR' => '₨',
            'MUR' => '₨',
            'KES' => 'KSh',
            'UGX' => 'USh',
            'TZS' => 'TSh',
            'RWF' => 'RWF',
            'BIF' => 'FBu',
            'DJF' => 'Fdj',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'SOS' => 'S',
            'GMD' => 'D',
            'GNF' => 'FG',
            'LRD' => 'L$',
            'SLL' => 'Le',
            'GHS' => 'GH₵',
            'NGN' => '₦',
            'XOF' => 'CFA',
            'XAF' => 'FCFA',
            'XCF' => 'CFP',
            'XPF' => 'CFPF',
            'CVE' => 'Esc',
            'STD' => 'Db',
            'MZN' => 'MTn',
            'ZMW' => 'ZK',
            'MWK' => 'MK',
            'BWP' => 'P',
            'SZL' => 'E',
            'LSL' => 'L',
            'NAD' => 'N$',
            'AOA' => 'Kz',
            'ZWL' => 'Z$',
        ];

        return $symbols[$currencyCode] ?? $currencyCode;
    }
}
