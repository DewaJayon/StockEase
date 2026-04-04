<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MidtransTransactionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $midtransTransactions = $this->paymentService->getPaginatedTransactions(
            $request->only(['search', 'start', 'end']),
            $perPage
        );

        return Inertia::render('MidtransTransaction/Index', [
            'midtransTransactions' => $midtransTransactions,
        ]);
    }
}
