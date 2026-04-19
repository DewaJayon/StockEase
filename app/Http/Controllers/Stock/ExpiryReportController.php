<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpiryReportController extends Controller
{
    /**
     * Display a listing of products with expiry dates.
     */
    public function index(Request $request): Response
    {
        $expiryData = PurchaseItem::with(['product', 'purchase.supplier'])
            ->whereNotNull('expiry_date')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'expired') {
                    $query->where('expiry_date', '<', now()->toDateString());
                } elseif ($status === 'near_expired') {
                    $query->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                }
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Reports/Expiry/Index', [
            'expiryData' => $expiryData,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
