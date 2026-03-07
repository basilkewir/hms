<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$startDate, $endDate] = $this->getDateRange($period, $startDate, $endDate);

        $revenueData = $this->financialService->getRevenueData($startDate, $endDate);

        return Inertia::render('Manager/Reports/Revenue', [
            'user' => auth()->user()->load('roles'),
            'revenueData' => $revenueData,
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currency' => $this->financialService->getCurrency(),
        ]);
    }

    private function getDateRange($period, $startDate = null, $endDate = null): array
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
}

