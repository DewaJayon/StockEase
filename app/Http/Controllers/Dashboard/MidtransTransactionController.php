<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MidtransTransactionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * Retrieves a paginated list of midtrans transactions, including related
     * payment status, external id, and created at. Supports searching by
     * external id and date filtering.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $midtransTransactions = PaymentTransaction::with([
            'sale',
            'sale.saleItems',
            'sale.saleItems.product',
        ])
            ->when($request->search, function ($query, $search) {
                return $query->where('external_id', 'like', '%' . $search . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('MidtransTransaction/Index', [
            'midtransTransactions' => $midtransTransactions
        ]);
    }
}
