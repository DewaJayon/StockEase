<?php

namespace App\Services\General;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard data based on user role.
     */
    public function getDashboardData(string $role): array
    {
        return match ($role) {
            'admin' => $this->adminData(),
            'cashier' => $this->cashierData(),
            'warehouse' => $this->warehouseData(),
            default => [],
        };
    }

    /**
     * Get dashboard data for admin.
     */
    private function adminData(): array
    {
        $todaySales = Sale::where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        $monthSales = Sale::where('status', 'completed')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total');

        $lowStock = Product::whereColumn('stock', '<=', 'alert_stock')
            ->select('name', 'stock')
            ->get();

        return [
            'salesSummary' => [
                'today' => $todaySales,
                'month' => $monthSales,
            ],
            'lowStock' => $lowStock,
            'activities' => $this->getActivityHistory(),
            'weeklySalesChart' => $this->getWeeklySalesChart(),
        ];
    }

    /**
     * Get dashboard data for cashier.
     */
    private function cashierData(): array
    {
        $totalTransactionPerWeek = Sale::where('status', 'completed')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])->count();

        $todaysIncome = Sale::where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        $bestSellingProductItem = SaleItem::whereHas('sale', function ($q) {
            $q->whereDate('created_at', Carbon::today())
                ->where('status', 'completed');
        })
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->first();

        $bestSellingProduct = $bestSellingProductItem ? $bestSellingProductItem->product->name : 'Tidak ada transaksi hari ini';

        $averagePerCustomer = Sale::where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->avg('total');

        $recentTransaction = Sale::where('status', 'completed')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($sale) => [
                'customer' => $sale->customer_name ?? 'Umum',
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'date' => $sale->created_at->format('d M Y'),
            ]);

        return [
            'cashierSalesSummary' => [
                'totalTransactionPerWeek' => $totalTransactionPerWeek,
                'todaysIncome' => $todaysIncome,
                'bestSellingProduct' => $bestSellingProduct,
                'averagePerCustomer' => $averagePerCustomer,
            ],
            'recentTransaction' => $recentTransaction,
            'weeklySalesChart' => $this->getWeeklySalesChart(),
        ];
    }

    /**
     * Get dashboard data for warehouse.
     */
    private function warehouseData(): array
    {
        $totalProduct = Product::count();

        $lowStockCount = Product::whereColumn('stock', '<=', 'alert_stock')->count();

        $newProductThisMonth = Product::whereMonth('created_at', Carbon::now()->month)->count();

        $activeSupplier = Supplier::count();

        $activityLogWarehouse = StockLog::with('product')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($log) => [
                'product_name' => $log->product->name,
                'type' => $log->type,
                'qty' => $log->qty,
                'date' => $log->created_at->format('d M Y'),
            ]);

        return [
            'warehouseSummary' => [
                'totalProduct' => $totalProduct,
                'lowStock' => $lowStockCount,
                'newProductThisMonth' => $newProductThisMonth,
                'activeSupplier' => $activeSupplier,
            ],
            'activityLogWarehouse' => $activityLogWarehouse,
            'warehouseChart' => $this->getWarehouseChart(),
        ];
    }

    /**
     * Get unified activity history.
     */
    public function getActivityHistory(): Collection
    {
        Carbon::setLocale('id');

        $latestSales = Sale::where('status', 'completed')
            ->with('saleItems.product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($sale) {
                $items = $sale->saleItems->map(fn ($item) => "{$item->qty} {$item->product->name}")->join(', ');

                return [
                    'type' => 'sale',
                    'desc' => "Penjualan {$items} sebesar Rp ".number_format($sale->total, 0, ',', '.'),
                    'time' => $sale->created_at->diffForHumans(),
                    'created_at' => $sale->created_at,
                ];
            });

        $latestPurchases = Purchase::latest()
            ->with('purchaseItems.product')
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                $items = $purchase->purchaseItems->map(fn ($item) => "{$item->qty} {$item->product->name}")->join(', ');

                return [
                    'type' => 'purchase',
                    'desc' => "Pembelian menambahkan {$items} produk sebesar Rp ".number_format($purchase->total, 0, ',', '.'),
                    'time' => $purchase->created_at->diffForHumans(),
                    'created_at' => $purchase->created_at,
                ];
            });

        $latestStockLogs = StockLog::with('product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                $isIncreasing = $log->type === 'in' || ($log->type === 'adjust' && $log->qty > 0);
                $action = $isIncreasing ? 'bertambah' : 'berkurang';
                $absQty = abs($log->qty);

                return [
                    'type' => 'stock',
                    'desc' => "Stok {$log->product->name} {$action} sebanyak {$absQty}",
                    'time' => $log->created_at->diffForHumans(),
                    'created_at' => $log->created_at,
                ];
            });

        return collect()
            ->merge($latestSales)
            ->merge($latestPurchases)
            ->merge($latestStockLogs)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->map(fn ($a) => collect($a)->except('created_at'));
    }

    /**
     * Get weekly sales chart data.
     */
    public function getWeeklySalesChart(): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $weeklySales = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total')
        )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->pluck('total', 'date');

        $chartData = [];
        $chartCategories = [];

        Carbon::setLocale('id');

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $chartCategories[] = $date->isoFormat('ddd');
            $chartData[] = $weeklySales[$date->toDateString()] ?? 0;
        }

        return [
            'categories' => $chartCategories,
            'data' => $chartData,
        ];
    }

    /**
     * Get warehouse specific chart data.
     */
    public function getWarehouseChart(): array
    {
        Carbon::setLocale('id');

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rawMovement = StockLog::selectRaw("
            DATE(created_at) as date,
            SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END) as masuk,
            SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END) as keluar
        ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $stockMovement = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $row = $rawMovement->get($dateKey);

            $stockMovement->push([
                'date' => $date->isoFormat('ddd'),
                'masuk' => $row ? (int) $row->masuk : 0,
                'keluar' => $row ? (int) $row->keluar : 0,
            ]);
        }

        $categoryDistribution = Product::selectRaw('
            categories.name as category_name,
            SUM(products.stock) as total_stock
        ')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category_name,
                'total' => (int) $row->total_stock,
            ]);

        return [
            'stockMovement' => $stockMovement,
            'categoryDistribution' => $categoryDistribution,
        ];
    }
}
