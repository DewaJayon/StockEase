<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purcase;
use App\Models\Sale;
use App\Models\StockLog;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{

    /**
     * Get dashboard data for admin, cashier, warehouse
     * @return \Illuminate\Support\Collection
     */
    public function index()
    {
        $role = Auth::user()->role;

        $data = [];

        switch ($role) {
            case 'admin':
                $data = $this->adminData();
                break;

            case 'cashier':
                $data = $this->cashierData();
                break;

            case 'warehouse':
                $data = $this->warehouseData();
                break;

            default:
                $data = [];
                break;
        }

        return Inertia::render('Dashboard/Index', [
            'data' => $data
        ]);
    }

    /**
     * Get dashboard data for admin.
     *
     * This function will return an array containing the following data:
     * - salesSummary: an array containing the total sales for today and this month
     * - lowStock: an array containing the products with low stock
     * - activities: an array containing the activities for the current user
     * - weeklySalesChart: an array containing the categories and data for the weekly sales chart
     *
     * @return array
     */
    private function adminData()
    {
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('total');

        $monthSales = Sale::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total');

        $lowStock = Product::whereColumn('stock', '<=', 'alert_stock')
            ->select('name', 'stock')
            ->get();

        $weeklySalesChart = $this->weeklySalesChart();

        $activities = $this->activity();

        return [
            'salesSummary' => [
                'today' => $todaySales,
                'month' => $monthSales,
            ],
            'lowStock'          => $lowStock,
            'activities'        => $activities,
            'weeklySalesChart'  => $weeklySalesChart
        ];
    }

    /**
     * Get dashboard data for cashier.
     *
     * This function will return an array containing the following data:
     * - cashierSalesSummary: an array containing the total transactions per week, today's income, best selling product, and average per customer
     * - recentTransaction: an array containing the recent transactions for the current user
     * - weeklySalesChart: an array containing the categories and data for the weekly sales chart
     *
     * @return array
     */
    private function cashierData()
    {

        $totalTransactionPerWeek = Sale::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        $todaysIncome = Sale::whereDate('created_at', Carbon::today())->sum('total');

        $todaySale = Sale::with('saleItems.product')
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($todaySale && $todaySale->saleItems->isNotEmpty()) {
            $bestSellingProduct = $todaySale->saleItems->first()->product->name;
        } else {
            $bestSellingProduct = 'Tidak ada transaksi hari ini';
        }

        $averagePerCustomer = Sale::whereDate('created_at', Carbon::today())->avg('total');

        $recentTransaction = Sale::where('payment_method', '!=', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'customer'          => $sale->customer_name ?? 'Umum',
                    'total'             => $sale->total,
                    'payment_method'    => $sale->payment_method,
                    'date'              => $sale->created_at->format('d M Y'),
                ];
            });

        $weeklySalesChart = $this->weeklySalesChart();

        return [
            'cashierSalesSummary' => [
                'totalTransactionPerWeek'   => $totalTransactionPerWeek,
                'todaysIncome'              => $todaysIncome,
                'bestSellingProduct'        => $bestSellingProduct,
                'averagePerCustomer'        => $averagePerCustomer,
            ],
            'recentTransaction' => $recentTransaction,
            'weeklySalesChart'  => $weeklySalesChart
        ];
    }

    /**
     * Get dashboard data for warehouse.
     *
     * This function will return an array containing the following data:
     * - warehouseSummary: an array containing the total product, low stock, new product this month, and active supplier
     * - activityLogWarehouse: an array containing the latest 5 stock logs
     * - warehouseChart: an array containing the categories and data for the warehouse chart
     *
     * @return array
     */
    private function warehouseData()
    {
        $totalProduct = Product::count();

        $lowStock = Product::whereColumn('stock', '<=', 'alert_stock')
            ->select('name', 'stock')
            ->count();

        $newProductThisMonth = Product::whereMonth('created_at', Carbon::now()->month)->count();

        $activeSupplier = Supplier::count();

        $activityLogWarehouse = StockLog::with('product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'product_name'      => $log->product->name,
                    'type'              => $log->type,
                    'qty'               => $log->qty,
                    'date'              => $log->created_at->format('d M Y'),
                ];
            });

        $warehouseChart = $this->warehouseChart();

        return [
            'warehouseSummary' => [
                'totalProduct'          => $totalProduct,
                'lowStock'              => $lowStock,
                'newProductThisMonth'   => $newProductThisMonth,
                'activeSupplier'        => $activeSupplier,
            ],
            'activityLogWarehouse'  => $activityLogWarehouse,
            'warehouseChart'        => $warehouseChart
        ];
    }

    /**
     * Returns the latest activities (sales, purchases, and stock logs)
     * 
     * @return \Illuminate\Support\Collection
     */
    private function activity()
    {
        $latestSales = Sale::where('payment_method', '!=', 'pending')
            ->with('saleItems.product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($sale) {

                $items = $sale->saleItems->map(function ($item) {
                    return "{$item->qty} {$item->product->name}";
                })->join(', ');

                return [
                    'type'          => 'sale',
                    'desc'          => "Penjualan {$items} sebesar Rp " . number_format($sale->total, 0, ',', '.'),
                    'time'          => $sale->created_at->diffForHumans(),
                    'created_at'    => $sale->created_at,
                ];
            });

        $latestPurchases = Purcase::latest()
            ->with('purcaseItems.product')
            ->take(5)
            ->get()
            ->map(function ($purcase) {

                $items = $purcase->purcaseItems->map(function ($item) {
                    return "{$item->qty} {$item->product->name}";
                })->join(', ');

                return [
                    'type'          => 'purchase',
                    'desc'          => "Pembelian menambahkan {$items} produk sebesar Rp " . number_format($purcase->total, 0, ',', '.'),
                    'time'          => $purcase->created_at->diffForHumans(),
                    'created_at'    => $purcase->created_at,
                ];
            });

        $latestStockLogs = StockLog::with('product')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                $action = $log->type === 'in' ? 'bertambah' : 'berkurang';

                return [
                    'type'          => 'stock',
                    'desc'          => "Stok {$log->product->name} {$action} sebanyak {$log->qty}",
                    'time'          => $log->created_at->diffForHumans(),
                    'created_at'    => $log->created_at,
                ];
            });

        $activities = collect()
            ->merge($latestSales)
            ->merge($latestPurchases)
            ->merge($latestStockLogs)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->map(fn($a) => collect($a)->except('created_at'));

        return $activities;
    }

    /**
     * Get weekly sales chart data
     *
     * This function will return an array containing the following data:
     * - categories: an array containing the days of the week (in Indonesian)
     * - data: an array containing the total sales for each day of the week
     *
     * @return array
     */
    private function weeklySalesChart()
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $weeklySales = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->pluck('total', 'date');

        $chartData = [];
        $chartCategories = [];

        Carbon::setLocale('id');

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $formatted = $date->isoFormat('ddd');
            $chartCategories[] = $formatted;
            $chartData[] = $weeklySales[$date->toDateString()] ?? 0;
        }

        return [
            'categories'    => $chartCategories,
            'data'          => $chartData
        ];
    }

    /**
     * Get warehouse chart data
     *
     * This function will return an array containing the following data:
     * - stockMovement: an array containing the date and the total stock in and out for each day of the week
     * - categoryDistribution: an array containing the distribution of products by category
     *
     * @return array
     */
    private function warehouseChart()
    {
        Carbon::setLocale('id');

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

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
                'date'   => $date->isoFormat('ddd'),
                'masuk'  => $row ? (int) $row->masuk : 0,
                'keluar' => $row ? (int) $row->keluar : 0,
            ]);
        }

        $categoryDistribution = Product::selectRaw("
            categories.name as category_name,
            SUM(products.stock) as total_stock
        ")
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get()
            ->map(function ($row) {
                return [
                    'category' => $row->category_name,
                    'total'    => (int) $row->total_stock,
                ];
            });

        return [
            'stockMovement'             => $stockMovement,
            'categoryDistribution'      => $categoryDistribution,
        ];
    }
}
