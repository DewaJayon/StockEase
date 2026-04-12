<?php

namespace App\Services\Sale;

use App\Models\Category;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosService
{
    /**
     * Get paginated products with category filter and search.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedProducts(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['category'] ?? null, function ($query, $categoryFilter) {
                $query->whereHas('category', function ($queryCategory) use ($categoryFilter) {
                    $queryCategory->where('slug', $categoryFilter);
                });
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('barcode', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get categories for POS filtering.
     */
    public function getCategories()
    {
        return Category::select('slug', 'name')->get()
            ->map(fn ($category) => [
                'value' => $category->slug,
                'label' => $category->name,
            ]);
    }

    /**
     * Get the current active cart (draft sale) for the authenticated user.
     */
    public function getOrCreateCart(): Sale
    {
        $cart = Sale::with('saleItems.product')
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->first();

        if (! $cart) {
            $cart = Sale::create([
                'user_id' => Auth::id(),
                'total' => 0,
                'payment_method' => 'pending',
                'paid' => 0,
                'change' => 0,
                'date' => now(),
                'status' => 'draft',
            ]);
            $cart->setRelation('saleItems', collect());
        }

        return $cart;
    }

    /**
     * Add or update product in cart.
     */
    public function addToCart(int $productId, int $qty = 1): array
    {
        $product = Product::findOrFail($productId);
        $cart = $this->getOrCreateCart();

        if ($product->stock <= 0) {
            throw new \Exception('Stok produk habis');
        }

        if ($product->stock < $qty) {
            throw new \Exception('Stok produk tidak mencukupi');
        }

        $existItem = $cart->saleItems->firstWhere('product_id', $product->id);

        if (! $existItem) {
            $newItem = SaleItem::create([
                'sale_id' => $cart->id,
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => $product->selling_price,
            ]);
            $newItem->setRelation('product', $product);
            $cart->saleItems->push($newItem);
        } else {
            $existItem->update([
                'qty' => $existItem->qty + $qty,
            ]);
        }

        $cart->calculateTotal();

        return ['cart' => $cart, 'total' => $cart->total];
    }

    /**
     * Update item quantity in cart.
     */
    public function updateCartItemQty(int $productId, int $qty): array
    {
        $cart = $this->getOrCreateCart();
        $product = Product::findOrFail($productId);

        if ($qty > 0 && $qty > $product->stock) {
            throw new \Exception('Stok produk tidak mencukupi');
        }

        if ($qty <= 0) {
            $cart->saleItems()->where('product_id', $productId)->delete();
            $cart->setRelation('saleItems', $cart->saleItems->reject(fn ($item) => $item->product_id === $productId)->values());
        } else {
            $saleItem = $cart->saleItems->firstWhere('product_id', $productId);
            if ($saleItem) {
                $saleItem->update(['qty' => $qty]);
            }
        }

        $cart->calculateTotal();

        return ['cart' => $cart, 'total' => $cart->total];
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(int $productId): array
    {
        $cart = $this->getOrCreateCart();
        $cart->saleItems()->where('product_id', $productId)->delete();
        $cart->setRelation('saleItems', $cart->saleItems->reject(fn ($item) => $item->product_id === $productId)->values());

        $cart->calculateTotal();

        return ['cart' => $cart, 'total' => $cart->total];
    }

    /**
     * Empty the cart.
     */
    public function emptyCart(): array
    {
        $cart = $this->getOrCreateCart();
        $cart->saleItems()->delete();
        $cart->setRelation('saleItems', collect());

        $cart->calculateTotal();

        return ['cart' => $cart, 'total' => $cart->total];
    }

    /**
     * Process checkout.
     */
    public function checkout(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::with('saleItems.product')
                ->where('user_id', Auth::id())
                ->where('status', 'draft')
                ->firstOrFail();

            if ($sale->saleItems->isEmpty()) {
                throw new \Exception('Keranjang kosong, tidak bisa checkout');
            }

            foreach ($sale->saleItems as $item) {
                if ($item->product->stock < $item->qty) {
                    throw new \Exception("Stok produk {$item->product->name} tidak mencukupi untuk checkout.");
                }
            }

            if ($data['payment_method'] === 'qris') {
                $sale->update([
                    'payment_method' => 'qris',
                    'customer_name' => $data['customer_name'] ?? null,
                    'paid' => $sale->total, // QRIS total matches sale total
                    'status' => 'pending',   // QRIS stays pending until webhook
                ]);

                PaymentTransaction::create([
                    'sale_id' => $sale->id,
                    'gateway' => 'midtrans',
                    'external_id' => $data['order_id'] ?? null,
                    'status' => 'pending',
                    'amount' => $sale->total,
                    'payment_type' => 'qris',
                ]);
            } elseif ($data['payment_method'] === 'cash') {
                if ($data['paid'] < $sale->total) {
                    throw new \Exception('Jumlah uang pembayaran kurang dari total belanja.');
                }

                $change = $data['paid'] - $sale->total;

                $sale->update([
                    'payment_method' => 'cash',
                    'customer_name' => $data['customer_name'] ?? null,
                    'paid' => $data['paid'],
                    'change' => $change,
                    'status' => 'completed',
                ]);

                Product::reduceStockFromSaleItems($sale->saleItems);
            }

            // After checkout, we get/create a new empty cart for the next transaction
            $newCart = $this->getOrCreateCart();

            return ['cart' => $newCart, 'total' => $newCart->total, 'sale' => $sale];
        });
    }
}
