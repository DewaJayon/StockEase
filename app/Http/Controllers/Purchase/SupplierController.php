<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StoreSupplierRequest;
use App\Http\Requests\Purchase\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\Purchase\SupplierService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $suppliers = $this->supplierService->getPaginatedSuppliers(
            $request->only('search'),
            $perPage
        );

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $this->supplierService->storeSupplier($request->validated());

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->supplierService->updateSupplier($supplier, $request->validated());

        return redirect()->back()->with('success', 'Supplier berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->deleteSupplier($supplier);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Supplier gagal dihapus');
        }

        return redirect()->back()->with('success', 'Supplier berhasil dihapus');
    }
}
