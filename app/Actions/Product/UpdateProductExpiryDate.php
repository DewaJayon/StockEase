<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\PurchaseItem;

class UpdateProductExpiryDate
{
    /**
     * Update the expiry date based on the earliest expiring available stock.
     */
    public function execute(Product $product): void
    {
        $earliestBatch = PurchaseItem::where('product_id', $product->id)
            ->where('remaining_qty', '>', 0)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->first();

        $product->update([
            'expiry_date' => $earliestBatch ? $earliestBatch->expiry_date : null,
        ]);
    }
}
