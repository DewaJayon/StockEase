<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Services\General\DashboardService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard data for admin, cashier, warehouse
     */
    public function index()
    {
        $role = Auth::user()->role;

        $data = $this->dashboardService->getDashboardData($role);

        return Inertia::render('Dashboard/Index', [
            'data' => $data,
        ]);
    }
}
