<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Purcase;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}
