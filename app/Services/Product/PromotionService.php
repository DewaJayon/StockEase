<?php

namespace App\Services\Product;

use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionService
{
    /**
     * Get paginated promotions with searching and date filtering.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedPromotions(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Promotion::with(['category:id,name', 'product:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($queryProduct) use ($search) {
                            $queryProduct->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('category', function ($queryCategory) use ($search) {
                            $queryCategory->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['start'] ?? null, function ($query, $start) {
                $query->where('start_date', '>=', Carbon::parse($start)->startOfDay());
            })
            ->when($filters['end'] ?? null, function ($query, $end) {
                $query->where('end_date', '<=', Carbon::parse($end)->endOfDay());
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
