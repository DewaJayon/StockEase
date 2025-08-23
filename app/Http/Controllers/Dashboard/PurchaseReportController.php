<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Purcase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseReportController extends Controller
{
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

            // TODO: summary purchases

            $sumTotalPurchase = $query->sum('total');

            $filteredPurchases = [
                'filters'               => $query->toArray(),
                'sumTotalPurchase'      => $sumTotalPurchase
            ];
        }

        // dump($filters, $filteredPurchases);

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
