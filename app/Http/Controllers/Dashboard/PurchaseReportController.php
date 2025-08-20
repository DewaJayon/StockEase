<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filters

        return Inertia::render('Reports/Purchase/Index');
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
