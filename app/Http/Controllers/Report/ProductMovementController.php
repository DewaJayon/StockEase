<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\ProductMovementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductMovementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductMovementService $productMovementService
    ) {}

    /**
     * Display the Product Movement (Fast & Slow Moving) analysis report.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $fastMoving = $this->productMovementService->getFastMovingProducts($startDate, $endDate);
        $slowMoving = $this->productMovementService->getSlowMovingProducts($startDate, $endDate);
        $chartData = $this->productMovementService->buildChartData($fastMoving, $slowMoving);
        $summary = $this->productMovementService->getSummaryStats($startDate, $endDate);

        return Inertia::render('Reports/ProductMovement/Index', [
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'chartData' => $chartData,
            'summary' => $summary,
            'filters' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }
}
