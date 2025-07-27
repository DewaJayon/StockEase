<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurcaseRequest;
use App\Models\Product;
use App\Models\Purcase;
use App\Models\PurcaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Searches for suppliers based on the given search query.
     *
     * When the request expects JSON, this controller will return a JSON response
     * with the results of the search. If the search query is empty, it will
     * return a 200 response with an empty list of suppliers. If no suppliers
     * are found, it will return a 404 response with a null data value.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function searchSupplier(Request $request)
    {
        if ($request->expectsJson()) {

            if (blank($request->search)) {
                return response()->json([
                    "message" => "empty search",
                    "data" => []
                ], 200);
            }

            $supplier = Supplier::where("name", "like", "%{$request->search}%")
                ->select("id as value", "name as label")
                ->get();

            if ($supplier->isEmpty()) {
                return response()->json([
                    "message" => "supplier not found",
                    "data"   => null
                ], 404);
            }

            return response()->json([
                "message" => "success search supplier",
                "data" => $supplier
            ], 200);
        }

        return back();
    }

    /**
     * Search products by name.
     *
     * When the request expects JSON, this controller will return a JSON response
     * with the results of the search. If the search query is empty, it will
     * return a 200 response with an empty list of products. If no products
     * are found, it will return a 200 response with an empty list of products.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function searchProduct(Request $request)
    {
        if ($request->expectsJson()) {
            if (blank($request->search)) {
                return response()->json([
                    "message" => "empty search",
                    "data" => []
                ], 200);
            }

            $products = Product::where("name", "like", "%{$request->search}%")
                ->select("id", "name as label", "purchase_price", "selling_price", "unit")
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    "message" => "product not found",
                    "data" => []
                ], 200);
            }

            return response()->json([
                "message" => "success search product",
                "data" => $products
            ], 200);
        }

        return back();
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
     *
     * @param  \App\Http\Requests\StorePurcaseRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(StorePurcaseRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {

            $purchasesParams = [
                "supplier_id"   => $data['supplier_id'],
                "user_id"       => Auth::user()->id,
                "total"         => 0,
                "date"          => $data['date'],
            ];

            $purchases = Purcase::create($purchasesParams);

            $totalPurcase = 0;

            $products = Product::whereIn('id', collect($data['product_items'])->pluck('product_id'))->get()->keyBy('id');

            foreach ($data['product_items'] as $item) {

                $subtotal = $item['qty'] * $item['price'];
                $totalPurcase += $subtotal;

                if ($item['qty'] <= 0 || $item['price'] <= 0) {
                    throw new \Exception("Qty atau harga tidak boleh 0");
                }

                PurcaseItem::create([
                    "purcase_id"    => $purchases->id,
                    "product_id"    => $item['product_id'],
                    "qty"           => $item['qty'],
                    "price"         => $item['price'],
                ]);

                $product = $products[$item['product_id']];

                $product->update([
                    "stock" => $product->stock + $item['qty']
                ]);

                if ($product->purchase_price != $item['price'] || $product->selling_price != $item['selling_price']) {

                    $product->update([
                        "purchase_price" => $item['price'],
                        "selling_price"  => $item['selling_price']
                    ]);
                }

                StockLog::create([
                    'product_id'     => $product->id,
                    'qty'            => $item['qty'],
                    'type'           => 'in',
                    'reference_type' => 'Purcase',
                    'reference_id'   => $purchases->id,
                    'note'           => 'Pembelian produk ' . $product->name,
                ]);
            }

            $purchases->update([
                "total" => $totalPurcase
            ]);

            DB::commit();

            return back()->with('success', 'Pembelian berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal menyimpan data: ' . $th->getMessage(),
            ]);
        }
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
