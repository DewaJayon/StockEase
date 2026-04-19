<?php

namespace App\Actions\Product;

use App\Actions\NotifyStockAlert;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use Illuminate\Support\Collection;

class ReduceProductStock
{
    /**
     * Reduce product stock from sale items.
     *
     * This function reduces the stock of each product in the given sale items
     * by the quantity sold. It also updates the product expiry date based on the
     * earliest expiring available stock. If the stock falls below the alert level,
     * it sends a notification to the admin.
     */
    public function execute(Collection $saleItems): void
    {
        $productIds = $saleItems->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

        foreach ($saleItems as $item) {
            /** @var Product $product */
            $product = $products[$item->product_id];

            if ($product->stock < $item->qty) {
                throw new \Exception("Stok produk {$product->name} tidak cukup.");
            }

            // FEFO Logic: Deduct stock from the earliest expiring purchase items
            $qtyToReduce = $item->qty;
            $purchaseItems = PurchaseItem::where('product_id', $product->id)
                ->where('remaining_qty', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
                ->lockForUpdate()
                ->get();

            foreach ($purchaseItems as $purchaseItem) {
                if ($qtyToReduce <= 0) {
                    break;
                }

                $reduce = min($purchaseItem->remaining_qty, $qtyToReduce);
                $purchaseItem->decrement('remaining_qty', $reduce);
                $qtyToReduce -= $reduce;
            }

            $product->decrement('stock', $item->qty);
            (new UpdateProductExpiryDate)->execute($product);
            $product->refresh(); // Refresh to get the actual stock value after decrement

            if ($product->stock <= $product->alert_stock) {
                (new NotifyStockAlert)->execute($product);
            }

            StockLog::create([
                'product_id' => $product->id,
                'qty' => $item->qty,
                'type' => 'out',
                'reference_type' => 'Sale',
                'reference_id' => $item->sale_id,
                'note' => 'Penjualan produk '.$product->name,
            ]);
        }
    }
}
