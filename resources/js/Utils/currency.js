import { usePage } from '@inertiajs/vue3'

/**
 * Format a monetary amount using the currency settings configured
 * on the Admin Settings page (settings.currency, settings.currency_position).
 *
 * This reads from Inertia page props on every call so changes are
 * immediately reflected across all pages without manual re‑initialization.
 */
export function formatCurrency(amount, overrideCurrencyCode = null, overridePosition = null) {
    const page = safeGetPage()
    const flatSettings = page?.props?.settings || {}
    const hotelSettings = page?.props?.hotelSettings || {}

    // Prefer structured hotelSettings if available, fall back to flat settings
    const currencyConfig = hotelSettings.currency || {}

    const currencyCode =
        overrideCurrencyCode ||
        currencyConfig.code ||
        flatSettings.currency ||
        'USD'

    // Admin Settings uses "prefix"/"suffix"
    const storedPosition =
        overridePosition ||
        flatSettings.currency_position ||
        (currencyConfig.position === 'after' ? 'suffix' : 'prefix')

    const decimals =
        typeof currencyConfig.decimals === 'number'
            ? currencyConfig.decimals
            : 2

    const thousandSeparator =
        currencyConfig.thousand_separator || ','

    const decimalSeparator =
        currencyConfig.decimal_separator || '.'

    // Normalize amount
    const numAmount =
        typeof amount === 'number'
            ? amount
            : parseFloat(amount || 0) || 0

    // Base formatting (use a neutral locale then replace separators)
    let formattedNumber = Number(numAmount).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })

    // Replace separators to match settings
    if (thousandSeparator !== ',' || decimalSeparator !== '.') {
        // Temporarily swap to avoid conflicts
        formattedNumber = formattedNumber
            .replace(/,/g, '__THOUSAND__')
            .replace(/\./g, '__DECIMAL__')
            .replace(/__THOUSAND__/g, thousandSeparator)
            .replace(/__DECIMAL__/g, decimalSeparator)
    }

    const symbol = getCurrencySymbol(currencyCode)

    if (storedPosition === 'suffix') {
        return `${formattedNumber} ${symbol}`
    }

    return `${symbol}${formattedNumber}`
}

function safeGetPage() {
    try {
        return usePage()
    } catch {
        return null
    }
}

export function getCurrencySymbol(currency = null) {
    // Get currency symbol for the specified currency code
    const finalCurrency = currency || 'USD'

    // Map of currency codes to their symbols
    const currencySymbols = {
        'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'AUD': 'A$',
        'CAD': 'C$', 'CHF': 'CHF', 'CNY': '¥', 'SEK': 'kr', 'NZD': 'NZ$',
        'MXN': 'Mex$', 'SGD': 'S$', 'HKD': 'HK$', 'NOK': 'kr', 'TRY': '₺',
        'RUB': '₽', 'INR': '₹', 'BRL': 'R$', 'ZAR': 'R', 'KRW': '₩',
        'PLN': 'zł', 'CZK': 'Kč', 'HUF': 'Ft', 'ILS': '₪', 'CLP': 'CLP$',
        'PHP': '₱', 'AED': 'د.إ', 'SAR': '﷼', 'EGP': '£', 'THB': '฿',
        'MYR': 'RM', 'IDR': 'Rp', 'VND': '₫', 'PKR': '₨', 'BGN': 'лв',
        'HRK': 'kn', 'RON': 'lei', 'ISK': 'kr', 'DKK': 'kr', 'COP': 'COL$',
        'PEN': 'S/', 'UYU': 'UYU$', 'ARS': 'AR$', 'BOB': 'Bs', 'PYG': '₲',
        'JOD': 'د.ا', 'KWD': 'د.ك', 'BHD': '.د.ب', 'OMR': '﷼', 'QAR': '﷼',
        'LBP': '£', 'SYP': '£', 'IQD': 'د.ع', 'IRR': '﷼', 'AFN': '؋',
        'AMD': '֏', 'AZN': '₼', 'GEL': '₾', 'KZT': '₸', 'KGS': 'лв',
        'TJS': 'ЅМ', 'TMT': 'T', 'UZS': 'лв', 'BDT': '৳', 'BTN': 'Nu.',
        'LKR': '₨', 'MVR': '.ރ', 'NPR': '₨', 'MMK': 'K', 'LAK': '₭',
        'KHR': '៛', 'BND': 'B$', 'FJD': 'FJ$', 'PGK': 'K', 'SBD': 'SI$',
        'TOP': 'T$', 'VUV': 'VT', 'WST': 'WS$', 'XCD': 'EC$', 'XOF': 'CFA',
        'XAF': 'FCFA', 'KMF': 'CF', 'DJF': 'Fdj', 'ERN': 'Nfk', 'ETB': 'Br',
        'KES': 'KSh', 'MGA': 'Ar', 'MWK': 'MK', 'MUR': '₨', 'MZN': 'MT',
        'RWF': 'R₣', 'SCR': '₨', 'SOS': 'S', 'TZS': 'TSh', 'UGX': 'USh',
        'ZMW': 'ZK', 'ZWL': 'Z$', 'AOA': 'Kz', 'BWP': 'P', 'BIF': 'FBu',
        'CVE': '$', 'GHS': 'GH₵', 'GMD': 'D', 'GNF': 'FG', 'LRD': 'L$',
        'LSL': 'L', 'MAD': 'د.م.', 'MDL': 'L', 'MKD': 'ден', 'MNT': '₮',
        'NAD': 'N$', 'NGN': '₦', 'RSD': 'Дин.', 'SLL': 'Le', 'SZL': 'L',
        'TND': 'د.ت', 'UAH': '₴', 'XPF': '₣', 'YER': '﷼', 'ALL': 'L',
        'BAM': 'KM', 'MRU': 'UM', 'STN': 'Db'
    }

    return currencySymbols[finalCurrency] || finalCurrency
}
// Legacy helpers kept for backwards compatibility (no‑ops now)
export function initializeCurrencySettings() {
    return (safeGetPage()?.props?.settings || {}).currency || 'USD'
}

export function refreshCurrencySettings() {
    return initializeCurrencySettings()
}

export function getCurrentCurrency() {
    const page = safeGetPage()
    const hotelSettings = page?.props?.hotelSettings || {}
    const settings = page?.props?.settings || {}
    return hotelSettings.currency?.code || settings.currency || 'USD'
}

export function setCurrentCurrency() {
    // Intentionally no-op to avoid diverging from admin settings
}

export function setCurrencyPosition() {
    // Intentionally no-op to avoid diverging from admin settings
}

export function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}
