<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\PosTerminal;
use App\Exports\PosReportExport;
use Dompdf\Dompdf;

class PosReportController extends Controller
{
    protected $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Show POS Reports Dashboard
     */
    public function dashboard()
    {
        $startDate = request('start_date', Carbon::now()->startOfDay());
        $endDate = request('end_date', Carbon::now()->endOfDay());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $data = $this->financialService->getPosDashboardData($startDate, $endDate);

        return view('admin.pos.reports.dashboard', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Show Daily Sales Report
     */
    public function dailySales()
    {
        $startDate = request('start_date', Carbon::now()->startOfDay());
        $endDate = request('end_date', Carbon::now()->endOfDay());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getDailySalesReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.daily-sales', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show Category Sales Report
     */
    public function categorySales()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth());
        $endDate = request('end_date', Carbon::now()->endOfMonth());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getCategorySalesReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.category-sales', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show Employee Sales Report
     */
    public function employeeSales()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth());
        $endDate = request('end_date', Carbon::now()->endOfMonth());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getEmployeeSalesReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.employee-sales', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show Product Performance Report
     */
    public function productPerformance()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth());
        $endDate = request('end_date', Carbon::now()->endOfMonth());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getProductPerformanceReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.product-performance', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show POS Terminal Report
     */
    public function posTerminal()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth());
        $endDate = request('end_date', Carbon::now()->endOfMonth());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getPosTerminalReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.pos-terminal', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show Payment Method Analysis
     */
    public function paymentMethod()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth());
        $endDate = request('end_date', Carbon::now()->endOfMonth());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $filters = $this->getFilters();
        $data = $this->financialService->getPaymentMethodReport($startDate, $endDate, $filters);

        return view('admin.pos.reports.payment-method', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters
        ]);
    }

    /**
     * Show Time-Based Analysis
     */
    public function timeBased()
    {
        $startDate = request('start_date', Carbon::now()->startOfDay());
        $endDate = request('end_date', Carbon::now()->endOfDay());

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $timePeriod = request('time_period', 'hour');
        $filters = $this->getFilters();
        $data = $this->financialService->getTimeBasedReport($startDate, $endDate, $timePeriod, $filters);

        return view('admin.pos.reports.time-based', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'timePeriod' => $timePeriod,
            'filters' => $filters
        ]);
    }

    /**
     * Export reports to various formats
     */
    public function export(Request $request)
    {
        $reportType = $request->input('report_type');
        $format = $request->input('format', 'pdf');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filters = $request->input('filters', []);

        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        switch ($reportType) {
            case 'daily-sales':
                $data = $this->financialService->getDailySalesReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.daily-sales';
                break;
            case 'category-sales':
                $data = $this->financialService->getCategorySalesReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.category-sales';
                break;
            case 'employee-sales':
                $data = $this->financialService->getEmployeeSalesReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.employee-sales';
                break;
            case 'product-performance':
                $data = $this->financialService->getProductPerformanceReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.product-performance';
                break;
            case 'pos-terminal':
                $data = $this->financialService->getPosTerminalReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.pos-terminal';
                break;
            case 'payment-method':
                $data = $this->financialService->getPaymentMethodReport($startDate, $endDate, $filters);
                $view = 'admin.pos.reports.exports.payment-method';
                break;
            case 'time-based':
                $timePeriod = $request->input('time_period', 'hour');
                $data = $this->financialService->getTimeBasedReport($startDate, $endDate, $timePeriod, $filters);
                $view = 'admin.pos.reports.exports.time-based';
                break;
            default:
                return redirect()->back()->with('error', 'Invalid report type');
        }

        $data['report_type'] = $reportType;
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        $data['filters'] = $filters;

        return $this->exportReport($view, $data, $reportType, $format);
    }

    /**
     * Get filter options for reports
     */
    protected function getFilters()
    {
        return [
            'category_id' => request('category_id'),
            'product_id' => request('product_id'),
            'user_id' => request('user_id'),
            'payment_method' => request('payment_method'),
            'pos_terminal' => request('pos_terminal')
        ];
    }

    /**
     * Export report to specified format
     */
    protected function exportReport($view, $data, $reportType, $format)
    {
        $reportName = $this->getReportName($reportType);
        $fileName = $reportName . '_' . now()->format('Y-m-d_H-i-s');

        switch ($format) {
            case 'pdf':
                $pdf = \PDF::loadView($view, $data)
                    ->setPaper('a4', 'landscape');
                return $pdf->download($fileName . '.pdf');

            case 'excel':
                return \Excel::download(new \App\Exports\PosReportExport($view, $data), $fileName . '.xlsx');

            case 'csv':
                return \Excel::download(new \App\Exports\PosReportExport($view, $data), $fileName . '.csv');

            case 'word':
                $html = view($view, $data)->render();
                $domPdf = new \Dompdf\Dompdf();
                $domPdf->loadHtml($html);
                $domPdf->setPaper('A4', 'landscape');
                $domPdf->render();
                return response($domPdf->output())
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                    ->header('Content-Disposition', 'attachment; filename="' . $fileName . '.docx"');

            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    /**
     * Get human-readable report name
     */
    protected function getReportName($reportType)
    {
        $names = [
            'daily-sales' => 'Daily Sales Report',
            'category-sales' => 'Category Sales Report',
            'employee-sales' => 'Employee Sales Report',
            'product-performance' => 'Product Performance Report',
            'pos-terminal' => 'POS Terminal Report',
            'payment-method' => 'Payment Method Analysis',
            'time-based' => 'Time Based Analysis'
        ];

        return $names[$reportType] ?? 'POS Report';
    }

    /**
     * Get filter options for dropdowns
     */
    public function getFilterOptions()
    {
        $categories = \App\Models\ProductCategory::all();
        $products = \App\Models\Product::with('category')->get();
        $users = \App\Models\User::role(['admin', 'manager', 'staff'])->get();
        $terminals = \App\Models\PosTerminal::all();

        return response()->json([
            'categories' => $categories,
            'products' => $products,
            'users' => $users,
            'terminals' => $terminals,
            'payment_methods' => ['cash', 'card', 'mobile', 'credit']
        ]);
    }
}
