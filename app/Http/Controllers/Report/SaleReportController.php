<?php

namespace App\Http\Controllers\Report;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SaleReportController extends Controller
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

            $cashier = User::where(function ($q) use ($request) {
                $q->where("name", "like", "%{$request->search}%")
                    ->orWhere("id", "like", "%{$request->search}%")
                    ->whereIn("role", ["cashier", "admin"]);
            })->select("id as value", "name as label")->get();

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

    /**
     * Generate a PDF report for a given date range, cashier, and payment method.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToPdf(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'cashier'       => 'required',
            'payment'       => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.sale.index')
                ->withErrors($validator)
                ->withInput();
        }

        $startDate  = $request->start_date;
        $endDate    = $request->end_date;
        $cashier    = $request->cashier;
        $payment    = $request->payment;

        $cashierUser = User::find($cashier);

        $query = Sale::with('user', 'saleItems', 'saleItems.product', 'paymentTransaction')
            ->where('payment_method', '!=', 'pending')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->when($payment, function ($query) use ($payment) {
                return $query->where('payment_method', $payment);
            })
            ->get();

        $totalSale = $query->sum('total');
        $transactionCount = $query->where('status', 'completed')->count();
        $productSold = $query->flatMap->saleItems->count();

        $bestSellingProduct = $query
            ->flatMap->saleItems
            ->groupBy('product_id')
            ->map(function ($items) {
                return (object) [
                    'product_id'   => $items->first()->product_id,
                    'product_name' => $items->first()->product->name ?? 'Unknown',
                    'total_sold'   => $items->sum('qty'),
                ];
            })
            ->sortByDesc('total_sold')
            ->first();

        $saleProducts = $query->flatMap->saleItems
            ->groupBy('product_id')
            ->map(function ($items) {

                $firstItem = $items->first();

                return (object) [
                    'date'          => $firstItem->sale->created_at,
                    'product_name'  => $firstItem->product->name ?? 'Unknown',
                    'quantity'      => $items->sum('qty'),
                    'total'         => $items->sum('price'),
                ];
            })
            ->values();

        $data = [
            'start_date'            => Carbon::parse($startDate)->translatedFormat('d F Y'),
            'end_date'              => Carbon::parse($endDate)->translatedFormat('d F Y'),
            'cashier_name'          => $cashierUser?->name ?? 'Unknown',
            'payment'               => $payment,
            'total_sales'           => $totalSale,
            'transaction_count'     => $transactionCount,
            'product_sold'          => $productSold,
            'best_selling_product'  => $bestSellingProduct,
            'sales'                 => $saleProducts,
        ];

        $pdf = Pdf::loadView('exports.sales.report', $data);

        $fileName = "Sale Report {$startDate} - {$endDate} StockEase.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Generate an Excel report for a given date range, cashier, and payment method.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'cashier'       => 'required',
            'payment'       => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.sale.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $query = Sale::with(['user', 'saleItems.product'])
            ->when($filters['start_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '>=', $filters['start_date']);
            })
            ->when($filters['end_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '<=', $filters['end_date']);
            })
            ->when($filters['cashier'], function ($query) use ($filters) {
                return $query->where('user_id', $filters['cashier']);
            })
            ->when($filters['payment'], function ($query) use ($filters) {
                return $query->where('payment_method', $filters['payment']);
            })
            ->get();

        $cashierName = $filters['cashier']
            ? User::find($filters['cashier'])->name
            : 'Semua';

        $summary = [
            'total_sales'       => number_format($query->sum('total')),
            'transaction_count' => $query->count(),
            'product_count'     => $query->flatMap->saleItems->sum('qty'),
            'best_product'      => $query->flatMap->saleItems
                ->groupBy('product_id')
                ->map->sum('quantity')
                ->sortDesc()
                ->keys()
                ->map(fn($id) => Product::find($id)->name)
                ->first() ?? '-',
        ];

        $filters['cashier'] = $cashierName;

        $fileName = "Sale Report {$filters['start_date']} - {$filters['end_date']} StockEase.xlsx";

        return Excel::download(new SalesReportExport($query, $filters, $summary), $fileName);
    }
}
