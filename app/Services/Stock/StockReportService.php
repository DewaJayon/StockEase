<?php

namespace App\Services\Stock;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockReportService
{
    /**
     * Get paginated filtered stocks based on provided criteria.
     */
    public function getPaginatedFilteredStocks(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Product::with(['category', 'purchaseItems.purchase.supplier'])
            ->whereHas('purchaseItems')
            ->when(($filters['category'] ?? 'semua-kategori') !== 'semua-kategori', function ($query) use ($filters) {
                return $query->where('category_id', $filters['category']);
            })
            ->when(($filters['supplier'] ?? 'semua-supplier') !== 'semua-supplier', function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purchase.supplier', function ($q) use ($filters) {
                    $q->where('id', $filters['supplier']);
                });
            })
            ->when($filters['start_date'] ?? null, function ($query, $start) {
                return $query->whereHas('purchaseItems.purchase', function ($q) use ($start) {
                    $q->whereDate('date', '>=', $start);
                });
            })
            ->when($filters['end_date'] ?? null, function ($query, $end) {
                return $query->whereHas('purchaseItems.purchase', function ($q) use ($end) {
                    $q->whereDate('date', '<=', $end);
                });
            })
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($product) {
                $firstPurchase = $product->purchaseItems->first();
                $supplierName = $firstPurchase ? $firstPurchase->purchase->supplier->name : '-';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'stock' => $product->stock,
                    'alert_stock' => $product->alert_stock,
                    'supplier' => $supplierName,
                ];
            });
    }

    /**
     * Get all filtered stocks for export.
     */
    public function getFilteredStocksForExport(array $filters): Collection
    {
        return Product::with(['category', 'purchaseItems.purchase.supplier'])
            ->whereHas('purchaseItems')
            ->when(($filters['category'] ?? 'semua-kategori') !== 'semua-kategori', function ($query) use ($filters) {
                return $query->where('category_id', $filters['category']);
            })
            ->when(($filters['supplier'] ?? 'semua-supplier') !== 'semua-supplier', function ($query) use ($filters) {
                return $query->whereHas('purchaseItems.purchase.supplier', function ($q) use ($filters) {
                    $q->where('id', $filters['supplier']);
                });
            })
            ->when($filters['start_date'] ?? null, function ($query, $start) {
                return $query->whereHas('purchaseItems.purchase', function ($q) use ($start) {
                    $q->whereDate('date', '>=', $start);
                });
            })
            ->when($filters['end_date'] ?? null, function ($query, $end) {
                return $query->whereHas('purchaseItems.purchase', function ($q) use ($end) {
                    $q->whereDate('date', '<=', $end);
                });
            })
            ->get()
            ->map(function ($product) {
                $firstPurchase = $product->purchaseItems->first();
                $supplierName = $firstPurchase ? $firstPurchase->purchase->supplier->name : '-';

                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'stock' => $product->stock,
                    'alert_stock' => $product->alert_stock,
                    'supplier' => $supplierName,
                ];
            });
    }

    /**
     * Prepare filters for export views.
     */
    public function prepareExportFilters(array $filters): array
    {
        return array_merge($filters, [
            'category' => ($filters['category'] ?? 'semua-kategori') === 'semua-kategori'
                ? 'Semua Kategori'
                : Category::find($filters['category'])?->name ?? 'Semua Kategori',
            'supplier' => ($filters['supplier'] ?? 'semua-supplier') === 'semua-supplier'
                ? 'Semua Supplier'
                : Supplier::find($filters['supplier'])?->name ?? 'Semua Supplier',
        ]);
    }
}
