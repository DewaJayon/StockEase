<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogStockController extends Controller
{

    /**
     * Handle log stock filtering and rendering.
     * 
     * The function expects search and per_page parameters to be passed in the request.
     * It will filter log stock based on the given parameters and return an Inertia response
     * with the filtered log stock data.
     * 
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $filters = [
            'end_date' => $request->end,
            'start_date' => $request->start
        ];

        $logStocks = StockLog::query()
            ->with('product')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                })
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            })
            ->when($filters['start_date'] && $filters['end_date'], function ($query) use ($filters) {
                $query->whereBetween('created_at', [
                    Carbon::parse($filters['start_date'])->startOfDay(),
                    Carbon::parse($filters['end_date'])->endOfDay()
                ]);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('LogStock/Index', [
            'logStocks' => $logStocks
        ]);
    }
}
