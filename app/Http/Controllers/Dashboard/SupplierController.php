<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupplierController extends Controller
{

    /**
     * Constructor for CategoryController.
     *
     * Checks if the user is logged in and has an admin role.
     * If not, aborts with a 403 status code.
     */
    public function __construct()
    {
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->role !== 'warehouse') {
            abort(403);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $suppliers = Supplier::query()
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers
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
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|regex:/^[0-9]+$/|max:20',
            'address'   => 'required|string',
        ]);

        $slug = SlugService::createSlug(Supplier::class, 'slug', $request->name);

        Supplier::create([
            'slug'      => $slug,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'address'   => $request->address
        ]);

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
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|regex:/^[0-9]+$/|max:20',
            'address'   => 'required|string',
        ]);

        if ($supplier->name !== $validated['name']) {
            $slug = SlugService::createSlug(Supplier::class, 'slug', $validated['name']);
        }

        $supplier->update([
            'slug'      => $slug ?? $supplier->slug,
            'name'      => $validated['name'],
            'phone'     => $validated['phone'],
            'address'   => $validated['address']
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Supplier gagal dihapus');
        }

        return redirect()->back()->with('success', 'Supplier berhasil dihapus');
    }
}
