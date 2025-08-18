<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleRepotController extends Controller
{

    /**
     * Index function handles report sales data based on given request
     * parameters. It will return Inertia response with filtered sales data.
     *
     * @param Request $request The request object containing start_date, end_date,
     *                        cashier, and payment parameters.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $cashier = $request->cashier;
        $payment = $request->payment;

        $filteredSales = [];

        if ($startDate && $endDate && $cashier && $payment) {
            $query = Sale::with('user', 'saleItems', 'saleItems.product', 'paymentTransaction')
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay(),
                    ]);
                })
                ->when($cashier, function ($query) use ($cashier) {
                    return $query->where('user_id', $cashier);
                })
                ->when($payment, function ($query) use ($payment) {
                    return $query->where('payment_method', $payment);
                })
                ->get();

            $sumTotalSale       = $query->sum('total');
            $transactionCount   = $query->where('status', 'completed')->count();
            $countProductSale   = $query->flatMap->saleItems->count();

            $bestSellingProduct = $query
                ->flatMap->saleItems
                ->groupBy('product_id')
                ->map(function ($items) {
                    return [
                        'product_id'   => $items->first()->product_id,
                        'product_name' => $items->first()->product->name ?? 'Unknown',
                        'total_sold'   => $items->sum('qty'),
                    ];
                })
                ->sortByDesc('total_sold')
                ->first();

            Carbon::setLocale('id');

            $salesTrend = $query
                ->groupBy(function ($sale) {
                    return Carbon::parse($sale->created_at)->translatedFormat('M');
                })
                ->map(function ($sales) {
                    return $sales->sum('total');
                });

            $salesTrend = [
                'labels' => $salesTrend->keys()->values(),
                'data'   => $salesTrend->values(),
            ];

            $productSalesShare = $query
                ->flatMap->saleItems
                ->groupBy('product_id')
                ->map(function ($items) {
                    return [
                        'product_name' => $items->first()->product->name ?? 'Unknown',
                        'total_sold'   => $items->sum('qty'),
                    ];
                })
                ->values();

            $filteredSales = [
                'sales'                 => $query,
                'sumTotalSale'          => $sumTotalSale,
                'transactionCount'      => $transactionCount,
                'countProductSale'      => $countProductSale,
                'bestSellingProduct'    => $bestSellingProduct,
                'salesTrend'            => $salesTrend,
                'productSalesShare'     => $productSalesShare
            ];
        }

        return Inertia::render('Reports/Sale/Index', [
            'sales' => $filteredSales
        ]);
    }

    /**
     * Search cashier by name.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function searchCashier(Request $request)
    {
        if ($request->expectsJson()) {

            if (blank($request->search)) {
                return response()->json([
                    "message"   => "empty search",
                    "data"      => []
                ], 200);
            }

            $cashier = User::where("name", "like", "%{$request->search}%")
                ->where("role", "cashier")
                ->orWhere("role", "admin")
                ->select("id as value", "name as label")
                ->get();

            if ($cashier->isEmpty()) {
                return response()->json([
                    "message"   => "cashier not found",
                    "data"      => null
                ], 404);
            }

            return response()->json([
                "message"   => "Success get cashier",
                "data"      => $cashier
            ], 200);
        }
    }

    public function printPdf(Request $request)
    {
        $data = [
            'start_date' => '27 July 2025',
            'end_date' => '06 September 2025',
            'cashier_name' => 'Dewa Jayon',
            'payment' => 'Midtrans',
            'total_sales' => 90000,
            'transaction_count' => 2,
            'product_sold' => 4,
            'best_selling_product' => 'Apel Merah',
            'sales' => collect([
                (object)['date' => '2025-07-27', 'product_name' => 'Apel Merah', 'quantity' => 2, 'total' => 45000],
                (object)['date' => '2025-08-01', 'product_name' => 'Nugget Ayam', 'quantity' => 2, 'total' => 45000],
            ]),
        ];

        return view('exports.sales.report', $data);
    }
}
