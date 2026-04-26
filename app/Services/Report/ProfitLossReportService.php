<?php

namespace App\Services\Report;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitLossReportService
{
    /**
     * Get summary of profit and loss.
     */
    public function getProfitLossSummary(string $startDate, string $endDate): array
    {
        $query = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);

        $totalRevenue = (float) $query->sum('total');
        $totalCost = (float) $query->sum('total_cost');
        $grossProfit = $totalRevenue - $totalCost;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
        ];
    }

    /**
     * Get product breakdown for profit and loss report.
     */
    public function getProductBreakdown(string $startDate, string $endDate, int $perPage = 10): LengthAwarePaginator
    {
        return SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->select(
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(sale_items.qty) as total_qty'),
                DB::raw('SUM(sale_items.qty * sale_items.price) as revenue'),
                DB::raw('SUM(sale_items.qty * sale_items.cost_price) as cost'),
                DB::raw('SUM(sale_items.qty * sale_items.price) - SUM(sale_items.qty * sale_items.cost_price) as profit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('profit', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get chart data for profit and loss report.
     */
    public function getChartData(string $startDate, string $endDate): Collection
    {
        return Sale::query()
            ->where('status', 'completed')
            ->whereBetween('date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(total_cost) as cost')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => $item->day,
                    'revenue' => (float) $item->revenue,
                    'cost' => (float) $item->cost,
                    'profit' => (float) ($item->revenue - $item->cost),
                ];
            });
    }
}
