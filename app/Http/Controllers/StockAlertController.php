<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{

    /**
     * Get all product stock alerts.
     * 
     * @return \Illuminate\Http\Response
     * 
     */
    public function index()
    {
        if (request()->expectsJson()) {
            $alertStock = Product::whereColumn('stock', '<=', 'alert_stock')->get();

            return response()->json($alertStock);
        }

        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }
}
