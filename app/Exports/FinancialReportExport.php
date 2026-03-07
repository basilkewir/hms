<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class FinancialReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $reportType;

    public function __construct($data, $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function array(): array
    {
        switch ($this->reportType) {
            case 'profit-loss':
                return $this->formatProfitLossData();
            case 'balance-sheet':
                return $this->formatBalanceSheetData();
            case 'cash-flow':
                return $this->formatCashFlowData();
            case 'revenue':
                return $this->formatRevenueData();
            default:
                return [];
        }
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'profit-loss':
                return ['Account', 'Amount'];
            case 'balance-sheet':
                return ['Account', 'Amount'];
            case 'cash-flow':
                return ['Activity', 'Amount'];
            case 'revenue':
                return ['Category', 'Amount', 'Percentage'];
            default:
                return [];
        }
    }

    public function title(): string
    {
        $titles = [
            'profit-loss' => 'Profit & Loss Statement',
            'balance-sheet' => 'Balance Sheet',
            'cash-flow' => 'Cash Flow Statement',
            'revenue' => 'Revenue Report'
        ];

        return $titles[$this->reportType] ?? 'Financial Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            'A:A' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ],
            'B:C' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]
        ];
    }

    private function formatProfitLossData(): array
    {
        $currency = $this->data['currency']['symbol'] ?? '$';
        $rows = [];

        // Revenue Section
        $rows[] = ['REVENUE', ''];
        foreach ($this->data['revenue'] as $key => $amount) {
            $rows[] = [ucwords(str_replace('_', ' ', $key)), $currency . number_format($amount, 2)];
        }
        $rows[] = ['TOTAL REVENUE', $currency . number_format($this->data['total_revenue'], 2)];
        $rows[] = ['', ''];

        // COGS Section
        $rows[] = ['COST OF GOODS SOLD', ''];
        foreach ($this->data['cogs'] as $key => $amount) {
            $rows[] = [ucwords(str_replace('_', ' ', $key)), $currency . number_format($amount, 2)];
        }
        $rows[] = ['TOTAL COGS', $currency . number_format($this->data['total_cogs'], 2)];
        $rows[] = ['', ''];

        // Gross Profit
        $rows[] = ['GROSS PROFIT', $currency . number_format($this->data['gross_profit'], 2)];
        $rows[] = ['', ''];

        // Operating Expenses
        $rows[] = ['OPERATING EXPENSES', ''];
        foreach ($this->data['operating_expenses'] as $key => $amount) {
            $rows[] = [ucwords(str_replace('_', ' ', $key)), $currency . number_format($amount, 2)];
        }
        $rows[] = ['TOTAL OPERATING EXPENSES', $currency . number_format($this->data['total_operating_expenses'], 2)];
        $rows[] = ['', ''];

        // Net Profit
        $rows[] = ['NET PROFIT', $currency . number_format($this->data['net_profit'], 2)];

        return $rows;
    }

    private function formatBalanceSheetData(): array
    {
        $currency = $this->data['currency']['symbol'] ?? '$';
        $rows = [];

        // Assets
        $rows[] = ['ASSETS', ''];
        $rows[] = ['Current Assets', ''];
        foreach ($this->data['current_assets'] as $asset) {
            $rows[] = [$asset['account'], $currency . number_format($asset['amount'], 2)];
        }
        $rows[] = ['', ''];

        $rows[] = ['Fixed Assets', ''];
        foreach ($this->data['fixed_assets'] as $asset) {
            $rows[] = [$asset['account'], $currency . number_format($asset['amount'], 2)];
        }
        $rows[] = ['', ''];

        // Liabilities
        $rows[] = ['LIABILITIES', ''];
        $rows[] = ['Current Liabilities', ''];
        foreach ($this->data['current_liabilities'] as $liability) {
            $rows[] = [$liability['account'], $currency . number_format($liability['amount'], 2)];
        }
        $rows[] = ['', ''];

        $rows[] = ['Long-term Liabilities', ''];
        foreach ($this->data['long_term_liabilities'] as $liability) {
            $rows[] = [$liability['account'], $currency . number_format($liability['amount'], 2)];
        }
        $rows[] = ['', ''];

        // Equity
        $rows[] = ['EQUITY', ''];
        foreach ($this->data['equity'] as $equity) {
            $rows[] = [$equity['account'], $currency . number_format($equity['amount'], 2)];
        }

        return $rows;
    }

    private function formatCashFlowData(): array
    {
        $currency = $this->data['currency']['symbol'] ?? '$';
        $rows = [];

        $rows[] = ['CASH FLOWS FROM OPERATING ACTIVITIES', ''];
        $rows[] = ['Net Income', $currency . number_format($this->data['net_income'], 2)];
        $rows[] = ['Net Cash from Operating Activities', $currency . number_format($this->data['operating_cash_flow'], 2)];
        $rows[] = ['', ''];

        $rows[] = ['CASH FLOWS FROM INVESTING ACTIVITIES', ''];
        $rows[] = ['Net Cash from Investing Activities', $currency . number_format($this->data['investing_cash_flow'], 2)];
        $rows[] = ['', ''];

        $rows[] = ['CASH FLOWS FROM FINANCING ACTIVITIES', ''];
        $rows[] = ['Net Cash from Financing Activities', $currency . number_format($this->data['financing_cash_flow'], 2)];
        $rows[] = ['', ''];

        $rows[] = ['NET CHANGE IN CASH', $currency . number_format($this->data['net_cash_change'], 2)];
        $rows[] = ['Cash at Beginning of Period', $currency . number_format($this->data['beginning_cash'], 2)];
        $rows[] = ['Cash at End of Period', $currency . number_format($this->data['ending_cash'], 2)];

        return $rows;
    }

    private function formatRevenueData(): array
    {
        $currency = $this->data['currency']['symbol'] ?? '$';
        $rows = [];

        $rows[] = ['REVENUE BY CATEGORY', '', ''];
        foreach ($this->data['revenue_by_category'] as $category) {
            $percentage = $this->data['total_revenue'] > 0 
                ? ($category['amount'] / $this->data['total_revenue']) * 100 
                : 0;
            $rows[] = [
                $category['category'], 
                $currency . number_format($category['amount'], 2),
                number_format($percentage, 1) . '%'
            ];
        }
        $rows[] = ['', '', ''];
        $rows[] = ['TOTAL REVENUE', $currency . number_format($this->data['total_revenue'], 2), '100.0%'];

        return $rows;
    }
}
