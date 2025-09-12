<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Purcase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PurchaseExportExcel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class PurchaseReportController extends Controller
{

    /**
     * Handles purchase report filtering and rendering.
     * 
     * The function expects start_date, end_date, supplier, and user parameters
     * to be passed in the request. It will filter purchases based on the given
     * parameters and return an Inertia response with the filtered purchases data.
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {

        $filters = [
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'supplier'   => $request->supplier,
            'user'       => $request->user,
        ];

        $filteredPurchases = [];

        $query = collect();

        if ($filters['start_date'] || $filters['end_date'] || $filters['supplier'] || $filters['user']) {

            $query = Purcase::with('supplier', 'user', 'purcaseItems', 'purcaseItems.product')
                ->when($filters['start_date'], function ($query) use ($filters) {
                    return $query->whereDate('created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($query) use ($filters) {
                    return $query->whereDate('created_at', '<=', $filters['end_date']);
                })
                ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                    return $query->where('supplier_id', $filters['supplier']);
                })
                ->when($filters['user'] && $filters['user'] !== 'semua-user', function ($query) use ($filters) {
                    return $query->where('user_id', $filters['user']);
                })
                ->get();

            $sumTotalPurchase = $query->sum('total');
            $totalItemsPurchased = $query->flatMap->purcaseItems->sum('qty');
            $totalTransaction = $query->count();

            Carbon::setLocale('id');

            $purchaseTrends = $query->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->translatedFormat('M');
            })->map(function ($item) {
                return $item->sum('total');
            });

            $purchaseTrends = [
                'labels' => $purchaseTrends->keys()->values(),
                'data'   => $purchaseTrends->values(),
            ];

            $topSupplier = $query->groupBy('supplier_id')->map(function ($items) {
                return [
                    'supplier_name' => $items->first()->supplier->name,
                    'total_purchase' => $items->sum('total'),
                    'transaction_count' => $items->count(),
                ];
            })->sortByDesc('total_purchase')->take(5)->values();

            $filteredPurchases = [
                'filters'               => $query->toArray(),
                'sumTotalPurchase'      => $sumTotalPurchase,
                'totalItemsPurchased'   => $totalItemsPurchased,
                'totalTransaction'      => $totalTransaction,
                'purchaseTrends'        => $purchaseTrends,
                'topSupplier'           => $topSupplier
            ];
        }

        return Inertia::render('Reports/Purchase/Index', [
            'filters' => $filteredPurchases
        ]);
    }


    /**
     * Search suppliers by name
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function searchSupplier(Request $request)
    {
        if ($request->expectsJson()) {

            if (blank($request->search)) {
                return response()->json([
                    "message"   => "empty search",
                    "data"      => []
                ], 200);
            }

            $supplier = Supplier::where(function ($q) use ($request) {
                $q->where("name", "like", "%{$request->search}%")
                    ->orWhere("id", "like", "%{$request->search}%");
            })->select("id as value", "name as label")->get();

            if ($supplier->isEmpty()) {
                return response()->json([
                    "message"   => "supplier not found",
                    "data"      => null
                ], 404);
            }

            return response()->json([
                "message"   => "success",
                "data"      => $supplier
            ], 200);
        }
    }


    /**
     * Search users by name or ID
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUser(Request $request)
    {
        if ($request->expectsJson()) {

            if (blank($request->search)) {
                return response()->json([
                    "message"   => "empty search",
                    "data"      => []
                ], 200);
            }

            $user = User::where(function ($q) use ($request) {
                $q->where("name", "like", "%{$request->search}%")
                    ->orWhere("id", "like", "%{$request->search}%")
                    ->whereIn("role", ["warehouse", "admin"]);
            })->select("id as value", "name as label")->get();

            if ($user->isEmpty()) {
                return response()->json([
                    "message"   => "user not found",
                    "data"      => null
                ], 404);
            }

            return response()->json([
                "message"   => "success",
                "data"      => $user
            ], 200);
        }
    }

    /**
     * Export purchase report to PDF
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'supplier'   => 'required',
            'user'       => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.purchase.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $query = Purcase::with('supplier', 'user', 'purcaseItems', 'purcaseItems.product', 'purcaseItems.purcase')
            ->when($filters['start_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '>=', $filters['start_date']);
            })
            ->when($filters['end_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '<=', $filters['end_date']);
            })
            ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                return $query->where('supplier_id', $filters['supplier']);
            })
            ->when($filters['user'] && $filters['user'] !== 'semua-user', function ($query) use ($filters) {
                return $query->where('user_id', $filters['user']);
            })
            ->get();

        $sumTotalPurchase = $query->sum('total');
        $totalItemsPurchased = $query->flatMap->purcaseItems->sum('qty');
        $totalTransaction = $query->count();

        $purchaseProducts = $query->flatMap->purcaseItems
            ->groupBy('product_id')->map(function ($items) {
                return  (object) [
                    'date'              => $items->first()->purcase->created_at,
                    'product_name'      => $items->first()->product->name,
                    'product_price'     => $items->first()->price,
                    'total_purchase'    => $items->first()->price * $items->first()->qty,
                    'qty'               => $items->first()->qty,
                ];
            })
            ->values();

        $user = $filters['user'] === 'semua-user' ? 'semua-user' : User::find($filters['user'])->name;
        $supplier = $filters['supplier'] === 'semua-supplier' ? 'semua-supplier' : Supplier::find($filters['supplier'])->name;

        $data = [
            'startDate'             => Carbon::parse($filters['start_date'])->translatedFormat('d F Y'),
            'endDate'               => Carbon::parse($filters['end_date'])->translatedFormat('d F Y'),
            'purchases'             => $purchaseProducts,
            'sumTotalPurchase'      => $sumTotalPurchase,
            'totalItemsPurchased'   => $totalItemsPurchased,
            'totalTransaction'      => $totalTransaction,
            'user'                  => $user,
            'supplier'              => $supplier
        ];

        $pdf = Pdf::loadView('exports.purchase-report.export-pdf', $data);

        $fileName = "Laporan Pembelian "
            . Carbon::parse($filters['start_date'])->translatedFormat('d F Y') . " - "
            . Carbon::parse($filters['end_date'])->translatedFormat('d F Y') . " StockEase.pdf";

        $filePath = "reports/purchase/"
            . Carbon::now('Asia/Shanghai')->format('Y') . "/"
            . Carbon::now('Asia/Shanghai')->translatedFormat('F') . "/"
            . $fileName;

        Storage::put($filePath, $pdf->output());

        return $pdf->download($fileName);
    }

    /**
     * Export the purchase report to an Excel file.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'supplier'   => 'required',
            'user'       => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.purchase.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $query = Purcase::with('supplier', 'user', 'purcaseItems', 'purcaseItems.product', 'purcaseItems.purcase')
            ->when($filters['start_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '>=', $filters['start_date']);
            })
            ->when($filters['end_date'], function ($query) use ($filters) {
                return $query->whereDate('created_at', '<=', $filters['end_date']);
            })
            ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                return $query->where('supplier_id', $filters['supplier']);
            })
            ->when($filters['user'] && $filters['user'] !== 'semua-user', function ($query) use ($filters) {
                return $query->where('user_id', $filters['user']);
            })
            ->get();

        $user = $filters['user'] === 'semua-user' ? 'Semua User' : User::find($filters['user'])->name;
        $supplier = $filters['supplier'] === 'semua-supplier' ? 'Semua Supplier' : Supplier::find($filters['supplier'])->name;

        $sumTotalPurchase = $query->sum('total');
        $totalItemsPurchased = $query->flatMap->purcaseItems->sum('qty');
        $totalTransaction = $query->count();

        $suppliers = $query
            ->map(function ($item) {
                return (object) [
                    'id' => $item->supplier->id,
                    'name' => $item->supplier->name,
                    'total' => $item->total,
                    'qty' => $item->purcaseItems->sum('qty')
                ];
            })
            ->groupBy('id')
            ->map(function ($items) {
                return (object) [
                    'name' => $items->first()->name,
                    'total' => $items->sum('total'),
                    'qty' => $items->sum('qty')
                ];
            })
            ->values();

        $filters = [
            'start_date'            => $filters['start_date'],
            'end_date'              => $filters['end_date'],
            'supplier'              => $supplier,
            'user'                  => $user,
        ];

        $summary = [
            'sumTotalPurchase'      => $sumTotalPurchase,
            'totalItemsPurchased'   => $totalItemsPurchased,
            'totalTransaction'      => $totalTransaction,
            'suppliers'             => $suppliers
        ];

        $fileName = "Laporan Pembelian "
            . Carbon::parse($filters['start_date'])->translatedFormat('d F Y') . " - "
            . Carbon::parse($filters['end_date'])->translatedFormat('d F Y') . " StockEase.xlsx";

        $filePath = "reports/purchase/"
            . Carbon::now('Asia/Shanghai')->format('Y') . "/"
            . Carbon::now('Asia/Shanghai')->translatedFormat('F') . "/"
            . $fileName;

        Excel::store(new PurchaseExportExcel($query, $filters, $summary), $filePath, "local");

        return Excel::download(new PurchaseExportExcel($query, $filters, $summary), $fileName);
    }
}
