<?php

namespace App\Http\Controllers\Report;

use App\Exports\StockExportExcel;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StockReportController extends Controller
{

    /**
     * Handle stock report filtering and rendering.
     * 
     * The function expects category, supplier, start_date, and end_date parameters
     * to be passed in the request. It will filter products based on the given
     * parameters and return an Inertia response with the filtered products data.
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $filters = [
            'category'      => $request->category,
            'supplier'      => $request->supplier,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date
        ];

        $filteredStocks = [];

        $query = collect();

        if ($filters['category'] || $filters['supplier'] || $filters['start_date'] || $filters['end_date']) {

            $query = Product::with(['category', 'purchaseItems.purcase.supplier'])
                ->whereHas('purchaseItems')
                ->when($filters['category'] && $filters['category'] !== 'semua-kategori', function ($query) use ($filters) {
                    return $query->where('category_id', $filters['category']);
                })
                ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                    return $query->whereHas('purchaseItems.purcase.supplier', function ($q) use ($filters) {
                        $q->where('id', $filters['supplier']);
                    });
                })
                ->when($filters['start_date'], function ($query) use ($filters) {
                    return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                        $q->whereDate('date', '>=', $filters['start_date']);
                    });
                })
                ->when($filters['end_date'], function ($query) use ($filters) {
                    return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                        $q->whereDate('date', '<=', $filters['end_date']);
                    });
                })
                ->paginate(10)
                ->withQueryString();

            $filteredStocks = $query->through(function ($product) {
                $firstPurchase = $product->purchaseItems->first();
                $supplierName = $firstPurchase ? $firstPurchase->purcase->supplier->name : '-';

                return [
                    'id'            => $product->id,
                    'name'          => $product->name,
                    'category'      => $product->category->name,
                    'stock'         => $product->stock,
                    'alert_stock'   => $product->alert_stock,
                    'supplier'      => $supplierName,
                ];
            });
        }

        return Inertia::render('Reports/Stock/Index', [
            'filteredStocks'    => $filteredStocks
        ]);
    }


    /**
     * Search category by name
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCategory(Request $request)
    {
        if ($request->expectsJson()) {

            $query = Category::query()
                ->when(is_numeric($request->search), function ($q) use ($request) {
                    $q->where('id', $request->search);
                }, function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })
                ->limit(5)
                ->get();

            if ($query->isEmpty()) {
                return response()->json([
                    "message"   => "category not found",
                    "data"      => null
                ], 404);
            }

            return response()->json([
                "message"   => "success",
                "data"      => $query
            ], 200);
        }
    }


    /**
     * Search supplier by name.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSupplier(Request $request)
    {
        if ($request->expectsJson()) {

            $query = Supplier::query()
                ->when(is_numeric($request->search), function ($q) use ($request) {
                    $q->where('id', $request->search);
                }, function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })
                ->limit(5)
                ->get();

            if ($query->isEmpty()) {
                return response()->json([
                    "message"   => "supplier not found",
                    "data"      => null
                ], 404);
            }

            return response()->json([
                "message"   => "success",
                "data"      => $query
            ]);
        }
    }

    /**
     * Export the stock report to a PDF file.
     * 
     * This function validates the request for the required parameters:
     * start_date, end_date, category, and supplier.
     * 
     * It then filters the products based on the given parameters and
     * generates a PDF report using the Blade template
     * exports.stock-report.export-pdf.
     * 
     * The PDF is then downloaded with a filename that includes the
     * start and end dates of the report.
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'category'      => 'required',
            'supplier'      => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.stock.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $query = Product::with(['category', 'purchaseItems.purcase.supplier'])
            ->whereHas('purchaseItems')
            ->when($filters['category'] && $filters['category'] !== 'semua-kategori', function ($query) use ($filters) {
                return $query->where('category_id', $filters['category']);
            })
            ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase.supplier', function ($q) use ($filters) {
                    $q->where('id', $filters['supplier']);
                });
            })
            ->when($filters['start_date'], function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                    $q->whereDate('date', '>=', $filters['start_date']);
                });
            })
            ->when($filters['end_date'], function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                    $q->whereDate('date', '<=', $filters['end_date']);
                });
            })
            ->get();

        $filteredStocks = $query->map(function ($product) {
            $firstPurchase = $product->purchaseItems->first();
            $supplierName = $firstPurchase ? $firstPurchase->purcase->supplier->name : '-';

            return (object) [
                'id'            => $product->id,
                'name'          => $product->name,
                'category'      => $product->category->name,
                'stock'         => $product->stock,
                'alert_stock'   => $product->alert_stock,
                'supplier'      => $supplierName,
            ];
        });

        $filters = array_merge($filters, [
            'category'      =>  $filters['category'] === 'semua-kategori' ? 'Semua Kategori' : Category::find($filters['category'])->name,
            'supplier'      => $filters['supplier'] === 'semua-supplier' ? 'Semua Supplier' : Supplier::find($filters['supplier'])->name
        ]);

        $pdf = Pdf::loadView('exports.stock-report.export-pdf', [
            "filters"           => $filters,
            "filteredStocks"    => $filteredStocks
        ]);

        $fileName = "Laporan Stock "
            . Carbon::parse($filters['start_date'])->translatedFormat('d F Y') . " - "
            . Carbon::parse($filters['end_date'])->translatedFormat('d F Y') . " StockEase.pdf";

        $filePath = "reports/stock/"
            . Carbon::now('Asia/Shanghai')->format('Y') . "/"
            . Carbon::now('Asia/Shanghai')->translatedFormat('F') . "/"
            . $fileName;

        Storage::put($filePath, $pdf->output());

        return $pdf->download($fileName);
    }

    /**
     * Export the stock report to an Excel file.
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'category'      => 'required',
            'supplier'      => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('reports.stock.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $query = Product::with(['category', 'purchaseItems.purcase.supplier'])
            ->whereHas('purchaseItems')
            ->when($filters['category'] && $filters['category'] !== 'semua-kategori', function ($query) use ($filters) {
                return $query->where('category_id', $filters['category']);
            })
            ->when($filters['supplier'] && $filters['supplier'] !== 'semua-supplier', function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase.supplier', function ($q) use ($filters) {
                    $q->where('id', $filters['supplier']);
                });
            })
            ->when($filters['start_date'], function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                    $q->whereDate('date', '>=', $filters['start_date']);
                });
            })
            ->when($filters['end_date'], function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purcase', function ($q) use ($filters) {
                    $q->whereDate('date', '<=', $filters['end_date']);
                });
            })
            ->get();

        $filteredStocks = $query->map(function ($product) {
            $firstPurchase = $product->purchaseItems->first();
            $supplierName = $firstPurchase ? $firstPurchase->purcase->supplier->name : '-';

            return (object) [
                'id'            => $product->id,
                'name'          => $product->name,
                'category'      => $product->category->name,
                'stock'         => $product->stock,
                'alert_stock'   => $product->alert_stock,
                'supplier'      => $supplierName,
            ];
        });

        $filters = array_merge($filters, [
            'category'      =>  $filters['category'] === 'semua-kategori' ? 'Semua Kategori' : Category::find($filters['category'])->name,
            'supplier'      => $filters['supplier'] === 'semua-supplier' ? 'Semua Supplier' : Supplier::find($filters['supplier'])->name
        ]);

        $fileName = "Laporan Stock "
            . Carbon::parse($filters['start_date'])->translatedFormat('d F Y') . " - "
            . Carbon::parse($filters['end_date'])->translatedFormat('d F Y') . " StockEase.xlsx";

        $filePath = "reports/stock/"
            . Carbon::now('Asia/Shanghai')->format('Y') . "/"
            . Carbon::now('Asia/Shanghai')->translatedFormat('F') . "/"
            . $fileName;

        Excel::store(new StockExportExcel($filters, $filteredStocks), $filePath, 'local');

        return Excel::download(new StockExportExcel($filters, $filteredStocks), $fileName);
    }
}
