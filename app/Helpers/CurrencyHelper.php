<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    private static $currencySymbols = [
        'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$',
        'CAD' => 'C$', 'CHF' => 'CHF', 'CNY' => '¥', 'SEK' => 'kr', 'NZD' => 'NZ$',
        'MXN' => 'Mex$', 'SGD' => 'S$', 'HKD' => 'HK$', 'NOK' => 'kr', 'TRY' => '₺',
        'RUB' => '₽', 'INR' => '₹', 'BRL' => 'R$', 'ZAR' => 'R', 'KRW' => '₩',
        'PLN' => 'zł', 'CZK' => 'Kč', 'HUF' => 'Ft', 'ILS' => '₪', 'CLP' => 'CLP$',
        'PHP' => '₱', 'AED' => 'د.إ', 'SAR' => '﷼', 'EGP' => '£', 'THB' => '฿',
        'MYR' => 'RM', 'IDR' => 'Rp', 'VND' => '₫', 'PKR' => '₨', 'BGN' => 'лв',
        'HRK' => 'kn', 'RON' => 'lei', 'ISK' => 'kr', 'DKK' => 'kr', 'COP' => 'COL$',
        'PEN' => 'S/', 'UYU' => 'UYU$', 'ARS' => 'AR$', 'BOB' => 'Bs', 'PYG' => '₲',
        'JOD' => 'د.ا', 'KWD' => 'د.ك', 'BHD' => '.د.ب', 'OMR' => '﷼', 'QAR' => '﷼',
        'LBP' => '£', 'SYP' => '£', 'IQD' => 'د.ع', 'IRR' => '﷼', 'AFN' => '؋',
        'AMD' => '֏', 'AZN' => '₼', 'GEL' => '₾', 'KZT' => '₸', 'KGS' => 'лв',
        'TJS' => 'ЅМ', 'TMT' => 'T', 'UZS' => 'лв', 'BDT' => '৳', 'BTN' => 'Nu.',
        'LKR' => '₨', 'MVR' => '.ރ', 'NPR' => '₨', 'MMK' => 'K', 'LAK' => '₭',
        'KHR' => '៛', 'BND' => 'B$', 'FJD' => 'FJ$', 'PGK' => 'K', 'SBD' => 'SI$',
        'TOP' => 'T$', 'VUV' => 'VT', 'WST' => 'WS$', 'XCD' => 'EC$', 'XOF' => 'CFA',
        'XAF' => 'FCFA', 'KMF' => 'CF', 'DJF' => 'Fdj', 'ERN' => 'Nfk', 'ETB' => 'Br',
        'KES' => 'KSh', 'MGA' => 'Ar', 'MWK' => 'MK', 'MUR' => '₨', 'MZN' => 'MT',
        'RWF' => 'R₣', 'SCR' => '₨', 'SOS' => 'S', 'TZS' => 'TSh', 'UGX' => 'USh',
        'ZMW' => 'ZK', 'ZWL' => 'Z$', 'AOA' => 'Kz', 'BWP' => 'P', 'BIF' => 'FBu',
        'CVE' => '$', 'GHS' => 'GH₵', 'GMD' => 'D', 'GNF' => 'FG', 'LRD' => 'L$',
        'LSL' => 'L', 'MAD' => 'د.م.', 'MDL' => 'L', 'MKD' => 'ден', 'MNT' => '₮',
        'NAD' => 'N$', 'NGN' => '₦', 'RSD' => 'Дин.', 'SLL' => 'Le', 'SZL' => 'L',
        'TND' => 'د.ت', 'UAH' => '₴', 'XPF' => '₣', 'YER' => '﷼', 'ALL' => 'L',
        'BAM' => 'KM', 'MRU' => 'UM', 'STN' => 'Db'
    ];

    public static function format($amount, $showSymbol = true)
    {
        $currencyCode = Setting::get('currency', 'USD');
        $currencyPosition = Setting::get('currency_position', 'prefix');
        $symbol = self::$currencySymbols[$currencyCode] ?? $currencyCode;
        
        $formatted = number_format($amount, 2);
        
        if (!$showSymbol) {
            return $formatted;
        }

        if ($currencyPosition === 'suffix') {
            return $formatted . ' ' . $symbol;
        } else {
            return $symbol . $formatted;
        }
    }

    public static function getSymbol()
    {
        $currencyCode = Setting::get('currency', 'USD');
        return self::$currencySymbols[$currencyCode] ?? $currencyCode;
    }

    public static function getCode()
    {
        return Setting::get('currency', 'USD');
    }

    public static function getPosition()
    {
        return Setting::get('currency_position', 'prefix');
    }
}
