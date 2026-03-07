<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FolioCharge;
use App\Models\GuestFolio;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Services\FinancialService;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    protected $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function profitLoss(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Set date range based on period
        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $profitLossData = $this->financialService->getProfitLossData($startDate, $endDate);

        return Inertia::render('Accountant/Reports/ProfitLoss', [
            'user' => auth()->user()->load('roles'),
            'profitLossData' => $profitLossData,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $period = $request->get('period', 'current');
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));

        $balanceSheetData = $this->getBalanceSheetData($asOfDate);

        return Inertia::render('Accountant/Reports/BalanceSheet', [
            'user' => auth()->user()->load('roles'),
            'balanceSheetData' => $balanceSheetData,
            'period' => $period,
            'asOfDate' => $asOfDate,
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function cashFlow(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $cashFlowData = $this->getCashFlowData($startDate, $endDate);

        return Inertia::render('Accountant/Reports/CashFlow', [
            'user' => auth()->user()->load('roles'),
            'cashFlowData' => $cashFlowData,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function budgetAnalysis(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $budgetAnalysis = $this->financialService->getBudgetAnalysis($startDate, $endDate);

        return Inertia::render('Accountant/Reports/BudgetAnalysis', [
            'user' => auth()->user()->load('roles'),
            'budgetAnalysis' => $budgetAnalysis,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function departmentalAnalysis(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $departmentalAnalysis = $this->financialService->getDepartmentalAnalysis($startDate, $endDate);

        return Inertia::render('Accountant/Reports/DepartmentalAnalysis', [
            'user' => auth()->user()->load('roles'),
            'departmentalAnalysis' => $departmentalAnalysis,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function productProfitability(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $productProfitability = $this->financialService->getProductProfitability($startDate, $endDate);

        return Inertia::render('Accountant/Reports/ProductProfitability', [
            'user' => auth()->user()->load('roles'),
            'productProfitability' => $productProfitability,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function financialRatios(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $financialRatios = $this->financialService->getFinancialRatios($startDate, $endDate);

        return Inertia::render('Accountant/Reports/FinancialRatios', [
            'user' => auth()->user()->load('roles'),
            'financialRatios' => $financialRatios,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $revenueData = $this->financialService->getRevenueData($startDate, $endDate);

        return Inertia::render('Accountant/Reports/Revenue', [
            'user' => auth()->user()->load('roles'),
            'revenueData' => $revenueData,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency()
        ]);
    }

    public function exportReport(Request $request)
    {
        $reportType = $request->get('type');
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $format = $request->get('format', 'xlsx'); // Default to Excel

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $data = [];
        $filename = '';

        switch ($reportType) {
            case 'profit-loss':
                $data = $this->financialService->getProfitLossData($startDate, $endDate);
                $filename = 'profit-loss-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d');
                break;
            case 'balance-sheet':
                $data = $this->getBalanceSheetData($endDate);
                $filename = 'balance-sheet-' . $endDate->format('Y-m-d');
                break;
            case 'cash-flow':
                $data = $this->getCashFlowData($startDate, $endDate);
                $filename = 'cash-flow-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d');
                break;
            case 'revenue':
                $data = $this->financialService->getRevenueData($startDate, $endDate);
                $filename = 'revenue-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d');
                break;
        }

        // Generate the export data
        $export = new FinancialReportExport($data, $reportType);

        switch ($format) {
            case 'xlsx':
                return Excel::download($export, $filename . '.xlsx');
            case 'csv':
                return Excel::download($export, $filename . '.csv');
            case 'pdf':
                return $this->exportToPDF($data, $reportType, $filename);
            case 'print':
                return $this->printReport($data, $reportType, $filename);
            default:
                return Excel::download($export, $filename . '.xlsx');
        }
    }

    private function exportToPDF($data, $reportType, $filename)
    {
        $reportData = $this->prepareReportDataForPDF($data, $reportType);

        $pdf = Pdf::loadView('pdf.financial-report', [
            'reportData' => $reportData,
            'reportType' => $reportType,
            'filename' => $filename
        ]);

        return $pdf->download($filename . '.pdf');
    }

    private function printReport($data, $reportType, $filename)
    {
        $reportData = $this->prepareReportDataForPDF($data, $reportType);

        return view('pdf.financial-report', [
            'reportData' => $reportData,
            'reportType' => $reportType,
            'filename' => $filename,
            'print_mode' => true
        ]);
    }

    private function prepareReportDataForPDF($data, $reportType)
    {
        $reportData = [
            'title' => $this->getReportTitle($reportType),
            'currency' => $data['currency'] ?? ['symbol' => '$', 'code' => 'USD'],
            'hotel_name' => $this->financialService->getHotelName(),
            'sections' => []
        ];

        switch ($reportType) {
            case 'profit-loss':
                $reportData['sections'] = [
                    'revenue' => [
                        'title' => 'REVENUE',
                        'items' => $data['revenue'] ?? [],
                        'total' => $data['total_revenue'] ?? 0
                    ],
                    'cogs' => [
                        'title' => 'COST OF GOODS SOLD',
                        'items' => $data['cogs'] ?? [],
                        'total' => $data['total_cogs'] ?? 0
                    ],
                    'gross_profit' => [
                        'title' => 'GROSS PROFIT',
                        'amount' => $data['gross_profit'] ?? 0
                    ],
                    'operating_expenses' => [
                        'title' => 'OPERATING EXPENSES',
                        'items' => $data['operating_expenses'] ?? [],
                        'total' => $data['total_operating_expenses'] ?? 0
                    ],
                    'net_profit' => [
                        'title' => 'NET PROFIT',
                        'amount' => $data['net_profit'] ?? 0
                    ]
                ];
                break;

            case 'balance-sheet':
                $reportData['sections'] = [
                    'current_assets' => [
                        'title' => 'CURRENT ASSETS',
                        'items' => $data['current_assets'] ?? []
                    ],
                    'fixed_assets' => [
                        'title' => 'FIXED ASSETS',
                        'items' => $data['fixed_assets'] ?? []
                    ],
                    'current_liabilities' => [
                        'title' => 'CURRENT LIABILITIES',
                        'items' => $data['current_liabilities'] ?? []
                    ],
                    'long_term_liabilities' => [
                        'title' => 'LONG-TERM LIABILITIES',
                        'items' => $data['long_term_liabilities'] ?? []
                    ],
                    'equity' => [
                        'title' => 'EQUITY',
                        'items' => $data['equity'] ?? []
                    ]
                ];
                break;

            case 'cash-flow':
                $reportData['sections'] = [
                    'operating' => [
                        'title' => 'CASH FLOWS FROM OPERATING ACTIVITIES',
                        'net_income' => $data['net_income'] ?? 0,
                        'operating_cash_flow' => $data['operating_cash_flow'] ?? 0
                    ],
                    'investing' => [
                        'title' => 'CASH FLOWS FROM INVESTING ACTIVITIES',
                        'investing_cash_flow' => $data['investing_cash_flow'] ?? 0
                    ],
                    'financing' => [
                        'title' => 'CASH FLOWS FROM FINANCING ACTIVITIES',
                        'financing_cash_flow' => $data['financing_cash_flow'] ?? 0
                    ],
                    'summary' => [
                        'beginning_cash' => $data['beginning_cash'] ?? 0,
                        'net_cash_change' => $data['net_cash_change'] ?? 0,
                        'ending_cash' => $data['ending_cash'] ?? 0
                    ]
                ];
                break;

            case 'revenue':
                $reportData['sections'] = [
                    'revenue_by_category' => [
                        'title' => 'REVENUE BY CATEGORY',
                        'items' => $data['revenue_by_category'] ?? [],
                        'total_revenue' => $data['total_revenue'] ?? 0
                    ]
                ];
                break;
        }

        return $reportData;
    }

    private function getReportTitle($reportType)
    {
        $titles = [
            'profit-loss' => 'Profit & Loss Statement',
            'balance-sheet' => 'Balance Sheet',
            'cash-flow' => 'Cash Flow Statement',
            'revenue' => 'Revenue Report'
        ];

        return $titles[$reportType] ?? 'Financial Report';
    }

    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [Carbon::parse($startDate), Carbon::parse($endDate)];
        }

        switch ($period) {
            case 'daily':
                return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
            case 'weekly':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'monthly':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'quarterly':
                return [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()];
            case 'yearly':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    private function getBalanceSheetData($asOfDate)
    {
        // This would typically come from a proper chart of accounts
        // For now, we'll calculate based on available data

        $currency = $this->financialService->getCurrency();

        // Assets (simplified calculation)
        $cashFromPayments = \App\Models\Payment::where('status', 'completed')
            ->where('processed_at', '<=', $asOfDate)
            ->sum('local_amount');

        $accountsReceivable = \App\Models\GuestFolio::where('status', 'open')
            ->where('folio_date', '<=', $asOfDate)
            ->sum('balance_amount');

        // POS Inventory value
        $posInventoryValue = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.sale_date', '<=', $asOfDate)
            ->where('sales.payment_status', 'paid')
            ->sum(DB::raw('sale_items.unit_cost * sale_items.quantity'));

        // Liabilities (simplified)
        $accountsPayable = \App\Models\Expense::where('status', 'approved')
            ->where('expense_date', '<=', $asOfDate)
            ->whereNull('paid_at')
            ->sum('amount');

        // Calculate totals
        $currentAssets = $cashFromPayments + $accountsReceivable + $posInventoryValue + 65000 + 35000;
        $fixedAssets = 750000 - 125000 + 180000 + 95000 + 45000; // 945,000
        $totalAssets = $currentAssets + $fixedAssets;
        
        $currentLiabilities = $accountsPayable + 25000 + 35000 + 28000;
        $longTermLiabilities = 250000 + 30000; // 280,000
        $totalLiabilities = $currentLiabilities + $longTermLiabilities;
        
        $totalEquity = 500000 + 250000 + 50000; // 800,000

        // Sample balance sheet structure
        return [
            'current_assets' => [
                ['account' => 'Cash and Cash Equivalents', 'amount' => $cashFromPayments],
                ['account' => 'Accounts Receivable', 'amount' => $accountsReceivable],
                ['account' => 'POS Inventory', 'amount' => $posInventoryValue],
                ['account' => 'Room Supplies Inventory', 'amount' => 65000],
                ['account' => 'Prepaid Expenses', 'amount' => 35000],
            ],
            'fixed_assets' => [
                ['account' => 'Property, Plant & Equipment', 'amount' => 750000],
                ['account' => 'Less: Accumulated Depreciation', 'amount' => -125000],
                ['account' => 'Furniture & Fixtures', 'amount' => 180000],
                ['account' => 'Equipment', 'amount' => 95000],
                ['account' => 'POS Equipment', 'amount' => 45000],
            ],
            'current_liabilities' => [
                ['account' => 'Accounts Payable', 'amount' => $accountsPayable],
                ['account' => 'Accrued Expenses', 'amount' => 25000],
                ['account' => 'Short-term Debt', 'amount' => 35000],
                ['account' => 'Payroll Liabilities', 'amount' => 28000],
            ],
            'long_term_liabilities' => [
                ['account' => 'Long-term Debt', 'amount' => 250000],
                ['account' => 'Deferred Tax Liabilities', 'amount' => 30000],
            ],
            'equity' => [
                ['account' => 'Owner\'s Capital', 'amount' => 500000],
                ['account' => 'Retained Earnings', 'amount' => 250000],
                ['account' => 'Current Year Earnings', 'amount' => 50000],
            ],
            'totals' => [
                'total_current_assets' => $currentAssets,
                'total_fixed_assets' => $fixedAssets,
                'total_assets' => $totalAssets,
                'total_current_liabilities' => $currentLiabilities,
                'total_long_term_liabilities' => $longTermLiabilities,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            ],
            'currency' => $currency
        ];
    }

    private function getCashFlowData($startDate, $endDate)
    {
        $currency = $this->financialService->getCurrency();

        // Room Revenue Cash Flow (from folio charges)
        $roomChargeCodes = ['ROOM', 'ROOM_RATE', 'ACCOMMODATION', 'ROOM_CHARGE', 'STAY', 'NIGHT'];
        $roomCashFlow = \App\Models\FolioCharge::whereIn('charge_code', $roomChargeCodes)
            ->where(function($query) {
                $query->where('is_voided', false)
                      ->orWhereNull('is_voided');
            })
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');

        // POS Sales Cash Flow - try multiple payment statuses and conditions
        $posCashFlow = \App\Models\Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where(function($query) {
                $query->whereIn('payment_status', ['paid', 'completed', 'approved'])
                      ->orWhereNull('payment_status');
            })
            ->sum('total_amount');

        // Debug: Check if any sales exist
        $allSales = \App\Models\Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount');
        
        // Debug: Check payment statuses
        $paymentStatuses = \App\Models\Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->distinct()
            ->pluck('payment_status');
        
        // Debug: Check if sales have any voided status (Sale model doesn't have is_voided field)
        // $voidedSales = \App\Models\Sale::whereBetween('sale_date', [$startDate, $endDate])
        //     ->where('is_voided', true)
        //     ->sum('total_amount');
        $voidedSales = 0; // Sale model doesn't have voided status
        
        \Log::info('All sales amount: ' . $allSales);
        \Log::info('Payment statuses: ' . $paymentStatuses->implode(', '));
        \Log::info('Voided sales amount: ' . $voidedSales);
        \Log::info('POS cash flow (paid/completed): ' . $posCashFlow);

        // Other Revenue Cash Flow (services, fees, etc.)
        $otherRevenueCashFlow = \App\Models\FolioCharge::whereNotIn('charge_code', $roomChargeCodes)
            ->notVoided()
            ->whereBetween('charge_date', [$startDate, $endDate])
            ->sum('net_amount');

        // Total Operating Cash Inflow
        $totalOperatingCashInflow = $roomCashFlow + $posCashFlow + $otherRevenueCashFlow;

        // Operating Expenses Cash Outflow
        $operatingExpenses = \App\Models\Expense::whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Payroll Expenses (if tracked separately)
        $payrollExpenses = \App\Models\Expense::whereHas('category', function($query) {
                $query->where('name', 'LIKE', '%payroll%')
                       ->orWhere('name', 'LIKE', '%salary%')
                       ->orWhere('name', 'LIKE', '%wage%');
            })
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Cost of Goods Sold (COGS) for POS
        $cogs = $this->financialService->getFoodBeverageCOGS($startDate, $endDate);

        // Total Operating Cash Outflow
        $totalOperatingCashOutflow = $operatingExpenses + $payrollExpenses + $cogs;

        // Net Operating Cash Flow
        $netOperatingCashFlow = $totalOperatingCashInflow - $totalOperatingCashOutflow;

        // Investing Cash Flow (capital expenditures, equipment purchases)
        $investingCashFlow = \App\Models\Expense::whereHas('category', function($query) {
                $query->where('name', 'LIKE', '%equipment%')
                       ->orWhere('name', 'LIKE', '%furniture%')
                       ->orWhere('name', 'LIKE', '%capital%')
                       ->orWhere('name', 'LIKE', '%investment%');
            })
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Financing Cash Flow (loans, interest payments, dividends)
        $financingCashFlow = \App\Models\Expense::whereHas('category', function($query) {
                $query->where('name', 'LIKE', '%interest%')
                       ->orWhere('name', 'LIKE', '%loan%')
                       ->orWhere('name', 'LIKE', '%dividend%')
                       ->orWhere('name', 'LIKE', '%financing%');
            })
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Get beginning cash balance (simplified - use a default value)
        $beginningCash = 100000; // Default beginning cash balance

        // Net Cash Change
        $netCashChange = $netOperatingCashFlow + $investingCashFlow + $financingCashFlow;

        // Ending Cash Balance
        $endingCash = $beginningCash + $netCashChange;

        // Get Net Income from Profit & Loss
        $profitLossData = $this->financialService->getProfitLossData($startDate, $endDate);
        $netIncome = $profitLossData['net_profit'];

        // Cash Flow Reconciliation (Net Income to Cash Flow)
        $depreciationAmortization = \App\Models\Expense::whereHas('category', function($query) {
                $query->where('name', 'LIKE', '%depreciation%')
                       ->orWhere('name', 'LIKE', '%amortization%');
            })
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Working Capital Changes (simplified)
        $accountsReceivableChange = 0; // Would need AR tracking
        $inventoryChange = 0; // Would need inventory tracking
        $accountsPayableChange = 0; // Would need AP tracking

        $workingCapitalChange = $accountsReceivableChange + $inventoryChange - $accountsPayableChange;

        return [
            // Cash Flow from Operations
            'beginning_cash' => $beginningCash,
            'room_cash_flow' => $roomCashFlow,
            'pos_cash_flow' => $posCashFlow,
            'other_revenue_cash_flow' => $otherRevenueCashFlow,
            'total_operating_cash_inflow' => $totalOperatingCashInflow,
            'operating_expenses' => $operatingExpenses,
            'payroll_expenses' => $payrollExpenses,
            'cogs' => $cogs,
            'total_operating_cash_outflow' => $totalOperatingCashOutflow,
            'net_operating_cash_flow' => $netOperatingCashFlow,
            
            // Cash Flow from Investing & Financing
            'investing_cash_flow' => $investingCashFlow,
            'financing_cash_flow' => $financingCashFlow,
            
            // Summary
            'net_cash_change' => $netCashChange,
            'ending_cash' => $endingCash,
            'net_income' => $netIncome,
            
            // Reconciliation Items
            'depreciation_amortization' => $depreciationAmortization,
            'working_capital_change' => $workingCapitalChange,
            
            // Currency and formatting
            'currency' => $currency,
            'formatted_beginning_cash' => $this->formatCurrency($beginningCash, $currency),
            'formatted_room_cash_flow' => $this->formatCurrency($roomCashFlow, $currency),
            'formatted_pos_cash_flow' => $this->formatCurrency($posCashFlow, $currency),
            'formatted_other_revenue_cash_flow' => $this->formatCurrency($otherRevenueCashFlow, $currency),
            'formatted_total_operating_cash_inflow' => $this->formatCurrency($totalOperatingCashInflow, $currency),
            'formatted_operating_expenses' => $this->formatCurrency($operatingExpenses, $currency),
            'formatted_payroll_expenses' => $this->formatCurrency($payrollExpenses, $currency),
            'formatted_cogs' => $this->formatCurrency($cogs, $currency),
            'formatted_total_operating_cash_outflow' => $this->formatCurrency($totalOperatingCashOutflow, $currency),
            'formatted_net_operating_cash_flow' => $this->formatCurrency($netOperatingCashFlow, $currency),
            'formatted_investing_cash_flow' => $this->formatCurrency($investingCashFlow, $currency),
            'formatted_financing_cash_flow' => $this->formatCurrency($financingCashFlow, $currency),
            'formatted_net_cash_change' => $this->formatCurrency($netCashChange, $currency),
            'formatted_ending_cash' => $this->formatCurrency($endingCash, $currency),
            'formatted_net_income' => $this->formatCurrency($netIncome, $currency),
            'formatted_depreciation_amortization' => $this->formatCurrency($depreciationAmortization, $currency),
            'formatted_working_capital_change' => $this->formatCurrency($workingCapitalChange, $currency),
        ];
    }

    private function formatCurrency($amount, $currency)
    {
        $formatted = number_format($amount, 2, '.', ',');
        return $currency['position'] === 'before'
            ? $currency['symbol'] . $formatted
            : $formatted . ' ' . $currency['symbol'];
    }
}
