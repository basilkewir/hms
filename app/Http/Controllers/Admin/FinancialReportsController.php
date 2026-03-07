<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FinancialReportsController extends Controller
{
    protected $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $this->authorize('view financial reports');

        // Get date filters from request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get financial data for the dashboard with date filtering
        $financialData = $this->financialService->getFinancialDashboardData($startDate, $endDate);

        return Inertia::render('Admin/FinancialReports/Index', [
            'financialData' => $financialData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
}
