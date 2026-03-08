<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Setting;

class DashboardController extends Controller
{
    /**
     * Display the server dashboard with real data.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        // Get food-related products
        $foodProducts = Product::where('is_active', true)
            ->whereIn('category_id', [
                DB::table('product_categories')->where('name', 'LIKE', '%food%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%restaurant%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%appetizer%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%dessert%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%main%')->value('id') ?? 0,
            ])
            ->count() ?: Product::where('is_active', true)->take(10)->count();

        // Get drink products if bar service is enabled
        $drinkProducts = Product::where('is_active', true)
            ->whereIn('category_id', [
                DB::table('product_categories')->where('name', 'LIKE', '%drink%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%beverage%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%cocktail%')->value('id') ?? 0,
                DB::table('product_categories')->where('name', 'LIKE', '%alcohol%')->value('id') ?? 0,
            ])
            ->count() ?: 0;

        // Get inventory statistics
        $lowStockItems = Product::where('is_active', true)
            ->whereRaw('stock_quantity <= min_stock_level')
            ->count();

        $totalInventoryValue = Product::where('is_active', true)
            ->sum(DB::raw('stock_quantity * cost_price')) ?: 0;

        // Get today's sales data
        $todaysSales = Sale::whereDate('sale_date', today())
            ->where('payment_status', 'completed')
            ->get();

        $todaysSalesCount = $todaysSales->count();
        $todaysRevenue = $todaysSales->sum('total_amount') ?: 0;
        $todaysDiscount = $todaysSales->sum('discount_amount') ?: 0;

        // Get this week's sales (last 7 days)
        $weekSales = Sale::whereBetween('sale_date', [
            Carbon::now()->startOfDay()->subDays(6),
            Carbon::now()->endOfDay()
        ])->where('payment_status', 'completed')->get();

        $weekRevenue = $weekSales->sum('total_amount') ?: 0;

        // Get this month's sales
        $monthSales = Sale::whereBetween('sale_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->where('payment_status', 'completed')->get();

        $monthRevenue = $monthSales->sum('total_amount') ?: 0;

        // Get sales by product category
        $salesByCategory = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', '>=', Carbon::now()->subDays(30))
            ->where('sales.payment_status', 'completed')
            ->select(
                'product_categories.name as category',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total_price) as revenue')
            )
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('revenue', 'desc')
            ->take(5)
            ->get();

        // Get top selling items
        $topItems = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', '>=', Carbon::now()->subDays(30))
            ->where('sales.payment_status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.emoji',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('AVG(sale_items.unit_price) as avg_price')
            )
            ->groupBy('products.id', 'products.name', 'products.emoji')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Get revenue trend for last 30 days
        $revenueTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = DB::table('sales')
                ->whereDate('sale_date', $date->format('Y-m-d'))
                ->where('payment_status', 'completed')
                ->sum('total_amount') ?: 0;

            $revenueTrend[] = [
                'date' => $date->format('M d'),
                'amount' => (float) $revenue,
            ];
        }

        // Get recent sales
        $recentSales = Sale::with(['user', 'customer'])
            ->where('payment_status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->take(8)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer_name ?? $sale->customer?->name ?? 'Walk-in',
                    'amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'date' => $sale->sale_date?->format('M d, h:i A') ?? now()->format('M d, h:i A'),
                ];
            });

        // Calculate average order value
        $avgOrderValue = $todaysSalesCount > 0 ? $todaysRevenue / $todaysSalesCount : 0;

        // Get shift statistics if available
        $currentShift = DB::table('employee_shifts')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $shiftHours = $currentShift ?
            ceil(Carbon::parse($currentShift->effective_date)->diffInMinutes(now()) / 60) : 0;

        // Get currency settings from database
        $currency = Setting::where('key', 'currency')->value('value') ?? 'USD';
        $currencyPosition = Setting::where('key', 'currency_position')->value('value') ?? 'prefix';

        return Inertia::render('Server/Dashboard', [
            'user' => $user,
            'stats' => [
                'food_count' => $foodProducts,
                'drinks_count' => $drinkProducts,
                'low_stock_items' => $lowStockItems,
                'total_inventory_value' => (float) $totalInventoryValue,
                'todays_sales' => $todaysSalesCount,
                'todays_revenue' => (float) $todaysRevenue,
                'week_revenue' => (float) $weekRevenue,
                'month_revenue' => (float) $monthRevenue,
                'avg_order_value' => (float) $avgOrderValue,
                'shift_hours' => $shiftHours,
            ],
            'recentSales' => $recentSales,
            'topItems' => $topItems,
            'salesByCategory' => $salesByCategory,
            'revenueTrend' => $revenueTrend,
            'currency' => $currency,
            'currencyPosition' => $currencyPosition,
        ]);
    }
}
