<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
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

        // Send notifications to admin users
        $admins = User::where('role', 'admin')->get();
        $alertProducts->each(function (Product $product) use ($admins) {
            $admins->each(fn (User $admin) => $admin->notify(new StockAlertNotification($product)));
        });

        return response()->json($alertProducts);
    }
}
