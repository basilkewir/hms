<?php

namespace App\Services;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    protected $currencySymbol = '$';

    /**
     * Export data to various formats
     */
    public function export($data, $fileName, $format = 'pdf', $headings = [])
    {
        $this->currencySymbol = $data['currency']['symbol'] ?? '$';

        switch ($format) {
            case 'pdf':
                return $this->exportPdf($data, $fileName);
            case 'excel':
            case 'xlsx':
                return $this->exportExcel($data, $fileName, $headings);
            case 'csv':
                return $this->exportCsv($data, $fileName, $headings);
            default:
                throw new \Exception('Invalid export format: ' . $format);
        }
    }

    /**
     * Export to PDF
     */
    private function exportPdf($data, $fileName)
    {
        $pdf = Pdf::loadView('pdf.financial-report', [
            'reportData' => $data,
            'reportType' => $data['report_type'] ?? 'report',
            'filename' => $fileName
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'dpi' => 96,
            'enable_remote' => true,
        ]);

        return $pdf->download($fileName . '.pdf');
    }

    /**
     * Export to Excel using FromCollection
     */
    private function exportExcel($data, $fileName, $headings = [])
    {
        $export = new class($data, $headings, $this->currencySymbol) implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
        {
            protected $data;
            protected $headings;
            protected $currencySymbol;

            public function __construct($data, $headings, $currencySymbol)
            {
                $this->data = $data;
                $this->headings = $headings;
                $this->currencySymbol = $currencySymbol;
            }

            public function collection(): Collection
            {
                return collect($this->formatDataForExport());
            }

            public function headings(): array
            {
                return $this->headings ?: ['Item', 'Amount'];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '366092']
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ],
                    'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
                    'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],
                ];
            }

            private function formatDataForExport(): array
            {
                $rows = [];
                $data = $this->data;

                // Handle different report types
                $reportType = $data['report_type'] ?? 'report';

                switch ($reportType) {
                    case 'profit-loss':
                        // Revenue Section
                        $rows[] = ['REVENUE', ''];
                        if (isset($data['revenue'])) {
                            foreach ($data['revenue'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $this->formatAmount($value)];
                            }
                        }
                        $rows[] = ['TOTAL REVENUE', $this->formatAmount($data['total_revenue'] ?? 0)];
                        $rows[] = ['', ''];

                        // COGS Section
                        $rows[] = ['COST OF GOODS SOLD', ''];
                        if (isset($data['cogs'])) {
                            foreach ($data['cogs'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $this->formatAmount($value)];
                            }
                        }
                        $rows[] = ['TOTAL COGS', $this->formatAmount($data['total_cogs'] ?? 0)];
                        $rows[] = ['', ''];

                        // Gross Profit
                        $rows[] = ['GROSS PROFIT', $this->formatAmount($data['gross_profit'] ?? 0)];
                        $rows[] = ['', ''];

                        // Operating Expenses
                        $rows[] = ['OPERATING EXPENSES', ''];
                        if (isset($data['operating_expenses'])) {
                            foreach ($data['operating_expenses'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $this->formatAmount($value)];
                            }
                        }
                        $rows[] = ['TOTAL OPERATING EXPENSES', $this->formatAmount($data['total_operating_expenses'] ?? 0)];
                        $rows[] = ['', ''];

                        // Net Profit
                        $rows[] = ['NET PROFIT', $this->formatAmount($data['net_profit'] ?? 0)];
                        break;

                    case 'balance-sheet':
                        // Assets
                        $rows[] = ['ASSETS', ''];
                        $rows[] = ['Current Assets', ''];
                        if (isset($data['current_assets'])) {
                            foreach ($data['current_assets'] as $asset) {
                                $rows[] = [$asset['account'] ?? '', $this->formatAmount($asset['amount'] ?? 0)];
                            }
                        }
                        $rows[] = ['Total Current Assets', $this->formatAmount($data['totals']['total_current_assets'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['Fixed Assets', ''];
                        if (isset($data['fixed_assets'])) {
                            foreach ($data['fixed_assets'] as $asset) {
                                $rows[] = [$asset['account'] ?? '', $this->formatAmount($asset['amount'] ?? 0)];
                            }
                        }
                        $rows[] = ['Total Fixed Assets', $this->formatAmount($data['totals']['total_fixed_assets'] ?? 0)];
                        $rows[] = ['', ''];
                        $rows[] = ['TOTAL ASSETS', $this->formatAmount($data['totals']['total_assets'] ?? 0)];
                        $rows[] = ['', ''];

                        // Liabilities
                        $rows[] = ['LIABILITIES', ''];
                        $rows[] = ['Current Liabilities', ''];
                        if (isset($data['current_liabilities'])) {
                            foreach ($data['current_liabilities'] as $liability) {
                                $rows[] = [$liability['account'] ?? '', $this->formatAmount($liability['amount'] ?? 0)];
                            }
                        }
                        $rows[] = ['Total Current Liabilities', $this->formatAmount($data['totals']['total_current_liabilities'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['Long-term Liabilities', ''];
                        if (isset($data['long_term_liabilities'])) {
                            foreach ($data['long_term_liabilities'] as $liability) {
                                $rows[] = [$liability['account'] ?? '', $this->formatAmount($liability['amount'] ?? 0)];
                            }
                        }
                        $rows[] = ['Total Long-term Liabilities', $this->formatAmount($data['totals']['total_long_term_liabilities'] ?? 0)];
                        $rows[] = ['', ''];
                        $rows[] = ['TOTAL LIABILITIES', $this->formatAmount($data['totals']['total_liabilities'] ?? 0)];
                        $rows[] = ['', ''];

                        // Equity
                        $rows[] = ['EQUITY', ''];
                        if (isset($data['equity'])) {
                            foreach ($data['equity'] as $equity) {
                                $rows[] = [$equity['account'] ?? '', $this->formatAmount($equity['amount'] ?? 0)];
                            }
                        }
                        $rows[] = ['TOTAL EQUITY', $this->formatAmount($data['totals']['total_equity'] ?? 0)];
                        $rows[] = ['TOTAL LIABILITIES AND EQUITY', $this->formatAmount($data['totals']['total_liabilities_and_equity'] ?? 0)];
                        break;

                    case 'cash-flow':
                        $rows[] = ['CASH FLOW STATEMENT', ''];
                        $rows[] = ['', ''];
                        $rows[] = ['Beginning Cash', $this->formatAmount($data['beginning_cash'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['OPERATING ACTIVITIES', ''];
                        $rows[] = ['Net Income', $this->formatAmount($data['net_income'] ?? 0)];
                        $rows[] = ['Net Cash from Operations', $this->formatAmount($data['net_operating_cash_flow'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['INVESTING ACTIVITIES', ''];
                        $rows[] = ['Net Cash from Investing', $this->formatAmount($data['investing_cash_flow'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['FINANCING ACTIVITIES', ''];
                        $rows[] = ['Net Cash from Financing', $this->formatAmount($data['financing_cash_flow'] ?? 0)];
                        $rows[] = ['', ''];

                        $rows[] = ['NET CHANGE IN CASH', $this->formatAmount($data['net_cash_change'] ?? 0)];
                        $rows[] = ['ENDING CASH', $this->formatAmount($data['ending_cash'] ?? 0)];
                        break;

                    case 'revenue':
                        $rows[] = ['REVENUE REPORT', ''];
                        $rows[] = ['', ''];
                        if (isset($data['revenue_by_category'])) {
                            foreach ($data['revenue_by_category'] as $category) {
                                $percentage = ($data['total_revenue'] ?? 0) > 0
                                    ? ($category['amount'] / $data['total_revenue']) * 100
                                    : 0;
                                $rows[] = [
                                    $category['category'] ?? '',
                                    $this->formatAmount($category['amount'] ?? 0) . ' (' . number_format($percentage, 1) . '%)'
                                ];
                            }
                        }
                        $rows[] = ['', ''];
                        $rows[] = ['TOTAL REVENUE', $this->formatAmount($data['total_revenue'] ?? 0)];
                        break;

                    default:
                        // Generic export
                        foreach ($data as $key => $value) {
                            if (is_array($value)) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), ''];
                                foreach ($value as $subKey => $subValue) {
                                    if (is_array($subValue)) {
                                        $rows[] = ['  ' . ($subValue['account'] ?? $subValue['name'] ?? ucwords(str_replace('_', ' ', $subKey))), $this->formatAmount($subValue['amount'] ?? 0)];
                                    } else {
                                        $rows[] = ['  ' . ucwords(str_replace('_', ' ', $subKey)), $this->formatAmount($subValue)];
                                    }
                                }
                            } else {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $this->formatAmount($value)];
                            }
                        }
                }

                return $rows;
            }

            private function formatAmount($amount): string
            {
                $num = is_numeric($amount) ? $amount : 0;
                return $this->currencySymbol . number_format($num, 2);
            }
        };

        return \Excel::download($export, $fileName . '.xlsx');
    }

    /**
     * Export to CSV using FromCollection
     */
    private function exportCsv($data, $fileName, $headings = [])
    {
        $export = new class($data, $headings, $this->currencySymbol) implements FromCollection, WithHeadings
        {
            protected $data;
            protected $headings;
            protected $currencySymbol;

            public function __construct($data, $headings, $currencySymbol)
            {
                $this->data = $data;
                $this->headings = $headings;
                $this->currencySymbol = $currencySymbol;
            }

            public function collection(): Collection
            {
                return collect($this->formatDataForExport());
            }

            public function headings(): array
            {
                return $this->headings ?: ['Item', 'Amount'];
            }

            private function formatDataForExport(): array
            {
                $rows = [];
                $data = $this->data;
                $reportType = $data['report_type'] ?? 'report';

                // Same logic as Excel but simpler formatting
                switch ($reportType) {
                    case 'profit-loss':
                        $rows[] = ['REVENUE', ''];
                        if (isset($data['revenue'])) {
                            foreach ($data['revenue'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $value];
                            }
                        }
                        $rows[] = ['TOTAL REVENUE', $data['total_revenue'] ?? 0];
                        $rows[] = ['', ''];
                        $rows[] = ['COST OF GOODS SOLD', ''];
                        if (isset($data['cogs'])) {
                            foreach ($data['cogs'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $value];
                            }
                        }
                        $rows[] = ['TOTAL COGS', $data['total_cogs'] ?? 0];
                        $rows[] = ['', ''];
                        $rows[] = ['GROSS PROFIT', $data['gross_profit'] ?? 0];
                        $rows[] = ['', ''];
                        $rows[] = ['OPERATING EXPENSES', ''];
                        if (isset($data['operating_expenses'])) {
                            foreach ($data['operating_expenses'] as $key => $value) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $value];
                            }
                        }
                        $rows[] = ['TOTAL OPERATING EXPENSES', $data['total_operating_expenses'] ?? 0];
                        $rows[] = ['', ''];
                        $rows[] = ['NET PROFIT', $data['net_profit'] ?? 0];
                        break;

                    case 'balance-sheet':
                        $rows[] = ['ASSETS', ''];
                        if (isset($data['current_assets'])) {
                            foreach ($data['current_assets'] as $asset) {
                                $rows[] = [$asset['account'] ?? '', $asset['amount'] ?? 0];
                            }
                        }
                        if (isset($data['fixed_assets'])) {
                            foreach ($data['fixed_assets'] as $asset) {
                                $rows[] = [$asset['account'] ?? '', $asset['amount'] ?? 0];
                            }
                        }
                        $rows[] = ['', ''];
                        $rows[] = ['LIABILITIES', ''];
                        if (isset($data['current_liabilities'])) {
                            foreach ($data['current_liabilities'] as $liability) {
                                $rows[] = [$liability['account'] ?? '', $liability['amount'] ?? 0];
                            }
                        }
                        $rows[] = ['', ''];
                        $rows[] = ['EQUITY', ''];
                        if (isset($data['equity'])) {
                            foreach ($data['equity'] as $equity) {
                                $rows[] = [$equity['account'] ?? '', $equity['amount'] ?? 0];
                            }
                        }
                        break;

                    case 'cash-flow':
                        $rows[] = ['CASH FLOW STATEMENT', ''];
                        $rows[] = ['Beginning Cash', $data['beginning_cash'] ?? 0];
                        $rows[] = ['Net Income', $data['net_income'] ?? 0];
                        $rows[] = ['Operating Cash Flow', $data['net_operating_cash_flow'] ?? 0];
                        $rows[] = ['Investing Cash Flow', $data['investing_cash_flow'] ?? 0];
                        $rows[] = ['Financing Cash Flow', $data['financing_cash_flow'] ?? 0];
                        $rows[] = ['Net Cash Change', $data['net_cash_change'] ?? 0];
                        $rows[] = ['Ending Cash', $data['ending_cash'] ?? 0];
                        break;

                    case 'revenue':
                        $rows[] = ['REVENUE REPORT', ''];
                        if (isset($data['revenue_by_category'])) {
                            foreach ($data['revenue_by_category'] as $category) {
                                $rows[] = [$category['category'] ?? '', $category['amount'] ?? 0];
                            }
                        }
                        $rows[] = ['TOTAL REVENUE', $data['total_revenue'] ?? 0];
                        break;

                    default:
                        foreach ($data as $key => $value) {
                            if (is_array($value)) {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), ''];
                                foreach ($value as $subKey => $subValue) {
                                    $rows[] = ['  ' . ucwords(str_replace('_', ' ', $subKey)), is_array($subValue) ? ($subValue['amount'] ?? '') : $subValue];
                                }
                            } else {
                                $rows[] = [ucwords(str_replace('_', ' ', $key)), $value];
                            }
                        }
                }

                return $rows;
            }
        };

        return \Excel::download($export, $fileName . '.csv');
    }
}
