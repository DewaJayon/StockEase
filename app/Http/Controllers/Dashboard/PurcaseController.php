<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Purcase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurcaseController extends Controller
{

    /**
     * Display a listing of the purchases.
     *
     * Retrieves a paginated list of purchases, including related supplier, user,
     * and purchase item details. Supports searching across supplier, user, and
     * product fields such as name, address, phone, SKU, and barcode.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $purchases = Purcase::with('supplier', 'user', 'purcaseItems', 'purcaseItems.product')
            ->when($request->search, function ($query, $search) {

                $query->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('purcaseItems', function ($q) use ($search) {
                    $q->whereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Purcase/Index', [
            'purcases' => $purchases
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
