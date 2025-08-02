<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurcaseRequest;
use App\Http\Requests\UpdatePurcaseRequest;
use App\Models\Product;
use App\Models\Purcase;
use App\Models\PurcaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use Carbon\Carbon;
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

        $startDate = $request->input('start');
        $endDate = $request->input('end');

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
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            })
            ->orderBy('date', 'desc')
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

                if ($item['qty'] <= 0 || $item['price'] <= 0) {
                    throw new \Exception("Qty atau harga tidak boleh 0");
                }

                $subtotal = $item['qty'] * $item['price'];
                $totalPurcase += $subtotal;

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
     *
     * Update the specified purcase including purcase items and stock products.
     *
     * @param  \App\Http\Requests\UpdatePurcaseRequest  $request
     * @param  \App\Models\Purcase  $purcase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurcaseRequest $request, Purcase $purcase)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {

            $purcaseParams = [
                "supplier_id"   => $data['supplier_id'],
                "user_id"       => Auth::user()->id,
                "total"         => $purcase->total,
                "date"          => $data['date'],
            ];

            $purcase->update($purcaseParams);

            $totalPurcase = 0;

            $products = Product::whereIn('id', collect($data['product_items'])->pluck('product_id'))->get()->keyBy('id');

            $deletedPurcaseItems = PurcaseItem::where('purcase_id', $purcase->id)
                ->whereNotIn('product_id', collect($data['product_items'])->pluck('product_id'))
                ->get();

            if ($deletedPurcaseItems->count() > 0) {
                foreach ($deletedPurcaseItems as $deletedItem) {
                    $product = $products[$deletedItem->product_id] ?? Product::find($deletedItem->product_id);
                    $product->decrement('stock', $deletedItem->qty);
                    $deletedItem->delete();
                }
            }

            foreach ($data['product_items'] as $item) {

                if ($item['qty'] <= 0 || $item['price'] <= 0) {
                    throw new \Exception("Qty atau harga tidak boleh 0");
                }

                $subtotal = $item['qty'] * $item['price'];
                $totalPurcase += $subtotal;

                $product = $products[$item['product_id']];

                $oldPurcaseItems = PurcaseItem::where('purcase_id', $purcase->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($oldPurcaseItems) {
                    $oldQty = $oldPurcaseItems->qty;
                    $newQty = $item['qty'];

                    $diffQty = $newQty - $oldQty;

                    $oldPurcaseItems->update([
                        "qty"   => $newQty,
                        "price" => $item['price'],
                    ]);

                    $product->stock += $diffQty;
                } else {
                    PurcaseItem::create([
                        'purcase_id' => $purcase->id,
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'price'      => $item['price'],
                    ]);

                    $product->stock += $item['qty'];
                }

                $product->save();

                if ($product->purchase_price != $item['price'] || $product->selling_price != $item['selling_price']) {
                    $product->update([
                        "purchase_price" => $item['price'],
                        "selling_price"  => $item['selling_price']
                    ]);
                }

                StockLog::create([
                    'product_id'     => $product->id,
                    'qty'            => $oldPurcaseItems ? $diffQty : $item['qty'],
                    'type'           => 'adjust',
                    'reference_type' => 'Purcase',
                    'reference_id'   => $purcase->id,
                    'note'           => 'Perubahan pembelian produk ' . $product->name,
                ]);
            }

            $purcase->update([
                "total" => $totalPurcase
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Gagal menyimpan data: ' . $th->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
