<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\PosBarcodeCartItemRequest;
use App\Http\Requests\Sale\PosCartItemRequest;
use App\Http\Requests\Sale\PosChangeQtyRequest;
use App\Http\Requests\Sale\PosCheckoutRequest;
use App\Models\Promotion;
use App\Services\Sale\PosService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PosService $posService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = $this->posService->getCategories();

        $products = $this->posService->getPaginatedProducts(
            $request->only(['category', 'search']),
            12
        );

        $promotions = Promotion::active()->get();

        return Inertia::render('Pos/Index', [
            'products' => $products,
            'categories' => $categories,
            'cart' => $this->posService->getOrCreateCart(),
            'active_promotions' => $promotions,
        ]);
    }

    /**
     * Return the cart data in JSON format.
     */
    public function getCartJson()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'cart' => $this->posService->getOrCreateCart(),
            ]);
        }

        return abort(403, 'Invalid request.');
    }

    /**
     * Update the qty of a product in the current cart.
     */
    public function changeQty(PosChangeQtyRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->posService->updateCartItemQty($validated['product_id'], (int) $validated['qty']);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Qty berhasil diubah',
                    'total' => $result['total'],
                    'cart' => $result['cart'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return back();
    }

    /**
     * Tambahkan item produk ke keranjang.
     */
    public function addToCart(PosCartItemRequest $request)
    {
        try {
            $validated = $request->validated();
            $qty = (int) ($validated['qty'] ?? 1);
            $result = $this->posService->addToCart((int) $validated['product_id'], $qty);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Item berhasil ditambahkan ke keranjang',
                    'total' => $result['total'],
                    'cart' => $result['cart'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return back();
    }

    /**
     * Tambahkan item produk ke keranjang via barcode.
     */
    public function addToCartByBarcode(PosBarcodeCartItemRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->posService->addToCartByBarcode($validated['barcode'], (int) ($validated['qty'] ?? 1));

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Item berhasil ditambahkan ke keranjang',
                    'total' => $result['total'],
                    'cart' => $result['cart'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return back();
    }

    /**
     * Hapus item produk dari keranjang.
     */
    public function removeFromCart(PosCartItemRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->posService->removeFromCart((int) $validated['product_id']);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Item berhasil dihapus',
                    'total' => $result['total'],
                    'cart' => $result['cart'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return back();
    }

    /**
     * Kosongkan keranjang.
     */
    public function emptyCart()
    {
        try {
            $result = $this->posService->emptyCart();

            return response()->json([
                'message' => 'Keranjang berhasil dikosongkan',
                'total' => $result['total'],
                'cart' => $result['cart'],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Checkout sale.
     */
    public function checkout(PosCheckoutRequest $request)
    {
        try {
            $data = $request->validated();

            // Add order_id if present for Midtrans
            if ($request->has('order_id')) {
                $data['order_id'] = $request->input('order_id');
            }

            $result = $this->posService->checkout($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Checkout berhasil',
                    'total' => $result['total'],
                    'cart' => $result['cart'],
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return back();
    }
}
