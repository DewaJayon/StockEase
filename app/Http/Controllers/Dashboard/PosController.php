<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::select('slug', 'name')->get();

        $categories = $categories->map(function ($category) {
            return [
                'value' => $category->slug,
                'label' => $category->name,
            ];
        });

        $categoryFilter = $request->category;

        $products = Product::query()
            ->when($categoryFilter, function ($query, $categoryFilter) {
                $query->whereHas('category', function ($queryCategory) use ($categoryFilter) {
                    $queryCategory->where('slug', $categoryFilter);
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Pos/Index', [
            'products'      => $products,
            'categories'    => $categories,
            'cart'          => $this->getCart()
        ]);
    }


    /**
     * Return the cart data in JSON format.
     * 
     * Only returns the cart data if the request is an AJAX request.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartJson()
    {
        if (request()->expectsJson()) {
            $cart = $this->getCart();
            return response()->json(['cart' => $cart]);
        }
        return abort(403, 'Invalid request.');
    }


    /**
     * Return the current cart data.
     * 
     * If the current cart doesn't exist, create a new one.
     * 
     * @return \App\Models\Sale
     */
    private function getCart()
    {
        $cart = Sale::with('saleItems.product')
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->first();

        if (!$cart) {
            $cart = Sale::create([
                'user_id'         => Auth::id(),
                'total'           => 0,
                'payment_method'  => 'pending',
                'paid'            => 0,
                'change'          => 0,
                'date'            => now(),
                'status'          => 'draft',
            ]);
        } else {
            $this->refreshCart($cart);
        }

        return $cart;
    }


    /**
     * Refresh the cart data by reloading the sale items and recalculating the total.
     *
     * @param \App\Models\Sale $cart The cart object to refresh.
     * @return void
     */

    private function refreshCart($cart)
    {
        $cart->load('saleItems.product');
        $cart->calculateTotal();
    }


    /**
     * Update the qty of a product in the current cart.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'qty'           => 'required|numeric'
        ]);

        $cart = $this->getCart();

        if ($request->qty <= 0) {
            $cart->saleItems()->where('product_id', $request->product_id)->delete();

            $this->refreshCart($cart);

            return response()->json([
                'message' => 'Item berhasil dihapus',
                'total'   => $cart->total,
                'cart'    => $cart
            ]);
        }

        $saleItem = $cart->saleItems()->where('product_id', $request->product_id)->first();

        if (!$saleItem) {
            return response()->json(['message' => 'Item tidak ditemukan di cart.'], 404);
        }

        DB::transaction(function () use ($request, $saleItem) {
            $saleItem->update([
                'qty' => $request->qty
            ]);
        });

        $this->refreshCart($cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Qty berhasil diubah',
                'total'   => $cart->total,
            ]);
        }

        return back();
    }


    /**
     * Tambahkan item produk ke keranjang.
     * 
     * Validasi:
     * - product_id harus ada di tabel products
     * - Jika qty yang diinputkan lebih besar dari stok produk, maka akan gagal dan mengembalikan response 400
     * - Jika stok produk habis, maka akan gagal dan mengembalikan response 400
     * 
     * Jika berhasil, maka akan mengembalikan response 200 dengan data keranjang yang sudah di update.
     * Jika gagal, maka akan mengembalikan response 400 dengan pesan error.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request)
    {

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::where('id', $request->product_id)->first();
        $cart    = $this->getCart();
        $qty     = 1;

        if ($product->stock == 0) {
            return response()->json([
                'message' => 'Stok produk habis'
            ], 400);
        }

        if ($product->stock < $qty) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi'
            ], 400);
        }

        $existItem = SaleItem::where([
            'sale_id'       => $cart->id,
            'product_id'    => $product->id
        ])->first();

        if (!$existItem) {
            SaleItem::create([
                'sale_id'       => $cart->id,
                'product_id'    => $product->id,
                'qty'           => $qty,
                'price'         => $product->selling_price
            ]);
        } else {
            $existItem->update([
                'qty' => $existItem->qty + $qty
            ]);
        }

        $this->refreshCart($cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'total' => $cart->total
            ]);
        }

        return back();
    }


    /**
     * Hapus item produk dari keranjang.
     * 
     * Validasi:
     * - product_id harus ada di tabel products
     * 
     * Jika berhasil, maka akan mengembalikan response 200 dengan data keranjang yang sudah di update.
     * Jika gagal, maka akan mengembalikan response 400 dengan pesan error.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function removeFromCart(Request $request)
    {
        if ($request->expectsJson()) {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $cart = $this->getCart();
            $cart->saleItems()->where('product_id', $request->product_id)->delete();

            $this->refreshCart($cart);

            return response()->json([
                'message'    => 'Item berhasil dihapus',
                'total'      => $cart->total,
                'cart'       => $cart
            ]);
        }

        return back();
    }


    /**
     * Kosongkan keranjang.
     * 
     * Jika keranjang sudah kosong, maka akan mengembalikan response 400 dengan pesan error.
     * Jika berhasil, maka akan mengembalikan response 200 dengan data keranjang yang sudah dikosongkan.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function emptyCart()
    {
        $cart = $this->getCart();

        if ($cart->saleItems()->count() == 0) {
            return response()->json([
                'message' => 'Keranjang masih kosong'
            ], 400);
        }

        if (request()->expectsJson()) {

            $cart->saleItems()->delete();
            $this->refreshCart($cart);

            return response()->json([
                'message' => 'Keranjang berhasil dikosongkan',
                'total'   => $cart->total,
                'cart'    => $cart
            ], 200);
        }
    }


    /**
     * Checkout sale.
     *
     * Validation rules:
     * - payment_method harus ada di tabel payments
     * - customer_name boleh kosong, tapi tidak boleh lebih dari 255 karakter
     * - paid harus berupa angka
     * - change harus berupa angka
     *
     * Jika sale item kosong, maka akan mengembalikan response 400 dengan pesan error.
     * Jika berhasil, maka akan mengembalikan response 200 dengan data keranjang yang sudah di update.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Sale $sale
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request)
    {

        $sale = Sale::with('saleItems.product')
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->firstOrFail();

        if ($sale->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to cart.');
        }

        if ($request->expectsJson()) {

            $request->validate([
                'payment_method' => 'required|in:cash,qris',
                'customer_name'  => 'nullable|string|max:255',
                'paid'           => 'required_if:payment_method,cash|numeric',
                'change'         => 'required_if:payment_method,cash|numeric',
            ]);

            if ($sale->saleItems->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong, tidak bisa checkout'], 400);
            }

            foreach ($sale->saleItems as $item) {
                if ($item->product->stock < $item->qty) {
                    return response()->json([
                        'message' => "Stok produk {$item->product->name} tidak mencukupi untuk checkout."
                    ], 400);
                }
            }

            DB::transaction(function () use ($request, $sale) {

                if ($request->payment_method === 'qris') {
                    $sale->update([
                        'payment_method' => 'qris',
                        'customer_name'  => $request->customer_name,
                        'paid'           => $request->paid,
                        'status'         => 'completed'
                    ]);

                    PaymentTransaction::create([
                        'sale_id'       => $sale->id,
                        'gateway'       => 'midtrans',
                        'external_id'   => $request->order_id,
                        'status'        => 'pending',
                        'amount'        => $request->paid,
                        'payment_type'  => 'qris',
                    ]);
                } else if ($request->payment_method === 'cash') {
                    $sale->update([
                        'payment_method' => $request->payment_method,
                        'customer_name'  => $request->customer_name,
                        'paid'           => $request->paid,
                        'change'         => $request->change,
                        'status'         => 'completed'
                    ]);
                }

                Product::reduceStockFromSaleItems($sale->saleItems);
            });

            $cart = $this->getCart();
            $this->refreshCart($cart);

            return response()->json([
                'message' => 'Checkout berhasil',
                'total'   => $cart->total,
                'cart'    => $cart
            ]);
        }

        return back();
    }
}
