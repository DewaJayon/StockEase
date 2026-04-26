<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\ProfitLossReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfitLossReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProfitLossReportService $profitLossService
    ) {}

    /**
     * Display the Profit & Loss report.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $perPage = $request->input('per_page', 10);

        $summary = $this->profitLossService->getProfitLossSummary($startDate, $endDate);
        $productBreakdown = $this->profitLossService->getProductBreakdown($startDate, $endDate, (int) $perPage);
        $chartData = $this->profitLossService->getChartData($startDate, $endDate);

        return Inertia::render('Reports/ProfitLoss/Index', [
            'summary' => $summary,
            'productBreakdown' => $productBreakdown,
            'chartData' => $chartData,
            'filters' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }
}
