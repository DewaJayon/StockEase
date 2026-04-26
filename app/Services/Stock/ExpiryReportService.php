<?php

namespace App\Services\Stock;

use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpiryReportService
{
    /**
     * Get paginated filtered expiry items.
     */
    public function getPaginatedExpiryItems(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return PurchaseItem::with(['product', 'purchase.supplier'])
            ->whereNotNull('expiry_date')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'expired') {
                    $query->where('expiry_date', '<', now()->toDateString());
                } elseif ($status === 'near_expired') {
                    $query->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
                }
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
