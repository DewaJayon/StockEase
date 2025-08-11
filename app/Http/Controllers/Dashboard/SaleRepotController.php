<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleRepotController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $cashier = $request->input('cashier');
        $payment = $request->input('payment');

        // dd($startDate, $endDate, $cashier, $payment);

        // $saleReports = Sale::with('user', 'saleItems', 'saleItems.product', 'paymentTransaction')

        return Inertia::render('Reports/Sale/Index');
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
}
