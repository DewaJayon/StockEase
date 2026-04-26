<?php

namespace App\Actions\Sale;

use App\Models\Sale;

class RecalculateSaleTotal
{
    /**
     * Calculate the total of a sale by summing up all sale items prices
     * and updating the sale record.
     *
     * @return float The total of the sale
     */
    public function execute(Sale $sale): float
    {
        $total = 0;
        $totalCost = 0;

        if (! $sale->relationLoaded('saleItems')) {
            $sale->load('saleItems.product');
        }

        foreach ($sale->saleItems as $item) {
            $total += $item->price * $item->qty;
            $totalCost += ($item->cost_price ?? 0) * $item->qty;
        }

        $sale->update([
            'total' => $total,
            'total_cost' => $totalCost,
        ]);

        return (float) $total;
    }
}
