<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        // dump($filteredStocks->toArray());

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
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('id', 'like', '%' . $request->search . '%')
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
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('id', 'like', '%' . $request->search . '%')
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
}
