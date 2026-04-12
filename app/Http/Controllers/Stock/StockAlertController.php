<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class StockAlertController extends Controller
{
    /**
     * Get all product stock alerts.
     */
    public function index(): JsonResponse
    {
        if (! request()->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $alertProducts = Product::whereColumn('stock', '<=', 'alert_stock')->get();

        return response()->json($alertProducts);
    }
}
