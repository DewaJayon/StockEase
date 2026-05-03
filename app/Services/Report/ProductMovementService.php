<?php

namespace App\Services\Report;

use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductMovementService
{
    /**
     * Get fast-moving products ranked by total quantity sold.
     *
     * @return Collection<int, array{product_id: int, product_name: string, sku: string, total_qty_sold: string, total_revenue: string, current_stock: int}>
     */
    public function getFastMovingProducts(string $startDate, string $endDate, int $limit = 10): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.stock as current_stock',
                DB::raw('SUM(sale_items.qty) as total_qty_sold'),
                DB::raw('SUM(sale_items.qty * sale_items.price) as total_revenue'),
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.stock')
            ->orderBy('total_qty_sold', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'total_qty_sold' => (int) $item->total_qty_sold,
                'total_revenue' => (float) $item->total_revenue,
                'current_stock' => (int) $item->current_stock,
            ]);
    }

    /**
     * Get slow-moving products: products with stock > 0 and the least sales (or none) in the period.
     *
     * @return Collection<int, array{product_id: int, product_name: string, sku: string, total_qty_sold: int, current_stock: int, last_sold_at: string|null}>
     */
    public function getSlowMovingProducts(string $startDate, string $endDate, int $limit = 10): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return Product::query()
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.stock as current_stock',
                DB::raw('COALESCE(SUM(CASE WHEN sales.status = \'completed\'
                    AND sales.date BETWEEN \''.$start->toDateString().'\' AND \''.$end->toDateString().'\'
                    THEN sale_items.qty ELSE 0 END), 0) as total_qty_sold'),
                DB::raw('MAX(CASE WHEN sales.status = \'completed\' THEN sales.date ELSE NULL END) as last_sold_at'),
            )
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('products.stock', '>', 0)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.stock')
            ->orderBy('total_qty_sold', 'asc')
            ->orderBy('products.stock', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'total_qty_sold' => (int) $item->total_qty_sold,
                'current_stock' => (int) $item->current_stock,
                'last_sold_at' => $item->last_sold_at,
            ]);
    }

    /**
     * Build chart data from already-fetched fast/slow moving collections.
     * Accepts the results of getFastMovingProducts() and getSlowMovingProducts()
     * to avoid re-running the same queries a second time.
     *
     * @param  Collection<int, array{product_name: string, total_qty_sold: int}>  $fastMoving
     * @param  Collection<int, array{product_name: string, total_qty_sold: int, current_stock: int}>  $slowMoving
     * @return array{fast: array<int, array{name: string, qty: int}>, slow: array<int, array{name: string, qty: int, stock: int}>}
     */
    public function buildChartData(Collection $fastMoving, Collection $slowMoving): array
    {
        return [
            'fast' => $fastMoving->map(fn ($item) => [
                'name' => $item['product_name'],
                'qty' => $item['total_qty_sold'],
            ])->values()->all(),
            'slow' => $slowMoving->map(fn ($item) => [
                'name' => $item['product_name'],
                'qty' => $item['total_qty_sold'],
                'stock' => $item['current_stock'],
            ])->values()->all(),
        ];
    }

    /**
     * Get summary statistics for the product movement report.
     *
     * @return array{total_products_analyzed: int, total_qty_sold: int, fast_moving_count: int, unsold_products_count: int}
     */
    public function getSummaryStats(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $totalQtySold = (int) SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('sale_items.qty');

        $fastMovingCount = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->distinct()
            ->count('sale_items.product_id');

        $totalProducts = Product::query()->count();

        $unsoldCount = Product::query()
            ->where('stock', '>', 0)
            ->whereNotIn('id', function ($query) use ($start, $end) {
                $query->select('sale_items.product_id')
                    ->from('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.status', 'completed')
                    ->whereBetween('sales.date', [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]);
            })
            ->count();

        return [
            'total_products_analyzed' => $totalProducts,
            'total_qty_sold' => $totalQtySold,
            'fast_moving_count' => $fastMovingCount,
            'unsold_products_count' => $unsoldCount,
        ];
    }
}
