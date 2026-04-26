<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Services\Stock\ExpiryReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpiryReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ExpiryReportService $expiryService
    ) {}

    /**
     * Display a listing of products with expiry dates.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $expiryData = $this->expiryService->getPaginatedExpiryItems($filters);

        return Inertia::render('Reports/Expiry/Index', [
            'expiryData' => $expiryData,
            'filters' => $filters,
        ]);
    }
}
