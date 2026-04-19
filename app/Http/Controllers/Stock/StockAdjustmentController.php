<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Stock\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StockAdjustmentService $stockAdjustmentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $adjustments = $this->stockAdjustmentService->getPaginatedAdjustments([
            'search' => $request->search,
        ]);

        return Inertia::render('StockAdjustment/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $this->stockAdjustmentService->storeAdjustment($validated);

        return redirect()->route('stock-adjustment.index')
            ->with('success', 'Berhasil melakukan penyesuaian stok.');
    }

    /**
     * Search products for selection.
     */
    public function searchProduct(Request $request)
    {
        if ($request->expectsJson()) {
            $search = $request->search;
            $products = Product::where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->select('id as value', 'name as label', 'stock')
                ->take(10)
                ->get();

            return response()->json($products);
        }
    }
}
