<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\FolioCharge;
use App\Models\Expense;
use App\Models\GuestFolio;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    public function getCurrency()
    {
        $currencyCode = Setting::get('currency', 'USD');
        $currencyPosition = Setting::get('currency_position', 'prefix');

        // Get currency symbol from the currency code
        $currencySymbols = $this->getCurrencySymbols();
        $symbol = $currencySymbols[$currencyCode] ?? $currencyCode;

        return [
            'code' => $currencyCode,
            'symbol' => $symbol,
            'position' => $currencyPosition === 'suffix' ? 'after' : 'before',
        ];
    }

    public function getHotelName()
    {
        return Setting::get('hotel_name', 'Hotel Management System');
    }

    private function getCurrencySymbols()
    {
        return [
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
    }

    public function formatCurrency($amount, $showSymbol = true)
    {
        $currency = $this->getCurrency();
        $formatted = number_format($amount, 2);

        if (!$showSymbol) {
            return $formatted;
        }

        return $currency['position'] === 'before'
            ? $currency['symbol'] . $formatted
            : $formatted . ' ' . $currency['symbol'];
    }

    public function getRevenueData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        // Room Revenue from folio charges - comprehensive room charge codes
        $roomChargeCodes = [
            'ROOM', 'ROOM_RATE', 'ACCOMMODATION', 'ROOM_CHARGE', 'STAY', 'NIGHT',
            'ROOM_NIGHT', 'ROOM_SERVICE_CHARGE', 'ACCOMMODATION_CHARGE', 
            'HOTEL_ROOM', 'GUEST_ROOM', 'LODGING', 'OCCUPANCY', 'DAILY_RATE',
            'ROOM_TAX', 'ROOM_FEE'
        ];
        $billAdjustmentChargeCodes = ['ADJUSTMENT'];
        $roomRevenue = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->where(function($query) {
                $query->where('is_voided', false)
                      ->orWhereNull('is_voided');
            })
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');

        $billAdjustmentRevenue = FolioCharge::whereIn('charge_code', $billAdjustmentChargeCodes)
            ->where(function($query) {
                $query->where('is_voided', false)
                      ->orWhereNull('is_voided');
            })
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');

        // Debug: Check all folio charges (no filters)
        $allFolioCharges = FolioCharge::whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');
        
        // Debug: Check folio charges with room codes (no voided filter)
        $roomChargesNoVoidFilter = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');
        
        // Debug: Check folio charges with room codes (with voided filter)
        $roomChargesWithVoidFilter = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->where(function($query) {
                $query->where('is_voided', false)
                      ->orWhereNull('is_voided');
            })
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');
        
        // Debug: Check if any room charges exist at all
        $anyRoomCharges = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->count();
        
        \Log::info('All folio charges amount: ' . $allFolioCharges);
        \Log::info('Room charges (no void filter): ' . $roomChargesNoVoidFilter);
        \Log::info('Room charges (with void filter): ' . $roomChargesWithVoidFilter);
        \Log::info('Room charges (using notVoided scope): ' . $roomRevenue);
        \Log::info('Any room charges exist: ' . $anyRoomCharges);

        // Debug: Check what charge codes exist
        $allChargeCodes = FolioCharge::select('charge_code')
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->distinct()
            ->pluck('charge_code');
        
        \Log::info('Available charge codes: ' . $allChargeCodes->implode(', '));

        // Debug: Check which room charge codes actually exist
        $existingRoomChargeCodes = FolioCharge::select('charge_code')
            ->whereIn('charge_code', $roomChargeCodes)
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->distinct()
            ->pluck('charge_code');
        
        \Log::info('Existing room charge codes: ' . $existingRoomChargeCodes->implode(', '));
        
        // Debug: Check if there are any room-related charges not in our list
        $potentialRoomCharges = $allChargeCodes->filter(function($code) {
            $upperCode = strtoupper($code);
            return strpos($upperCode, 'ROOM') !== false || 
                   strpos($upperCode, 'ACCOMMODATION') !== false || 
                   strpos($upperCode, 'STAY') !== false || 
                   strpos($upperCode, 'NIGHT') !== false ||
                   strpos($upperCode, 'RATE') !== false;
        });
        
        \Log::info('Potential room charges not in list: ' . $potentialRoomCharges->implode(', '));

        // Debug: Check if room charges exist
        $roomChargesCount = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->count();
        
        \Log::info('Room charges count: ' . $roomChargesCount);
        \Log::info('Room revenue amount: ' . $roomRevenue);

        // Get POS Sales Revenue - use flexible payment status filtering
        $posSalesRevenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where(function($query) {
                $query->where('payment_status', 'completed')
                      ->orWhere('payment_status', 'paid')
                      ->orWhere('payment_status', 'approved')
                      ->orWhereNull('payment_status');
            })
            ->sum('total_amount');

        // Debug: Check POS sales data
        $allPosSales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount');
        
        $posPaymentStatuses = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->distinct()
            ->pluck('payment_status');
        
        \Log::info('All POS sales amount: ' . $allPosSales);
        \Log::info('POS payment statuses: ' . $posPaymentStatuses->implode(', '));
        \Log::info('POS sales revenue (flexible): ' . $posSalesRevenue);

        // Total Revenue = Room Revenue + POS Sales + Other Services Revenue
        // Other Services Revenue should exclude POS-related charge codes and only include services like laundry, carwash, spa, etc.
        $posChargeCodes = ['POS_SALE', 'RETAIL', 'GIFT_SHOP', 'SOUVENIR'];
        $otherServicesRevenue = FolioCharge::whereNotIn('charge_code', $roomChargeCodes)
            ->whereNotIn('charge_code', $billAdjustmentChargeCodes)
            ->whereNotIn('charge_code', $posChargeCodes)
            ->where(function($query) {
                $query->where('is_voided', false)
                      ->orWhereNull('is_voided');
            })
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');

        $totalRevenue = $roomRevenue + $billAdjustmentRevenue + $posSalesRevenue + $otherServicesRevenue;

        // Debug: Check the breakdown
        \Log::info('Room Revenue: ' . $roomRevenue);
        \Log::info('Bill Adjustment Revenue: ' . $billAdjustmentRevenue);
        \Log::info('POS Sales Revenue: ' . $posSalesRevenue);
        \Log::info('Other Services Revenue: ' . $otherServicesRevenue);
        \Log::info('Total Revenue: ' . $totalRevenue);

        // If no revenue found, try a broader date range
        if ($totalRevenue == 0) {
            // Try last 30 days
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now();
            
            // Recalculate with broader date range
            $roomRevenue = FolioCharge::whereIn('charge_code', $roomChargeCodes)
                ->where(function($query) {
                    $query->where('is_voided', false)
                          ->orWhereNull('is_voided');
                })
                ->whereBetween('charge_date', [$startDate, $endDate])
                ->sum('net_amount');

            $billAdjustmentRevenue = FolioCharge::whereIn('charge_code', $billAdjustmentChargeCodes)
                ->where(function($query) {
                    $query->where('is_voided', false)
                          ->orWhereNull('is_voided');
                })
                ->whereBetween('charge_date', [$startDate, $endDate])
                ->sum('net_amount');

            $posSalesRevenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->where(function($query) {
                    $query->where('payment_status', 'completed')
                          ->orWhere('payment_status', 'paid')
                          ->orWhere('payment_status', 'approved')
                          ->orWhereNull('payment_status');
                })
                ->sum('total_amount');

            $otherServicesRevenue = FolioCharge::whereNotIn('charge_code', $roomChargeCodes)
                ->whereNotIn('charge_code', $billAdjustmentChargeCodes)
                ->whereNotIn('charge_code', $posChargeCodes)
                ->notVoided()
                ->byDateRange($startDate, $endDate)
                ->sum('net_amount');

            $totalRevenue = $roomRevenue + $billAdjustmentRevenue + $posSalesRevenue + $otherServicesRevenue;
        }

        // Revenue by category (from folio charges) - ensure all categories are shown
        $allChargeCodes = FolioCharge::select('charge_code')
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->distinct()
            ->pluck('charge_code');

        $revenueByCategory = $allChargeCodes->map(function ($chargeCode) use ($startDate, $endDate) {
            $total = FolioCharge::where('charge_code', $chargeCode)
                ->notVoided()
                ->byDateRange($startDate, $endDate)
                ->sum('net_amount');

            return [
                'category' => $this->getChargeCategoryName($chargeCode),
                'amount' => $total,
                'formatted_amount' => $this->formatCurrency($total),
                'charge_code' => $chargeCode
            ];
        })->groupBy('category')->map(function ($items) {
            $totalAmount = $items->sum('amount');
            return [
                'category' => $items->first()['category'],
                'amount' => $totalAmount,
                'formatted_amount' => $this->formatCurrency($totalAmount),
                'charge_codes' => $items->pluck('charge_code')->unique()
            ];
        })->values();

        // Add POS sales as a category if it exists
        if ($posSalesRevenue > 0) {
            $posCategory = [
                'category' => 'POS Sales',
                'amount' => $posSalesRevenue,
                'formatted_amount' => $this->formatCurrency($posSalesRevenue),
                'charge_codes' => collect(['POS_SALES'])
            ];
            
            // Check if POS Sales category already exists and merge, otherwise add
            $existingPosIndex = $revenueByCategory->search(function ($item) {
                return $item['category'] === 'POS Sales';
            });
            
            if ($existingPosIndex !== false) {
                $revenueByCategory[$existingPosIndex]['amount'] += $posSalesRevenue;
                $revenueByCategory[$existingPosIndex]['formatted_amount'] = $this->formatCurrency($revenueByCategory[$existingPosIndex]['amount']);
            } else {
                $revenueByCategory->push($posCategory);
            }
        }

        // Get POS Sales by category (through products)
        $posSalesByCategory = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', 'paid')
            ->groupBy('product_categories.name')
            ->select('product_categories.name as category', DB::raw('SUM(sale_items.total_price - sale_items.discount_amount) as total'))
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'amount' => $item->total,
                    'formatted_amount' => $this->formatCurrency($item->total)
                ];
            });

        // Average Daily Rate (ADR)
        $totalRoomNights = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->sum('quantity');

        $averageDailyRate = $totalRoomNights > 0 ? $roomRevenue / $totalRoomNights : 0;

        // Revenue growth (compared to previous period)
        $previousPeriodStart = Carbon::parse($startDate)->subMonth();
        $previousPeriodEnd = Carbon::parse($endDate)->subMonth();

        $previousRoomRevenue = FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->notVoided()
            ->byDateRange($previousPeriodStart, $previousPeriodEnd)
            ->sum('net_amount');

        $previousBillAdjustmentRevenue = FolioCharge::whereIn('charge_code', $billAdjustmentChargeCodes)
            ->notVoided()
            ->byDateRange($previousPeriodStart, $previousPeriodEnd)
            ->sum('net_amount');

        $previousPosRevenue = Sale::whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $previousOtherServicesRevenue = FolioCharge::whereNotIn('charge_code', $roomChargeCodes)
            ->whereNotIn('charge_code', $billAdjustmentChargeCodes)
            ->whereNotIn('charge_code', $posChargeCodes)
            ->notVoided()
            ->byDateRange($previousPeriodStart, $previousPeriodEnd)
            ->sum('net_amount');

        $previousTotalRevenue = $previousRoomRevenue + $previousBillAdjustmentRevenue + $previousPosRevenue + $previousOtherServicesRevenue;

        $growthRate = $previousTotalRevenue > 0
            ? (($totalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100
            : 0;

        return [
            'total_revenue' => $totalRevenue,
            'formatted_total_revenue' => $this->formatCurrency($totalRevenue),
            'room_revenue' => $roomRevenue,
            'formatted_room_revenue' => $this->formatCurrency($roomRevenue),
            'bill_adjustment_revenue' => $billAdjustmentRevenue,
            'formatted_bill_adjustment_revenue' => $this->formatCurrency($billAdjustmentRevenue),
            'pos_sales_revenue' => $posSalesRevenue,
            'formatted_pos_sales_revenue' => $this->formatCurrency($posSalesRevenue),
            'other_services_revenue' => $otherServicesRevenue,
            'formatted_other_services_revenue' => $this->formatCurrency($otherServicesRevenue),
            'room_revenue_percentage' => $totalRevenue > 0 ? ($roomRevenue / $totalRevenue) * 100 : 0,
            'bill_adjustment_revenue_percentage' => $totalRevenue > 0 ? ($billAdjustmentRevenue / $totalRevenue) * 100 : 0,
            'pos_sales_percentage' => $totalRevenue > 0 ? ($posSalesRevenue / $totalRevenue) * 100 : 0,
            'other_services_revenue_percentage' => $totalRevenue > 0 ? ($otherServicesRevenue / $totalRevenue) * 100 : 0,
            'average_daily_rate' => $averageDailyRate,
            'formatted_average_daily_rate' => $this->formatCurrency($averageDailyRate),
            'growth_rate' => round($growthRate, 1),
            'revenue_by_category' => $revenueByCategory,
            'pos_sales_by_category' => $posSalesByCategory,
            'currency' => $this->getCurrency()
        ];
    }

    public function getExpenseData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        // Total expenses
        $totalExpenses = Expense::byDateRange($startDate, $endDate)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        // Expenses by category
        $expensesByCategory = Expense::select('expense_categories.name', 'expense_categories.code', DB::raw('SUM(expenses.amount) as total'))
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->byDateRange($startDate, $endDate)
            ->whereIn('expenses.status', ['approved', 'paid'])
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.code')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->name,
                    'code' => $item->code,
                    'amount' => $item->total,
                    'formatted_amount' => $this->formatCurrency($item->total)
                ];
            });

        // Pending expenses
        $pendingExpenses = Expense::byStatus('pending')->count();

        return [
            'total_expenses' => $totalExpenses,
            'formatted_total_expenses' => $this->formatCurrency($totalExpenses),
            'expenses_by_category' => $expensesByCategory,
            'pending_expenses' => $pendingExpenses,
            'currency' => $this->getCurrency()
        ];
    }

    public function getProfitLossData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $revenueData = $this->getRevenueData($startDate, $endDate);
        $expenseData = $this->getExpenseData($startDate, $endDate);

        // Get POS Sales Revenue
        $posSalesRevenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Get POS Sales by category (through products)
        $posSalesByCategory = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', 'paid')
            ->groupBy('product_categories.name')
            ->select('product_categories.name as category', DB::raw('SUM(sale_items.total_price - sale_items.discount_amount) as total'))
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'amount' => $item->total,
                    'formatted_amount' => $this->formatCurrency($item->total)
                ];
            });

        // Revenue breakdown
        $revenue = [
            'room_revenue' => $revenueData['room_revenue'],
            'bill_adjustments' => $revenueData['bill_adjustment_revenue'] ?? 0,
            'pos_sales' => $posSalesRevenue,
            'food_beverage' => $this->getRevenueByChargeCode('FOOD', $startDate, $endDate) +
                             $this->getRevenueByChargeCode('BEVERAGE', $startDate, $endDate),
            'conference_services' => $this->getRevenueByChargeCode('CONFERENCE', $startDate, $endDate),
            'other_services' => $this->getRevenueByChargeCode('OTHER', $startDate, $endDate),
        ];

        $totalRevenue = array_sum($revenue);

        // Cost of Goods Sold (COGS) - Enhanced with POS cost tracking
        $posCOGS = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.payment_status', 'paid')
            ->sum(DB::raw('sale_items.unit_cost * sale_items.quantity'));

        $cogs = [
            'pos_costs' => $posCOGS,
            'food_beverage_costs' => $this->getFoodBeverageCOGS($startDate, $endDate),
            'room_supplies' => $this->getExpenseByCategory('ROOM_SUPPLIES', $startDate, $endDate),
            'laundry_cleaning' => $this->getExpenseByCategory('LAUNDRY', $startDate, $endDate),
            'utilities_direct' => $this->getExpenseByCategory('UTILITIES', $startDate, $endDate) * 0.6, // 60% direct
            'other_direct_costs' => $this->getExpenseByCategory('DIRECT_COSTS', $startDate, $endDate),
        ];

        $totalCOGS = array_sum($cogs);
        $grossProfit = $totalRevenue - $totalCOGS;

        // Operating Expenses
        $operatingExpenses = [
            'salaries_wages' => $this->getExpenseByCategory('PAYROLL', $startDate, $endDate),
            'marketing_advertising' => $this->getExpenseByCategory('MARKETING', $startDate, $endDate),
            'administrative' => $this->getExpenseByCategory('ADMIN', $startDate, $endDate),
            'maintenance_repairs' => $this->getExpenseByCategory('MAINTENANCE', $startDate, $endDate),
            'insurance' => $this->getExpenseByCategory('INSURANCE', $startDate, $endDate),
            'professional_services' => $this->getExpenseByCategory('PROFESSIONAL', $startDate, $endDate),
            'depreciation' => $this->getExpenseByCategory('DEPRECIATION', $startDate, $endDate),
        ];

        $totalOperatingExpenses = array_sum($operatingExpenses);
        $operatingIncome = $grossProfit - $totalOperatingExpenses;

        // Other Income/Expenses
        $otherIncomeExpenses = [
            'interest_income' => $this->getExpenseByCategory('INTEREST_INCOME', $startDate, $endDate),
            'interest_expense' => $this->getExpenseByCategory('INTEREST_EXPENSE', $startDate, $endDate),
        ];

        $totalOtherIncomeExpenses = array_sum($otherIncomeExpenses);
        $netProfit = $operatingIncome + $totalOtherIncomeExpenses;

        return [
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'pos_sales_by_category' => $posSalesByCategory,
            'cogs' => $cogs,
            'total_cogs' => $totalCOGS,
            'gross_profit' => $grossProfit,
            'gross_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
            'operating_expenses' => $operatingExpenses,
            'total_operating_expenses' => $totalOperatingExpenses,
            'operating_income' => $operatingIncome,
            'operating_margin' => $totalRevenue > 0 ? ($operatingIncome / $totalRevenue) * 100 : 0,
            'other_income_expenses' => $otherIncomeExpenses,
            'total_other_income_expenses' => $totalOtherIncomeExpenses,
            'net_profit' => $netProfit,
            'net_margin' => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
            'currency' => $this->getCurrency()
        ];
    }

    private function getRevenueByChargeCode($chargeCode, $startDate, $endDate)
    {
        return FolioCharge::where('charge_code', $chargeCode)
            ->notVoided()
            ->byDateRange($startDate, $endDate)
            ->sum('net_amount');
    }

    private function getExpenseByCategory($categoryCode, $startDate, $endDate)
    {
        return Expense::join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_categories.code', $categoryCode)
            ->byDateRange($startDate, $endDate)
            ->whereIn('expenses.status', ['approved', 'paid'])
            ->sum('expenses.amount');
    }

    private function getChargeCategoryName($chargeCode)
    {
        $categories = [
            // Room Revenue
            'ROOM' => 'Room Revenue',
            'ROOM_RATE' => 'Room Revenue',
            'ROOM_CHARGE' => 'Room Revenue',
            'ACCOMMODATION' => 'Room Revenue',
            'STAY' => 'Room Revenue',
            'NIGHT' => 'Room Revenue',
            'ROOM_NIGHT' => 'Room Revenue',
            'ROOM_SERVICE_CHARGE' => 'Room Revenue',
            'ACCOMMODATION_CHARGE' => 'Room Revenue',
            'HOTEL_ROOM' => 'Room Revenue',
            'GUEST_ROOM' => 'Room Revenue',
            'LODGING' => 'Room Revenue',
            'OCCUPANCY' => 'Room Revenue',
            'DAILY_RATE' => 'Room Revenue',
            'ROOM_TAX' => 'Room Revenue',
            'ROOM_FEE' => 'Room Revenue',
            
            // Food & Beverage
            'FOOD' => 'Food & Beverage',
            'BEVERAGE' => 'Food & Beverage',
            'RESTAURANT' => 'Food & Beverage',
            'BAR' => 'Food & Beverage',
            'MINIBAR' => 'Food & Beverage',
            'ROOM_SERVICE' => 'Food & Beverage',
            'BREAKFAST' => 'Food & Beverage',
            'LUNCH' => 'Food & Beverage',
            'DINNER' => 'Food & Beverage',
            'COFFEE_SHOP' => 'Food & Beverage',
            
            // Conference & Events
            'CONFERENCE' => 'Conference Services',
            'MEETING_ROOM' => 'Conference Services',
            'EVENT_HALL' => 'Conference Services',
            'BANQUET' => 'Conference Services',
            'CATERING' => 'Conference Services',
            'AUDIO_VISUAL' => 'Conference Services',
            'EQUIPMENT_RENTAL' => 'Conference Services',
            
            // Telecommunications & Entertainment
            'IPTV' => 'IPTV Services',
            'TV_RENTAL' => 'IPTV Services',
            'MOVIE' => 'IPTV Services',
            'INTERNET' => 'Internet Services',
            'WIFI' => 'Internet Services',
            'TELEPHONE' => 'Telephone Services',
            'LONG_DISTANCE' => 'Telephone Services',
            
            // Transportation
            'AIRPORT_TRANSFER' => 'Transportation',
            'TAXI' => 'Transportation',
            'SHUTTLE' => 'Transportation',
            'PARKING' => 'Parking Services',
            'VALET_PARKING' => 'Parking Services',
            
            // Laundry & Services
            'LAUNDRY' => 'Laundry Services',
            'DRY_CLEANING' => 'Laundry Services',
            'PRESSING' => 'Laundry Services',
            'SPA' => 'Spa & Wellness',
            'MASSAGE' => 'Spa & Wellness',
            'FITNESS' => 'Spa & Wellness',
            'GYM' => 'Spa & Wellness',
            'POOL' => 'Recreation Services',
            
            // Business Services
            'BUSINESS_CENTER' => 'Business Services',
            'PRINTING' => 'Business Services',
            'FAX' => 'Business Services',
            'PHOTOCOPY' => 'Business Services',
            'SECRETARIAL' => 'Business Services',
            
            // Miscellaneous Services
            'LATE_CHECKOUT' => 'Other Services',
            'EARLY_CHECKIN' => 'Other Services',
            'PET_FEE' => 'Other Services',
            'PET_CARE' => 'Other Services',
            'BABY_SITTING' => 'Other Services',
            'CONCIERGE' => 'Other Services',
            'BELLBOY' => 'Other Services',
            'PORTER' => 'Other Services',
            'LUGGAGE' => 'Other Services',
            'SAFE_DEPOSIT' => 'Other Services',
            'WAKE_UP_CALL' => 'Other Services',

            // Bill Adjustments
            'ADJUSTMENT' => 'Bill Adjustments',
            
            // Fees & Charges
            'SERVICE_CHARGE' => 'Service Charges',
            'RESORT_FEE' => 'Service Charges',
            'CITY_TAX' => 'Taxes & Fees',
            'TOURISM_TAX' => 'Taxes & Fees',
            'VAT' => 'Taxes & Fees',
            'LATE_FEE' => 'Penalty Fees',
            'NO_SHOW_FEE' => 'Penalty Fees',
            'CANCELLATION_FEE' => 'Penalty Fees',
            'DAMAGE_FEE' => 'Penalty Fees',
            
            // POS Sales (handled separately but included for completeness)
            'POS_SALE' => 'POS Sales',
            'RETAIL' => 'Retail Sales',
            'GIFT_SHOP' => 'Retail Sales',
            'SOUVENIR' => 'Retail Sales',
            
            // Generic categories
            'OTHER' => 'Other Services',
            'MISCELLANEOUS' => 'Other Services',
            'MISC' => 'Other Services',
            'SERVICE' => 'Other Services',
            'CHARGE' => 'Other Services',
            'FEE' => 'Other Services',
        ];

        return $categories[$chargeCode] ?? 'Other Services';
    }

    /**
     * Calculate Food & Beverage COGS based on product cost prices
     */
    public function getFoodBeverageCOGS($startDate, $endDate)
    {
        // Get POS sales for food and beverage products
        // Note: Sale model uses 'payment_status' field, not 'status'
        // Valid payment statuses are likely: pending, completed, cancelled, etc.
        $posSales = \App\Models\Sale::where('payment_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with(['items.product'])
            ->get();

        $totalCOGS = 0;

        foreach ($posSales as $sale) {
            foreach ($sale->items as $item) {
                if ($item->product) {
                    // Calculate COGS based on product cost price
                    // Use unit_cost from sale item if available, otherwise use product cost_price
                    $costPrice = $item->unit_cost ?? $item->product->cost_price;
                    $itemCOGS = $costPrice * $item->quantity;
                    $totalCOGS += $itemCOGS;
                }
            }
        }

        return $totalCOGS;
    }

    /**
     * Get advanced budget vs actual analysis
     */
    public function getBudgetAnalysis($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        // Get active budgets in the period
        $budgets = \App\Models\Budget::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        })->with(['category', 'department'])
          ->get();

        $analysis = [];

        foreach ($budgets as $budget) {
            $actualSpend = Expense::where('expense_category_id', $budget->category_id)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $variance = $actualSpend - $budget->amount;
            $variancePercentage = $budget->amount > 0 ? ($variance / $budget->amount) * 100 : 0;

            $analysis[] = [
                'budget' => $budget,
                'budgeted_amount' => $budget->amount,
                'actual_amount' => $actualSpend,
                'variance' => $variance,
                'variance_percentage' => round($variancePercentage, 2),
                'status' => $this->getBudgetStatus($budget->amount, $actualSpend),
                'health' => $budget->getBudgetHealth(),
                'utilization' => $budget->utilization_percentage
            ];
        }

        return [
            'budgets' => $analysis,
            'summary' => [
                'total_budgeted' => $budgets->sum('amount'),
                'total_actual' => Expense::whereIn('expense_category_id', $budgets->pluck('category_id'))
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->whereIn('status', ['approved', 'paid'])
                    ->sum('amount'),
                'total_variance' => 0, // Will be calculated
                'over_budget_count' => collect($analysis)->where('variance', '>', 0)->count(),
                'under_budget_count' => collect($analysis)->where('variance', '<', 0)->count(),
                'on_target_count' => collect($analysis)->where('variance', 0)->count(),
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get departmental financial analysis
     */
    public function getDepartmentalAnalysis($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $departments = \App\Models\Department::active()->get();

        $analysis = [];

        foreach ($departments as $department) {
            // Revenue by department (simplified - would need more complex logic in real implementation)
            $revenue = 0;
            // Expenses are not directly linked to departments, so we'll use 0 for now
            // In a real implementation, you might want to link expenses to departments through categories
            $expenses = 0;

            // Budgets for this department
            $budgets = \App\Models\Budget::where('department_id', $department->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })
                ->get();

            $budgetedAmount = $budgets->sum('amount');
            $actualSpend = Expense::whereIn('expense_category_id', $budgets->pluck('category_id'))
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $profit = $revenue - $actualSpend;
            $budgetVariance = $actualSpend - $budgetedAmount;

            $analysis[] = [
                'department' => $department,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $profit,
                'budgeted_amount' => $budgetedAmount,
                'actual_spend' => $actualSpend,
                'budget_variance' => $budgetVariance,
                'budget_variance_percentage' => $budgetedAmount > 0 ? ($budgetVariance / $budgetedAmount) * 100 : 0,
                'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0
            ];
        }

        return [
            'departments' => $analysis,
            'summary' => [
                'total_revenue' => collect($analysis)->sum('revenue'),
                'total_expenses' => collect($analysis)->sum('expenses'),
                'total_profit' => collect($analysis)->sum('profit'),
                'total_budgeted' => collect($analysis)->sum('budgeted_amount'),
                'total_actual_spend' => collect($analysis)->sum('actual_spend'),
                'total_budget_variance' => collect($analysis)->sum('budget_variance'),
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get product profitability analysis
     */
    public function getProductProfitability($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        // Get POS sales with product details
        $salesData = \App\Models\SaleItem::select(
            'products.id',
            'products.name',
            'products.code',
            'products.price',
            'products.cost_price',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue')
        )
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->where('sales.payment_status', 'completed')
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->groupBy('products.id', 'products.name', 'products.code', 'products.price', 'products.cost_price')
        ->get();

        $products = [];

        foreach ($salesData as $item) {
            $totalCost = $item->cost_price * $item->total_quantity;
            $grossProfit = $item->total_revenue - $totalCost;
            $profitMargin = $item->total_revenue > 0 ? ($grossProfit / $item->total_revenue) * 100 : 0;

            $products[] = [
                'product' => $item,
                'total_cost' => $totalCost,
                'gross_profit' => $grossProfit,
                'profit_margin' => round($profitMargin, 2),
                'roi' => $totalCost > 0 ? ($grossProfit / $totalCost) * 100 : 0
            ];
        }

        // Sort by profit margin descending
        $products = collect($products)->sortByDesc('profit_margin')->values();

        return [
            'products' => $products,
            'summary' => [
                'total_revenue' => $products->sum('product.total_revenue'),
                'total_cost' => $products->sum('total_cost'),
                'total_profit' => $products->sum('gross_profit'),
                'average_profit_margin' => $products->count() > 0 ? $products->avg('profit_margin') : 0,
                'highest_margin_product' => $products->first(),
                'lowest_margin_product' => $products->last(),
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get financial ratios and KPIs
     */
    public function getFinancialRatios($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $profitLoss = $this->getProfitLossData($startDate, $endDate);

        // Calculate key financial ratios
        $totalAssets = $this->getTotalAssets($endDate);
        $totalLiabilities = $this->getTotalLiabilities($endDate);
        $equity = $totalAssets - $totalLiabilities;

        $ratios = [
            // Profitability Ratios
            'gross_profit_margin' => $profitLoss['gross_margin'],
            'operating_profit_margin' => $profitLoss['operating_margin'],
            'net_profit_margin' => $profitLoss['net_margin'],
            'return_on_assets' => $totalAssets > 0 ? ($profitLoss['net_profit'] / $totalAssets) * 100 : 0,
            'return_on_equity' => $equity > 0 ? ($profitLoss['net_profit'] / $equity) * 100 : 0,

            // Liquidity Ratios
            'current_ratio' => $this->getCurrentRatio($endDate),
            'quick_ratio' => $this->getQuickRatio($endDate),

            // Efficiency Ratios
            'asset_turnover' => $totalAssets > 0 ? ($profitLoss['total_revenue'] / $totalAssets) : 0,
            'inventory_turnover' => $this->getInventoryTurnover($startDate, $endDate),

            // Leverage Ratios
            'debt_to_equity' => $equity > 0 ? ($totalLiabilities / $equity) : 0,
            'debt_to_assets' => $totalAssets > 0 ? ($totalLiabilities / $totalAssets) * 100 : 0,
        ];

        return [
            'ratios' => $ratios,
            'benchmark_targets' => [
                'gross_profit_margin' => 60, // 60%
                'net_profit_margin' => 15,   // 15%
                'current_ratio' => 2.0,      // 2:1
                'return_on_assets' => 10,    // 10%
                'return_on_equity' => 15,    // 15%
            ],
            'currency' => $this->getCurrency()
        ];
    }

    private function getBudgetStatus($budgeted, $actual)
    {
        $variance = $actual - $budgeted;
        if ($variance > 0) {
            return 'over_budget';
        } elseif ($variance < 0) {
            return 'under_budget';
        }
        return 'on_target';
    }

    private function getTotalAssets($asOfDate)
    {
        // Simplified calculation - in a real system, this would come from a proper chart of accounts
        return 1250000; // Example: Cash + Accounts Receivable + Inventory + Fixed Assets
    }

    private function getTotalLiabilities($asOfDate)
    {
        // Simplified calculation
        return 400000; // Example: Accounts Payable + Short-term Debt + Long-term Debt
    }

    private function getCurrentRatio($asOfDate)
    {
        $currentAssets = 300000; // Cash + Receivables + Inventory
        $currentLiabilities = 150000; // Accounts Payable + Short-term Debt
        return $currentLiabilities > 0 ? ($currentAssets / $currentLiabilities) : 0;
    }

    private function getQuickRatio($asOfDate)
    {
        $quickAssets = 200000; // Cash + Receivables (excluding inventory)
        $currentLiabilities = 150000;
        return $currentLiabilities > 0 ? ($quickAssets / $currentLiabilities) : 0;
    }

    private function getInventoryTurnover($startDate, $endDate)
    {
        $cogs = $this->getFoodBeverageCOGS($startDate, $endDate);
        $averageInventory = 65000; // Simplified average inventory value
        return $averageInventory > 0 ? ($cogs / $averageInventory) : 0;
    }

    /**
     * Get daily sales report with comprehensive filtering
     */
    public function getDailySalesReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfDay();
        $endDate = $endDate ?: Carbon::now()->endOfDay();

        $query = \App\Models\Sale::where('payment_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->whereHas('items.product', function ($q) use ($filters) {
                $q->where('product_category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['pos_terminal'])) {
            $query->where('pos_terminal_id', $filters['pos_terminal']);
        }

        $sales = $query->with(['items.product.category', 'user'])
            ->orderBy('sale_date', 'desc')
            ->get();

        $dailySales = [];
        $totalRevenue = 0;
        $totalItems = 0;
        $categoryBreakdown = [];
        $productBreakdown = [];
        $employeeBreakdown = [];

        foreach ($sales as $sale) {
            $saleDate = $sale->sale_date->format('Y-m-d');

            if (!isset($dailySales[$saleDate])) {
                $dailySales[$saleDate] = [
                    'date' => $saleDate,
                    'sales_count' => 0,
                    'total_revenue' => 0,
                    'total_items' => 0,
                    'sales' => []
                ];
            }

            $dailySales[$saleDate]['sales_count']++;
            $dailySales[$saleDate]['total_revenue'] += $sale->total_amount;
            $dailySales[$saleDate]['total_items'] += $sale->items->sum('quantity');
            $dailySales[$saleDate]['sales'][] = $sale;

            $totalRevenue += $sale->total_amount;
            $totalItems += $sale->items->sum('quantity');

            // Category breakdown
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->category) {
                    $categoryName = $item->product->category->name;
                    if (!isset($categoryBreakdown[$categoryName])) {
                        $categoryBreakdown[$categoryName] = [
                            'name' => $categoryName,
                            'revenue' => 0,
                            'items_sold' => 0,
                            'sales_count' => 0
                        ];
                    }
                    $categoryBreakdown[$categoryName]['revenue'] += $item->subtotal;
                    $categoryBreakdown[$categoryName]['items_sold'] += $item->quantity;
                    $categoryBreakdown[$categoryName]['sales_count']++;
                }
            }

            // Product breakdown
            foreach ($sale->items as $item) {
                if ($item->product) {
                    $productName = $item->product->name;
                    if (!isset($productBreakdown[$productName])) {
                        $productBreakdown[$productName] = [
                            'name' => $productName,
                            'product_id' => $item->product->id,
                            'revenue' => 0,
                            'items_sold' => 0,
                            'sales_count' => 0,
                            'avg_price' => 0
                        ];
                    }
                    $productBreakdown[$productName]['revenue'] += $item->subtotal;
                    $productBreakdown[$productName]['items_sold'] += $item->quantity;
                    $productBreakdown[$productName]['sales_count']++;
                }
            }

            // Employee breakdown
            if ($sale->user) {
                $employeeName = $sale->user->name;
                if (!isset($employeeBreakdown[$employeeName])) {
                    $employeeBreakdown[$employeeName] = [
                        'name' => $employeeName,
                        'user_id' => $sale->user->id,
                        'revenue' => 0,
                        'sales_count' => 0,
                        'avg_sale_amount' => 0
                    ];
                }
                $employeeBreakdown[$employeeName]['revenue'] += $sale->total_amount;
                $employeeBreakdown[$employeeName]['sales_count']++;
            }
        }

        // Calculate averages
        foreach ($productBreakdown as &$product) {
            $product['avg_price'] = $product['items_sold'] > 0 ? ($product['revenue'] / $product['items_sold']) : 0;
        }

        foreach ($employeeBreakdown as &$employee) {
            $employee['avg_sale_amount'] = $employee['sales_count'] > 0 ? ($employee['revenue'] / $employee['sales_count']) : 0;
        }

        return [
            'daily_sales' => array_values($dailySales),
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_items' => $totalItems,
                'total_sales' => $sales->count(),
                'avg_sale_amount' => $sales->count() > 0 ? ($totalRevenue / $sales->count()) : 0,
                'avg_items_per_sale' => $sales->count() > 0 ? ($totalItems / $sales->count()) : 0
            ],
            'category_breakdown' => array_values($categoryBreakdown),
            'product_breakdown' => array_values($productBreakdown),
            'employee_breakdown' => array_values($employeeBreakdown),
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get category-wise sales report
     */
    public function getCategorySalesReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = \App\Models\SaleItem::select(
            'product_categories.name as category_name',
            'product_categories.id as category_id',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
            DB::raw('AVG(sale_items.unit_price) as avg_price')
        )
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->where('sales.payment_status', 'completed')
        ->whereBetween('sales.sale_date', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('product_categories.id', $filters['category_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('sales.user_id', $filters['user_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('sales.payment_method', $filters['payment_method']);
        }

        $categories = $query->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $categories->sum('total_revenue');
        $categoryData = [];

        foreach ($categories as $category) {
            $categoryData[] = [
                'category' => $category,
                'percentage' => $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0,
                'formatted_revenue' => $this->formatCurrency($category->total_revenue),
                'formatted_avg_price' => $this->formatCurrency($category->avg_price)
            ];
        }

        return [
            'categories' => $categoryData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_items' => $categories->sum('total_quantity'),
                'total_sales' => $categories->sum('sales_count'),
                'avg_category_revenue' => $categories->count() > 0 ? ($totalRevenue / $categories->count()) : 0
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get employee sales performance report
     */
    public function getEmployeeSalesReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = \App\Models\Sale::select(
            'users.name as employee_name',
            'users.id as user_id',
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as avg_sale_amount'),
            DB::raw('MIN(created_at) as first_sale'),
            DB::raw('MAX(created_at) as last_sale')
        )
        ->join('users', 'sales.user_id', '=', 'users.id')
        ->where('sales.payment_status', 'completed')
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->groupBy('users.id', 'users.name');

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('users.id', $filters['user_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('sales.payment_method', $filters['payment_method']);
        }

        $employees = $query->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $employees->sum('total_revenue');
        $employeeData = [];

        foreach ($employees as $employee) {
            $employeeData[] = [
                'employee' => $employee,
                'revenue_percentage' => $totalRevenue > 0 ? ($employee->total_revenue / $totalRevenue) * 100 : 0,
                'formatted_revenue' => $this->formatCurrency($employee->total_revenue),
                'formatted_avg_sale' => $this->formatCurrency($employee->avg_sale_amount)
            ];
        }

        return [
            'employees' => $employeeData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $employees->sum('sales_count'),
                'avg_employee_revenue' => $employees->count() > 0 ? ($totalRevenue / $employees->count()) : 0,
                'top_performer' => $employees->first()
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get product performance report
     */
    public function getProductPerformanceReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = \App\Models\SaleItem::select(
            'products.name as product_name',
            'products.code as product_code',
            'products.id as product_id',
            'product_categories.name as category_name',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
            DB::raw('AVG(sale_items.unit_price) as avg_price'),
            DB::raw('MIN(sale_items.unit_price) as min_price'),
            DB::raw('MAX(sale_items.unit_price) as max_price')
        )
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->where('sales.payment_status', 'completed')
        ->whereBetween('sales.sale_date', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('product_categories.id', $filters['category_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('sales.user_id', $filters['user_id']);
        }

        $products = $query->groupBy(
            'products.id', 'products.name', 'products.code', 'product_categories.name'
        )
        ->orderBy('total_revenue', 'desc')
        ->get();

        $totalRevenue = $products->sum('total_revenue');
        $productData = [];

        foreach ($products as $product) {
            $productData[] = [
                'product' => $product,
                'revenue_percentage' => $totalRevenue > 0 ? ($product->total_revenue / $totalRevenue) * 100 : 0,
                'items_per_sale' => $product->sales_count > 0 ? ($product->total_quantity / $product->sales_count) : 0,
                'formatted_revenue' => $this->formatCurrency($product->total_revenue),
                'formatted_avg_price' => $this->formatCurrency($product->avg_price),
                'formatted_min_price' => $this->formatCurrency($product->min_price),
                'formatted_max_price' => $this->formatCurrency($product->max_price)
            ];
        }

        return [
            'products' => $productData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_items' => $products->sum('total_quantity'),
                'total_sales' => $products->sum('sales_count'),
                'avg_product_revenue' => $products->count() > 0 ? ($totalRevenue / $products->count()) : 0,
                'top_seller' => $products->first(),
                'worst_seller' => $products->last()
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get POS terminal performance report
     */
    public function getPosTerminalReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = \App\Models\Sale::select(
            'pos_terminals.name as terminal_name',
            'pos_terminals.id as terminal_id',
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as avg_sale_amount'),
            DB::raw('COUNT(DISTINCT DATE(sale_date)) as active_days')
        )
        ->join('pos_terminals', 'sales.pos_terminal_id', '=', 'pos_terminals.id')
        ->where('sales.payment_status', 'completed')
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->groupBy('pos_terminals.id', 'pos_terminals.name');

        // Apply filters
        if (!empty($filters['pos_terminal'])) {
            $query->where('pos_terminals.id', $filters['pos_terminal']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('sales.user_id', $filters['user_id']);
        }

        $terminals = $query->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $terminals->sum('total_revenue');
        $terminalData = [];

        foreach ($terminals as $terminal) {
            $terminalData[] = [
                'terminal' => $terminal,
                'revenue_percentage' => $totalRevenue > 0 ? ($terminal->total_revenue / $totalRevenue) * 100 : 0,
                'revenue_per_day' => $terminal->active_days > 0 ? ($terminal->total_revenue / $terminal->active_days) : 0,
                'sales_per_day' => $terminal->active_days > 0 ? ($terminal->sales_count / $terminal->active_days) : 0,
                'formatted_revenue' => $this->formatCurrency($terminal->total_revenue),
                'formatted_avg_sale' => $this->formatCurrency($terminal->avg_sale_amount)
            ];
        }

        return [
            'terminals' => $terminalData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $terminals->sum('sales_count'),
                'active_terminals' => $terminals->count(),
                'avg_terminal_revenue' => $terminals->count() > 0 ? ($totalRevenue / $terminals->count()) : 0
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get payment method analysis report
     */
    public function getPaymentMethodReport($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = \App\Models\Sale::select(
            'payment_method',
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as avg_sale_amount')
        )
        ->where('payment_status', 'completed')
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->groupBy('payment_method');

        // Apply filters
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $paymentMethods = $query->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $paymentMethods->sum('total_revenue');
        $paymentData = [];

        foreach ($paymentMethods as $method) {
            $paymentData[] = [
                'method' => $method,
                'revenue_percentage' => $totalRevenue > 0 ? ($method->total_revenue / $totalRevenue) * 100 : 0,
                'sales_percentage' => $paymentMethods->sum('sales_count') > 0 ? ($method->sales_count / $paymentMethods->sum('sales_count')) * 100 : 0,
                'formatted_revenue' => $this->formatCurrency($method->total_revenue),
                'formatted_avg_sale' => $this->formatCurrency($method->avg_sale_amount)
            ];
        }

        return [
            'payment_methods' => $paymentData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $paymentMethods->sum('sales_count'),
                'avg_sale_amount' => $paymentMethods->sum('sales_count') > 0 ? ($totalRevenue / $paymentMethods->sum('sales_count')) : 0
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get time-based sales analysis (hourly, daily, weekly)
     */
    public function getTimeBasedReport($startDate = null, $endDate = null, $timePeriod = 'hour', $filters = [])
    {
        $startDate = $startDate ?: Carbon::now()->startOfDay();
        $endDate = $endDate ?: Carbon::now()->endOfDay();

        $query = \App\Models\Sale::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as time_period"),
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as avg_sale_amount')
        )
        ->where('payment_status', 'completed')
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')"))
        ->orderBy('time_period');

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $salesData = $query->get();

        $totalRevenue = $salesData->sum('total_revenue');
        $timeData = [];

        foreach ($salesData as $sale) {
            $timeData[] = [
                'time_period' => $sale->time_period,
                'sales_count' => $sale->sales_count,
                'total_revenue' => $sale->total_revenue,
                'avg_sale_amount' => $sale->avg_sale_amount,
                'formatted_revenue' => $this->formatCurrency($sale->total_revenue),
                'formatted_avg_sale' => $this->formatCurrency($sale->avg_sale_amount)
            ];
        }

        return [
            'time_data' => $timeData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $salesData->sum('sales_count'),
                'peak_hour' => $salesData->max('total_revenue'),
                'avg_hourly_revenue' => $salesData->count() > 0 ? ($totalRevenue / $salesData->count()) : 0
            ],
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get comprehensive POS dashboard data
     */
    public function getPosDashboardData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfDay();
        $endDate = $endDate ?: Carbon::now()->endOfDay();

        // Basic sales metrics
        $sales = \App\Models\Sale::where('payment_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalSales = $sales->count();
        $totalItems = $sales->sum(function($sale) {
            return $sale->items->sum('quantity');
        });

        // Category performance
        $categoryPerformance = $this->getCategorySalesReport($startDate, $endDate);

        // Employee performance
        $employeePerformance = $this->getEmployeeSalesReport($startDate, $endDate);

        // Product performance
        $productPerformance = $this->getProductPerformanceReport($startDate, $endDate);

        // Payment method analysis
        $paymentAnalysis = $this->getPaymentMethodReport($startDate, $endDate);

        // Time-based analysis
        $timeAnalysis = $this->getTimeBasedReport($startDate, $endDate);

        return [
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $totalSales,
                'total_items' => $totalItems,
                'avg_sale_amount' => $totalSales > 0 ? ($totalRevenue / $totalSales) : 0,
                'avg_items_per_sale' => $totalSales > 0 ? ($totalItems / $totalSales) : 0,
                'formatted_total_revenue' => $this->formatCurrency($totalRevenue),
                'formatted_avg_sale' => $this->formatCurrency($totalSales > 0 ? ($totalRevenue / $totalSales) : 0)
            ],
            'category_performance' => $categoryPerformance,
            'employee_performance' => $employeePerformance,
            'product_performance' => $productPerformance,
            'payment_analysis' => $paymentAnalysis,
            'time_analysis' => $timeAnalysis,
            'currency' => $this->getCurrency()
        ];
    }

    /**
     * Get financial dashboard data for the financial reports page
     */
    public function getFinancialDashboardData($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        // Get revenue data
        $revenueData = $this->getRevenueData($startDate, $endDate);

        // Get expense data
        $expenseData = $this->getExpenseData($startDate, $endDate);

        // Get profit and loss data
        $profitLossData = $this->getProfitLossData($startDate, $endDate);

        // Get budget analysis
        $budgetAnalysis = $this->getBudgetAnalysis($startDate, $endDate);

        // Get departmental analysis
        $departmentalAnalysis = $this->getDepartmentalAnalysis($startDate, $endDate);

        // Get financial ratios
        $financialRatios = $this->getFinancialRatios($startDate, $endDate);

        // Calculate key metrics
        $netProfit = $profitLossData['net_profit'];
        $totalRevenue = $profitLossData['total_revenue'];
        $totalExpenses = $expenseData['total_expenses'];
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
                'formatted_total_revenue' => $this->formatCurrency($totalRevenue),
                'formatted_total_expenses' => $this->formatCurrency($totalExpenses),
                'formatted_net_profit' => $this->formatCurrency($netProfit),
                'formatted_profit_margin' => round($profitMargin, 2) . '%'
            ],
            'revenue_data' => $revenueData,
            'expense_data' => $expenseData,
            'profit_loss_data' => $profitLossData,
            'budget_analysis' => $budgetAnalysis,
            'departmental_analysis' => $departmentalAnalysis,
            'financial_ratios' => $financialRatios,
            'currency' => $this->getCurrency()
        ];
    }
}
